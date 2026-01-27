<?php
ini_set('display_errors', 0);
error_reporting(0);

while (ob_get_level()) ob_end_clean();
ob_start();

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('No se recibieron datos');
    }
    
    // Validar campos
    $required = ['nombre', 'telefono', 'correo'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Campo requerido: $field");
        }
    }
    
    // Cargar solicitudes
    $solicitudesFile = __DIR__ . '/../data/solicitudes.json';
    $solicitudesData = file_exists($solicitudesFile) 
        ? json_decode(file_get_contents($solicitudesFile), true) 
        : ['solicitudes' => []];
    
    if (!isset($solicitudesData['solicitudes'])) {
        $solicitudesData['solicitudes'] = [];
    }
    
    // Generar ID
    $id = count($solicitudesData['solicitudes']) > 0 
        ? max(array_column($solicitudesData['solicitudes'], 'id')) + 1 
        : 1;
    
    // Crear solicitud
    $nuevaSolicitud = [
        'id' => $id,
        'tipo' => 'vehiculo',
        'nombre' => $data['nombre'],
        'telefono' => $data['telefono'],
        'correo' => $data['correo'],
        'vehiculo_id' => $data['vehiculo_id'] ?? null,
        'vehiculo_nombre' => $data['vehiculo_nombre'] ?? '',
        'mensaje' => $data['mensaje'] ?? '',
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s')
    ];
    
    // Guardar
    $solicitudesData['solicitudes'][] = $nuevaSolicitud;
    
    $guardado = file_put_contents(
        $solicitudesFile,
        json_encode($solicitudesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
    
    if (!$guardado) {
        throw new Exception('Error al guardar la solicitud');
    }
    
    // Intentar enviar notificación
    $notificacionEnviada = false;
    try {
        $notificacionEnviada = enviarNotificacionSolicitud($nuevaSolicitud);
    } catch (Exception $e) {
        error_log('Error notificación: ' . $e->getMessage());
    }
    
    ob_end_clean();
    
    echo json_encode([
        'success' => true,
        'message' => 'Solicitud enviada exitosamente',
        'solicitud_id' => $id,
        'notificacion_enviada' => $notificacionEnviada
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit;

function enviarNotificacionSolicitud($solicitud) {
    $configFile = __DIR__ . '/../data/configuracion.json';
    
    if (!file_exists($configFile)) {
        return false;
    }
    
    $config = json_decode(file_get_contents($configFile), true);
    
    // OAuth2 primero
    if (isset($config['oauth']) && $config['oauth']['enabled']) {
        return enviarConOAuth($solicitud, $config['oauth']);
    }
    
    // SMTP fallback
    if (isset($config['smtp']) && $config['smtp']['enabled']) {
        return enviarConSMTP($solicitud, $config['smtp']);
    }
    
    return false;
}

function enviarConOAuth($solicitud, $oauth) {
    try {
        // Obtener token
        $tokenUrl = "https://login.microsoftonline.com/{$oauth['tenant_id']}/oauth2/v2.0/token";
        
        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'client_credentials',
            'scope' => 'https://graph.microsoft.com/.default',
            'client_id' => $oauth['client_id'],
            'client_secret' => $oauth['client_secret']
        ]));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $tokenResponse = curl_exec($ch);
        curl_close($ch);
        
        $tokenResult = json_decode($tokenResponse, true);
        if (!isset($tokenResult['access_token'])) {
            return false;
        }
        
        // Enviar correo
        $emailData = [
            'message' => [
                'subject' => 'Solicitud de Información - Auto Stok',
                'body' => [
                    'contentType' => 'HTML',
                    'content' => construirCuerpoCorreoSolicitud($solicitud)
                ],
                'toRecipients' => [
                    ['emailAddress' => ['address' => $solicitud['correo']]]
                ]
            ]
        ];
        
        $sendUrl = "https://graph.microsoft.com/v1.0/users/{$oauth['from_email']}/sendMail";
        
        $ch = curl_init($sendUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $tokenResult['access_token'],
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 202 || $httpCode === 200);
        
    } catch (Exception $e) {
        return false;
    }
}

function enviarConSMTP($solicitud, $smtp) {
    $phpmailerPath = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    
    if (!file_exists($phpmailerPath)) {
        return false;
    }
    
    try {
        require_once $phpmailerPath;
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = $smtp['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp['username'];
        $mail->Password = $smtp['password'];
        $mail->Port = intval($smtp['port']);
        $mail->CharSet = 'UTF-8';
        
        if (($smtp['encryption'] ?? 'tls') === 'ssl') {
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
        
        $mail->setFrom($smtp['username'], $smtp['from_name'] ?? 'Auto Stok');
        $mail->addAddress($solicitud['correo']);
        
        $mail->isHTML(true);
        $mail->Subject = 'Solicitud de Información - Auto Stok';
        $mail->Body = construirCuerpoCorreoSolicitud($solicitud);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        return false;
    }
}

function construirCuerpoCorreoSolicitud($solicitud) {
    $tipoTexto = $solicitud['tipo'] === 'vehiculo' ? 'vehículo' : 'producto';
    $itemNombre = $solicitud['vehiculo_nombre'] ?? $solicitud['producto_nombre'] ?? 'el artículo';
    
    return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; margin: 0; }
        .container { background: white; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #FFD700; }
        .info { background: #f9f9f9; padding: 20px; border-left: 4px solid #FFD700; margin: 20px 0; border-radius: 4px; }
        .footer { color: #666; font-size: 0.9rem; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Solicitud Recibida</h1>
        <p>Hola <strong>' . htmlspecialchars($solicitud['nombre']) . '</strong>,</p>
        <p>Hemos recibido tu solicitud de información sobre <strong>' . htmlspecialchars($itemNombre) . '</strong>.</p>
        
        <div class="info">
            <strong>Tipo:</strong> ' . ucfirst($tipoTexto) . '<br>
            <strong>Teléfono:</strong> ' . htmlspecialchars($solicitud['telefono']) . '<br>
            <strong>Fecha:</strong> ' . date('d/m/Y H:i') . '
        </div>
        
        <p>Nuestro equipo se pondrá en contacto contigo lo antes posible.</p>
        
        <div class="footer">
            <strong>Auto Stok</strong><br>
            Este correo se envió automáticamente.
        </div>
    </div>
</body>
</html>';
}