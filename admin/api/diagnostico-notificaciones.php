<?php
ini_set('display_errors', 0);
error_reporting(0);

while (ob_get_level()) ob_end_clean();
ob_start();

header('Content-Type: application/json');

try {
    // Leer datos de prueba
    $data = json_decode(file_get_contents('php://input'), true);
    
    $configFile = __DIR__ . '../data/configuracion.json';
    echo json_encode(['ruta_buscada' => $configFile, 'existe' => file_exists($configFile)]);
    exit;
    
    if (!file_exists($configFile)) {
        throw new Exception('Archivo de configuración no existe');
    }
    
    $config = json_decode(file_get_contents($configFile), true);
    
    $diagnostico = [
        'config_existe' => true,
        'oauth_configurado' => isset($config['oauth']),
        'oauth_enabled' => isset($config['oauth']['enabled']) ? $config['oauth']['enabled'] : false,
        'oauth_tenant_id' => isset($config['oauth']['tenant_id']) ? substr($config['oauth']['tenant_id'], 0, 8) . '...' : 'No configurado',
        'oauth_client_id' => isset($config['oauth']['client_id']) ? substr($config['oauth']['client_id'], 0, 8) . '...' : 'No configurado',
        'oauth_from_email' => isset($config['oauth']['from_email']) ? $config['oauth']['from_email'] : 'No configurado',
        'smtp_configurado' => isset($config['smtp']),
        'smtp_enabled' => isset($config['smtp']['enabled']) ? $config['smtp']['enabled'] : false,
    ];
    
    // Si se envió data, intentar enviar correo de prueba
    if ($data && isset($data['test_email'])) {
        $citaPrueba = [
            'nombre' => 'Prueba Diagnóstico',
            'correo' => $data['test_email'],
            'servicio_nombre' => 'Test Service',
            'fecha' => date('Y-m-d'),
            'hora' => '10:00',
            'telefono' => '1234567890'
        ];
        
        $resultado = enviarNotificacionCita($citaPrueba, $config, $diagnostico['error_detalle']);
        $diagnostico['envio_prueba'] = $resultado;
        $diagnostico['error_envio'] = $diagnostico['error_detalle'] ?? null;
    }
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'diagnostico' => $diagnostico
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit;

function enviarNotificacionCita($cita, $config, &$errorMsg) {
    // OAuth2 primero
    if (isset($config['oauth']) && !empty($config['oauth']['enabled'])) {
        return enviarConOAuth($cita, $config['oauth'], $errorMsg);
    }
    
    // SMTP fallback
    if (isset($config['smtp']) && !empty($config['smtp']['enabled'])) {
        return enviarConSMTP($cita, $config['smtp'], $errorMsg);
    }
    
    $errorMsg = 'No hay método de envío configurado o habilitado';
    return false;
}

function enviarConOAuth($cita, $oauth, &$errorMsg) {
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
            CURLOPT_TIMEOUT => 15
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $errorMsg = "Error obteniendo token (HTTP $httpCode)";
            return false;
        }
        
        $tokenData = json_decode($response, true);
        if (!isset($tokenData['access_token'])) {
            $errorMsg = 'No se obtuvo access token';
            return false;
        }
        
        // Construir email
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
            CURLOPT_TIMEOUT => 15
        ]);
        
        $sendResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 202 || $httpCode === 200) {
            return true;
        }
        
        $errorData = json_decode($sendResponse, true);
        $errorMsg = "Error enviando (HTTP $httpCode): " . ($errorData['error']['message'] ?? 'Unknown');
        return false;
        
    } catch (Exception $e) {
        $errorMsg = 'Exception: ' . $e->getMessage();
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
        $errorMsg = 'SMTP Exception: ' . $e->getMessage();
        return false;
    }
}

function construirEmailCita($cita) {
    return '<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
body{font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;margin:0}
.container{background:white;padding:30px;border-radius:10px;max-width:600px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
h1{color:#FFD700;margin-bottom:20px;border-bottom:3px solid #FFD700;padding-bottom:10px}
.info{background:#f9f9f9;padding:20px;border-left:4px solid #FFD700;margin:20px 0;border-radius:4px}
.footer{color:#666;font-size:0.9rem;margin-top:30px;padding-top:20px;border-top:1px solid #ddd;text-align:center}
</style></head>
<body>
<div class="container">
<h1>✓ Cita Registrada</h1>
<p>Hola <strong>' . htmlspecialchars($cita['nombre']) . '</strong>,</p>
<p>Tu cita ha sido registrada exitosamente.</p>
<div class="info">
<strong>Servicio:</strong> ' . htmlspecialchars($cita['servicio_nombre']) . '<br>
<strong>Fecha:</strong> ' . date('d/m/Y', strtotime($cita['fecha'])) . '<br>
<strong>Hora:</strong> ' . htmlspecialchars($cita['hora']) . '<br>
<strong>Teléfono:</strong> ' . htmlspecialchars($cita['telefono']) . '
</div>
<p>Nos contactaremos contigo para confirmar.</p>
<div class="footer"><strong>Auto Stok</strong><br><small>Correo automático</small></div>
</div>
</body>
</html>';
}