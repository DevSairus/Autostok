<?php
error_reporting(0);
ini_set('display_errors', 0);

while (ob_get_level()) ob_end_clean();
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Incluir el sistema de correos
require_once __DIR__ . '/mailer.php';

function sendJSON($data, $httpCode = 200) {
    while (ob_get_level()) ob_end_clean();
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJSON(['success' => false, 'message' => 'Método no permitido'], 405);
    }
    
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        sendJSON(['success' => false, 'message' => 'No se recibieron datos'], 400);
    }
    
    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJSON(['success' => false, 'message' => 'JSON inválido'], 400);
    }
    
    // Validar campos
    $required = ['nombre', 'telefono', 'correo', 'servicio_id', 'servicio_nombre', 'fecha', 'hora'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendJSON(['success' => false, 'message' => "Campo requerido: $field"], 400);
        }
    }
    
    // Guardar cita
    $citasFile = __DIR__ . '/../../data/citas.json';
    $dataDir = dirname($citasFile);
    
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    $citasData = ['citas' => []];
    if (file_exists($citasFile)) {
        $content = file_get_contents($citasFile);
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['citas'])) {
            $citasData = $decoded;
        }
    }
    
    $maxId = 0;
    foreach ($citasData['citas'] as $cita) {
        if (isset($cita['id']) && $cita['id'] > $maxId) {
            $maxId = $cita['id'];
        }
    }
    $nuevoId = $maxId + 1;
    
    $nuevaCita = [
        'id' => $nuevoId,
        'nombre' => trim($data['nombre']),
        'telefono' => trim($data['telefono']),
        'correo' => trim($data['correo']),
        'servicio_id' => $data['servicio_id'],
        'servicio_nombre' => trim($data['servicio_nombre']),
        'sucursal' => $data['sucursal'] ?? '',
        'fecha' => $data['fecha'],
        'hora' => trim($data['hora']),
        'comentarios' => $data['comentarios'] ?? '',
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s')
    ];
    
    $citasData['citas'][] = $nuevaCita;
    file_put_contents($citasFile, json_encode($citasData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // ENVIAR NOTIFICACIONES (cliente + admin)
    $resultadosEmail = [];
    try {
        $resultadosEmail = enviarEmailNuevaCita($nuevaCita);
    } catch (Exception $e) {
        error_log('Error notificación: ' . $e->getMessage());
    }
    
    sendJSON([
        'success' => true,
        'message' => 'Cita registrada exitosamente',
        'cita_id' => $nuevoId,
        'emails' => $resultadosEmail
    ]);
    
} catch (Exception $e) {
    sendJSON(['success' => false, 'message' => $e->getMessage()], 500);
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