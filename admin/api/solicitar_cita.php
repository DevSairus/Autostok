<?php
// ===== CONFIGURACIÓN ESTRICTA DE ERRORES =====
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../data/php_errors.log');

// ===== LIMPIAR COMPLETAMENTE EL BUFFER =====
while (ob_get_level() > 0) {
    ob_end_clean();
}

// ===== INICIAR NUEVO BUFFER =====
ob_start();

// ===== FUNCIÓN PARA RESPUESTA JSON GARANTIZADA =====
function sendJSON($data, $httpCode = 200) {
    // Limpiar cualquier salida previa
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== MANEJO DE ERRORES FATAL =====
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        sendJSON([
            'success' => false,
            'message' => 'Error interno del servidor',
            'debug' => 'Fatal error detected'
        ], 500);
    }
});

// ===== MANEJO DE EXCEPCIONES NO CAPTURADAS =====
set_exception_handler(function($e) {
    sendJSON([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => 'Exception: ' . $e->getFile() . ':' . $e->getLine()
    ], 500);
});

// ===== MANEJO DE OPTIONS (CORS) =====
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJSON(['success' => true], 200);
}

// ===== INICIO DEL SCRIPT =====
try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJSON([
            'success' => false,
            'message' => 'Método no permitido'
        ], 405);
    }
    
    // Leer datos RAW
    $rawInput = file_get_contents('php://input');
    
    if (empty($rawInput)) {
        sendJSON([
            'success' => false,
            'message' => 'No se recibieron datos'
        ], 400);
    }
    
    // Decodificar JSON
    $data = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJSON([
            'success' => false,
            'message' => 'JSON inválido: ' . json_last_error_msg()
        ], 400);
    }
    
    // Validar campos requeridos
    $required = ['nombre', 'telefono', 'correo', 'servicio_id', 'servicio_nombre', 'fecha', 'hora'];
    $missing = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendJSON([
            'success' => false,
            'message' => 'Campos requeridos faltantes: ' . implode(', ', $missing)
        ], 400);
    }
    
    // Validar email
    if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
        sendJSON([
            'success' => false,
            'message' => 'Correo electrónico inválido'
        ], 400);
    }
    
    // Ruta del archivo de citas
    $citasFile = __DIR__ . '/../../data/citas.json';
    $dataDir = dirname($citasFile);
    
    // Crear directorio si no existe
    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0755, true)) {
            sendJSON([
                'success' => false,
                'message' => 'No se pudo crear el directorio de datos'
            ], 500);
        }
    }
    
    // Verificar permisos de escritura
    if (file_exists($citasFile) && !is_writable($citasFile)) {
        sendJSON([
            'success' => false,
            'message' => 'No se tienen permisos de escritura en el archivo de citas'
        ], 500);
    }
    
    // Cargar citas existentes
    $citasData = ['citas' => []];
    
    if (file_exists($citasFile)) {
        $content = file_get_contents($citasFile);
        if ($content !== false) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['citas'])) {
                $citasData = $decoded;
            }
        }
    }
    
    // Generar ID
    $maxId = 0;
    if (!empty($citasData['citas'])) {
        foreach ($citasData['citas'] as $cita) {
            if (isset($cita['id']) && $cita['id'] > $maxId) {
                $maxId = $cita['id'];
            }
        }
    }
    $nuevoId = $maxId + 1;
    
    // Crear nueva cita
    $nuevaCita = [
        'id' => $nuevoId,
        'nombre' => trim($data['nombre']),
        'telefono' => trim($data['telefono']),
        'correo' => trim($data['correo']),
        'servicio_id' => $data['servicio_id'],
        'servicio_nombre' => trim($data['servicio_nombre']),
        'sucursal' => isset($data['sucursal']) ? trim($data['sucursal']) : '',
        'fecha' => $data['fecha'],
        'hora' => trim($data['hora']),
        'comentarios' => isset($data['comentarios']) ? trim($data['comentarios']) : '',
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s')
    ];
    
    // Agregar nueva cita
    $citasData['citas'][] = $nuevaCita;
    
    // Guardar en archivo
    $jsonContent = json_encode($citasData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if ($jsonContent === false) {
        sendJSON([
            'success' => false,
            'message' => 'Error al codificar JSON'
        ], 500);
    }
    
    $bytesWritten = file_put_contents($citasFile, $jsonContent);
    
    if ($bytesWritten === false) {
        sendJSON([
            'success' => false,
            'message' => 'Error al guardar en el archivo'
        ], 500);
    }
    
    // Intentar enviar notificación (sin bloquear si falla)
    $notificacionEnviada = false;
    $notificacionError = null;
    
    try {
        $notificacionEnviada = @enviarNotificacionCita($nuevaCita, $notificacionError);
    } catch (Exception $e) {
        $notificacionError = $e->getMessage();
        // Log pero no bloquear
        @error_log('Error notificación cita: ' . $e->getMessage());
    }
    
    // Respuesta exitosa
    sendJSON([
        'success' => true,
        'message' => 'Cita registrada exitosamente',
        'cita_id' => $nuevoId,
        'notificacion_enviada' => $notificacionEnviada,
        'notificacion_error' => $notificacionError
    ], 200);
    
} catch (Exception $e) {
    sendJSON([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => 'Line: ' . $e->getLine()
    ], 500);
}

// ===== FUNCIONES DE ENVÍO DE NOTIFICACIÓN =====

function enviarNotificacionCita($cita, &$errorMsg) {
    $configFile = __DIR__ . '/../../data/configuracion.json';
    
    if (!file_exists($configFile)) {
        $errorMsg = 'Archivo de configuración no existe';
        return false;
    }
    
    $configContent = @file_get_contents($configFile);
    if ($configContent === false) {
        $errorMsg = 'No se pudo leer configuración';
        return false;
    }
    
    $config = json_decode($configContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errorMsg = 'JSON de configuración inválido';
        return false;
    }
    
    // Intentar OAuth2 primero
    if (isset($config['oauth']) && !empty($config['oauth']['enabled'])) {
        $result = @enviarConOAuth($cita, $config['oauth'], $errorMsg);
        if ($result) return true;
    }
    
    // Fallback a SMTP
    if (isset($config['smtp']) && !empty($config['smtp']['enabled'])) {
        $result = @enviarConSMTP($cita, $config['smtp'], $errorMsg);
        if ($result) return true;
    }
    
    $errorMsg = 'No hay método de envío configurado';
    return false;
}

function enviarConOAuth($cita, $oauth, &$errorMsg) {
    // Validar configuración
    if (empty($oauth['tenant_id']) || empty($oauth['client_id']) || 
        empty($oauth['client_secret']) || empty($oauth['from_email'])) {
        $errorMsg = 'Configuración OAuth incompleta';
        return false;
    }
    
    try {
        // Obtener token
        $tokenUrl = "https://login.microsoftonline.com/{$oauth['tenant_id']}/oauth2/v2.0/token";
        
        $ch = curl_init($tokenUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'client_credentials',
                'scope' => 'https://graph.microsoft.com/.default',
                'client_id' => $oauth['client_id'],
                'client_secret' => $oauth['client_secret']
            ]),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $errorMsg = "Token error (HTTP $httpCode)";
            return false;
        }
        
        $tokenData = json_decode($response, true);
        if (!isset($tokenData['access_token'])) {
            $errorMsg = 'No access token';
            return false;
        }
        
        // Enviar correo
        $emailData = [
            'message' => [
                'subject' => 'Confirmación de Cita - Auto Stok',
                'body' => [
                    'contentType' => 'HTML',
                    'content' => construirEmailCita($cita)
                ],
                'toRecipients' => [
                    ['emailAddress' => ['address' => $cita['correo']]]
                ]
            ],
            'saveToSentItems' => true
        ];
        
        $sendUrl = "https://graph.microsoft.com/v1.0/users/{$oauth['from_email']}/sendMail";
        
        $ch = curl_init($sendUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($emailData),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $tokenData['access_token'],
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10
        ]);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 202 || $httpCode === 200) {
            return true;
        }
        
        $errorMsg = "Send error (HTTP $httpCode)";
        return false;
        
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        return false;
    }
}

function enviarConSMTP($cita, $smtp, &$errorMsg) {
    $phpmailerPath = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    
    if (!file_exists($phpmailerPath)) {
        $errorMsg = 'PHPMailer no instalado';
        return false;
    }
    
    try {
        require_once $phpmailerPath;
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(false);
        
        $mail->isSMTP();
        $mail->Host = $smtp['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp['username'];
        $mail->Password = $smtp['password'];
        $mail->Port = intval($smtp['port']);
        $mail->CharSet = 'UTF-8';
        
        $encryption = $smtp['encryption'] ?? 'tls';
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = 'ssl';
        } else {
            $mail->SMTPSecure = 'tls';
        }
        
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        $mail->Timeout = 10;
        
        $mail->setFrom($smtp['username'], $smtp['from_name'] ?? 'Auto Stok');
        $mail->addAddress($cita['correo']);
        
        $mail->isHTML(true);
        $mail->Subject = 'Confirmación de Cita - Auto Stok';
        $mail->Body = construirEmailCita($cita);
        
        if ($mail->send()) {
            return true;
        }
        
        $errorMsg = $mail->ErrorInfo;
        return false;
        
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        return false;
    }
}

function construirEmailCita($cita) {
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; margin: 0; }
        .container { background: white; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #FFD700; margin-bottom: 20px; border-bottom: 3px solid #FFD700; padding-bottom: 10px; }
        .info { background: #f9f9f9; padding: 20px; border-left: 4px solid #FFD700; margin: 20px 0; border-radius: 4px; }
        .footer { color: #666; font-size: 0.9rem; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Cita Registrada Exitosamente</h1>
        <p>Hola <strong>' . htmlspecialchars($cita['nombre']) . '</strong>,</p>
        <p>Tu cita ha sido registrada correctamente. A continuación los detalles:</p>
        
        <div class="info">
            <strong>Servicio:</strong> ' . htmlspecialchars($cita['servicio_nombre']) . '<br>
            <strong>Fecha:</strong> ' . date('d/m/Y', strtotime($cita['fecha'])) . '<br>
            <strong>Hora:</strong> ' . htmlspecialchars($cita['hora']) . '<br>
            <strong>Teléfono de contacto:</strong> ' . htmlspecialchars($cita['telefono']) . '
        </div>
        
        <p>Nuestro equipo se pondrá en contacto contigo para confirmar tu cita.</p>
        <p>¡Gracias por confiar en nosotros!</p>
        
        <div class="footer">
            <strong>Auto Stok</strong><br>
            Sistema de Gestión de Citas<br>
            <small>Este correo se envió automáticamente</small>
        </div>
    </div>
</body>
</html>';
    
    return $html;
}