<?php
error_reporting(0);
ini_set('display_errors', 0);

while (ob_get_level()) ob_end_clean();
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

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
    $required = ['nombre', 'telefono', 'correo'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendJSON(['success' => false, 'message' => "Campo requerido: $field"], 400);
        }
    }
    
    // Validar email
    if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
        sendJSON(['success' => false, 'message' => 'Correo electrónico inválido'], 400);
    }
    
    // Ruta al archivo de solicitudes
    $solicitudesFile = __DIR__ . '/../../data/solicitudes.json';
    $dataDir = dirname($solicitudesFile);
    
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Cargar solicitudes existentes
    $solicitudesData = ['solicitudes' => []];
    if (file_exists($solicitudesFile)) {
        $content = @file_get_contents($solicitudesFile);
        if ($content !== false) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['solicitudes'])) {
                $solicitudesData = $decoded;
            }
        }
    }
    
    // Generar ID
    $maxId = 0;
    if (!empty($solicitudesData['solicitudes'])) {
        foreach ($solicitudesData['solicitudes'] as $sol) {
            if (isset($sol['id']) && $sol['id'] > $maxId) {
                $maxId = $sol['id'];
            }
        }
    }
    $nuevoId = $maxId + 1;
    
    // Crear solicitud
    $nuevaSolicitud = [
        'id' => $nuevoId,
        'tipo' => 'producto',
        'nombre' => trim($data['nombre']),
        'telefono' => trim($data['telefono']),
        'correo' => trim($data['correo']),
        'producto_id' => $data['producto_id'] ?? null,
        'producto_nombre' => $data['producto_nombre'] ?? '',
        'cantidad' => $data['cantidad'] ?? 1,
        'mensaje' => $data['mensaje'] ?? '',
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s')
    ];
    
    // Guardar
    $solicitudesData['solicitudes'][] = $nuevaSolicitud;
    $jsonContent = json_encode($solicitudesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $bytesWritten = @file_put_contents($solicitudesFile, $jsonContent);
    
    if ($bytesWritten === false) {
        sendJSON(['success' => false, 'message' => 'Error al guardar solicitud'], 500);
    }
    
    // Intentar notificación
    $notificacionEnviada = false;
    $notificacionError = null;
    try {
        $notificacionEnviada = @enviarNotificacionSolicitud($nuevaSolicitud, $notificacionError);
    } catch (Exception $e) {
        $notificacionError = $e->getMessage();
        @error_log('Error notificación producto: ' . $e->getMessage());
    }
    
    sendJSON([
        'success' => true,
        'message' => 'Solicitud enviada exitosamente',
        'solicitud_id' => $nuevoId,
        'notificacion_enviada' => $notificacionEnviada,
        'notificacion_error' => $notificacionError
    ]);
    
} catch (Exception $e) {
    sendJSON(['success' => false, 'message' => $e->getMessage()], 500);
}

function enviarNotificacionSolicitud($solicitud, &$errorMsg) {
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
    
    if (isset($config['oauth']) && $config['oauth']['enabled']) {
        return enviarConOAuth($solicitud, $config['oauth'], $errorMsg);
    }
    
    if (isset($config['smtp']) && $config['smtp']['enabled']) {
        return enviarConSMTP($solicitud, $config['smtp'], $errorMsg);
    }
    
    $errorMsg = 'No hay método de envío configurado';
    return false;
}

function enviarConOAuth($solicitud, $oauth, &$errorMsg) {
    if (empty($oauth['tenant_id']) || empty($oauth['client_id']) || 
        empty($oauth['client_secret']) || empty($oauth['from_email'])) {
        $errorMsg = 'Configuración OAuth incompleta';
        return false;
    }
    
    try {
        // Token
        $ch = curl_init("https://login.microsoftonline.com/{$oauth['tenant_id']}/oauth2/v2.0/token");
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
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
        
        // Email
        $emailData = [
            'message' => [
                'subject' => 'Solicitud de Producto - Auto Stok',
                'body' => [
                    'contentType' => 'HTML',
                    'content' => construirEmailSolicitud($solicitud)
                ],
                'toRecipients' => [
                    ['emailAddress' => ['address' => $solicitud['correo']]]
                ]
            ],
            'saveToSentItems' => true
        ];
        
        $ch = curl_init("https://graph.microsoft.com/v1.0/users/{$oauth['from_email']}/sendMail");
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
        
        return ($httpCode === 202 || $httpCode === 200);
        
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        return false;
    }
}

function enviarConSMTP($solicitud, $smtp, &$errorMsg) {
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
        $mail->SMTPSecure = ($encryption === 'ssl') ? 'ssl' : 'tls';
        
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        $mail->Timeout = 10;
        
        $mail->setFrom($smtp['username'], $smtp['from_name'] ?? 'Auto Stok');
        $mail->addAddress($solicitud['correo']);
        
        $mail->isHTML(true);
        $mail->Subject = 'Solicitud de Producto - Auto Stok';
        $mail->Body = construirEmailSolicitud($solicitud);
        
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

function construirEmailSolicitud($solicitud) {
    return '<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
body{font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;margin:0}
.container{background:white;padding:30px;border-radius:10px;max-width:600px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
h1{color:#FFD700;border-bottom:3px solid #FFD700;padding-bottom:10px}
.info{background:#f9f9f9;padding:20px;border-left:4px solid #FFD700;margin:20px 0;border-radius:4px}
.footer{color:#666;font-size:0.9rem;margin-top:30px;padding-top:20px;border-top:1px solid #ddd;text-align:center}
</style></head><body>
<div class="container">
<h1>✓ Solicitud Recibida</h1>
<p>Hola <strong>' . htmlspecialchars($solicitud['nombre']) . '</strong>,</p>
<p>Hemos recibido tu solicitud sobre <strong>' . htmlspecialchars($solicitud['producto_nombre']) . '</strong>.</p>
<div class="info">
<strong>Producto:</strong> ' . htmlspecialchars($solicitud['producto_nombre']) . '<br>
<strong>Cantidad:</strong> ' . $solicitud['cantidad'] . '<br>
<strong>Teléfono:</strong> ' . htmlspecialchars($solicitud['telefono']) . '
</div>
<p>Nos contactaremos contigo pronto.</p>
<div class="footer"><strong>Auto Stok</strong><br><small>Correo automático</small></div>
</div></body></html>';
}