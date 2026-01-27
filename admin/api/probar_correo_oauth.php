<?php
ini_set('display_errors', 0);
error_reporting(0);

while (ob_get_level()) ob_end_clean();
ob_start();

session_start();

try {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_rol'] !== 'super_admin') {
        throw new Exception('No autorizado');
    }
    
    $configFile = __DIR__ . '/../../data/configuracion.json';
    
    if (!file_exists($configFile)) {
        throw new Exception('Archivo de configuración no encontrado');
    }
    
    $config = json_decode(file_get_contents($configFile), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al leer configuración: ' . json_last_error_msg());
    }
    
    $oauth = $config['oauth'] ?? null;
    
    if (!$oauth) {
        throw new Exception('No hay configuración OAuth2 guardada');
    }
    
    // Validar campos requeridos
    if (empty($oauth['tenant_id']) || empty($oauth['client_id']) || empty($oauth['client_secret']) || empty($oauth['from_email'])) {
        throw new Exception('Configuración OAuth2 incompleta');
    }
    
    if (!$oauth['enabled']) {
        throw new Exception('El envío de correos OAuth2 está desactivado');
    }
    
    // ============== OBTENER TOKEN ==============
    $tokenUrl = "https://login.microsoftonline.com/{$oauth['tenant_id']}/oauth2/v2.0/token";
    
    $tokenData = [
        'grant_type' => 'client_credentials',
        'scope' => 'https://graph.microsoft.com/.default',
        'client_id' => $oauth['client_id'],
        'client_secret' => $oauth['client_secret']
    ];
    
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $tokenResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Error al obtener token OAuth2. Código: ' . $httpCode);
    }
    
    $tokenResult = json_decode($tokenResponse, true);
    
    if (!isset($tokenResult['access_token'])) {
        throw new Exception('No se pudo obtener el access token');
    }
    
    $accessToken = $tokenResult['access_token'];
    
    // ============== ENVIAR CORREO ==============
    $fromName = $oauth['from_name'] ?? 'Auto Stok';
    
    $emailData = [
        'message' => [
            'subject' => 'Correo de Prueba - Auto Stok',
            'body' => [
                'contentType' => 'HTML',
                'content' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; margin: 0; }
        .container { 
            background: white; 
            padding: 40px; 
            border-radius: 10px; 
            max-width: 600px; 
            margin: 0 auto; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        }
        h1 { color: #FFD700; margin-bottom: 20px; border-bottom: 3px solid #FFD700; padding-bottom: 10px; }
        .success { 
            color: #28a745; 
            font-weight: bold; 
            font-size: 1.2rem; 
            background: #d4edda; 
            padding: 15px; 
            border-radius: 5px; 
            border-left: 4px solid #28a745;
        }
        .info { 
            background: #f9f9f9; 
            padding: 20px; 
            border-left: 4px solid #FFD700; 
            margin: 20px 0; 
            border-radius: 4px; 
        }
        .footer { 
            color: #666; 
            font-size: 0.9rem; 
            margin-top: 30px; 
            padding-top: 20px; 
            border-top: 1px solid #ddd; 
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Configuración OAuth2 Correcta</h1>
        <p class="success">¡Felicitaciones! Tu configuración de Microsoft Graph API está funcionando perfectamente.</p>
        
        <div class="info">
            <strong>📋 Detalles de configuración:</strong><br><br>
            <strong>Método:</strong> Microsoft Graph API con OAuth2<br>
            <strong>Tenant ID:</strong> ' . htmlspecialchars(substr($oauth['tenant_id'], 0, 8)) . '...<br>
            <strong>Remitente:</strong> ' . htmlspecialchars($oauth['from_email']) . '<br>
            <strong>Fecha de prueba:</strong> ' . date('d/m/Y H:i:s') . '
        </div>
        
        <p>Este correo fue enviado automáticamente desde el panel de administración de <strong>Auto Stok</strong> usando <strong>Microsoft Graph API</strong>.</p>
        
        <p>Esta es la forma moderna y segura de enviar correos con Office365/Microsoft 365, usando autenticación OAuth2 en lugar de contraseñas SMTP.</p>
        
        <div class="footer">
            <strong>Auto Stok</strong> - Sistema de Gestión<br>
            Este es un correo de prueba. No es necesario responder.
        </div>
    </div>
</body>
</html>'
            ],
            'toRecipients' => [
                [
                    'emailAddress' => [
                        'address' => $oauth['from_email']
                    ]
                ]
            ]
        ],
        'saveToSentItems' => true
    ];
    
    $sendUrl = "https://graph.microsoft.com/v1.0/users/{$oauth['from_email']}/sendMail";
    
    $ch = curl_init($sendUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $sendResponse = curl_exec($ch);
    $sendHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($sendHttpCode !== 202 && $sendHttpCode !== 200) {
        $errorDetail = json_decode($sendResponse, true);
        $errorMsg = isset($errorDetail['error']['message']) ? $errorDetail['error']['message'] : 'Error desconocido';
        throw new Exception('Error al enviar correo. Código: ' . $sendHttpCode . ' - ' . $errorMsg);
    }
    
    // Registrar log exitoso
    registrarLog(
        'sistema',
        'prueba_correo_oauth_exitosa',
        $_SESSION['admin_username'] ?? 'Admin',
        [
            'destinatario' => $oauth['from_email'],
            'metodo' => 'Microsoft Graph API OAuth2'
        ]
    );
    
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => '✓ Correo enviado exitosamente a ' . $oauth['from_email'] . ' usando Microsoft Graph API (OAuth2)'
    ]);
    
} catch (Exception $e) {
    // Registrar log de error
    registrarLog(
        'sistema',
        'prueba_correo_oauth_error',
        $_SESSION['admin_username'] ?? 'Admin',
        ['error' => $e->getMessage()]
    );
    
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit;

function registrarLog($tipo, $accion, $usuario, $datos) {
    $logFile = __DIR__ . '/../../data/logs.json';
    $logsData = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : ['logs' => []];
    
    if (!is_array($logsData)) {
        $logsData = ['logs' => []];
    }
    if (!isset($logsData['logs']) || !is_array($logsData['logs'])) {
        $logsData['logs'] = [];
    }
    
    $logsData['logs'][] = [
        'fecha' => date('Y-m-d H:i:s'),
        'tipo' => $tipo,
        'accion' => $accion,
        'usuario' => $usuario,
        'datos' => $datos,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    if (count($logsData['logs']) > 500) {
        $logsData['logs'] = array_slice($logsData['logs'], -500);
    }
    
    @file_put_contents($logFile, json_encode($logsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}