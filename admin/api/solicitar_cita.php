<?php
error_reporting(0);
ini_set('display_errors', 0);

while (ob_get_level()) ob_end_clean();
ob_start();

header('Content-Type: application/json');

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
        sendJSON(['success' => false, 'message' => 'JSON inválido: ' . json_last_error_msg()], 400);
    }
    
    // Validar campos
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
            'message' => 'Campos faltantes: ' . implode(', ', $missing)
        ], 400);
    }
    
    // Guardar cita
    $citasFile = __DIR__ . '/../data/citas.json';
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
    if (!empty($citasData['citas'])) {
        foreach ($citasData['citas'] as $cita) {
            if (isset($cita['id']) && $cita['id'] > $maxId) {
                $maxId = $cita['id'];
            }
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
        'sucursal' => isset($data['sucursal']) ? trim($data['sucursal']) : '',
        'fecha' => $data['fecha'],
        'hora' => trim($data['hora']),
        'comentarios' => isset($data['comentarios']) ? trim($data['comentarios']) : '',
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s')
    ];
    
    $citasData['citas'][] = $nuevaCita;
    $jsonContent = json_encode($citasData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $bytesWritten = file_put_contents($citasFile, $jsonContent);
    
    if ($bytesWritten === false) {
        sendJSON(['success' => false, 'message' => 'Error al guardar'], 500);
    }
    
    // ===== INTENTAR ENVIAR NOTIFICACIÓN CON DEBUG =====
    $debugInfo = [
        'intento_notificacion' => true,
        'config_existe' => false,
        'oauth_configurado' => false,
        'oauth_enabled' => false,
        'resultado_envio' => null,
        'error_envio' => null
    ];
    
    $configFile = __DIR__ . '/../data/configuracion.json';
    
    if (file_exists($configFile)) {
        $debugInfo['config_existe'] = true;
        
        $configContent = file_get_contents($configFile);
        $config = json_decode($configContent, true);
        
        if (isset($config['oauth'])) {
            $debugInfo['oauth_configurado'] = true;
            $debugInfo['oauth_enabled'] = $config['oauth']['enabled'] ?? false;
            $debugInfo['oauth_has_tenant'] = !empty($config['oauth']['tenant_id']);
            $debugInfo['oauth_has_client'] = !empty($config['oauth']['client_id']);
            $debugInfo['oauth_has_secret'] = !empty($config['oauth']['client_secret']);
            $debugInfo['oauth_has_email'] = !empty($config['oauth']['from_email']);
            
            if ($config['oauth']['enabled']) {
                try {
                    $resultadoEnvio = enviarNotificacionOAuth($nuevaCita, $config['oauth']);
                    $debugInfo['resultado_envio'] = $resultadoEnvio['success'];
                    $debugInfo['detalle_envio'] = $resultadoEnvio['message'];
                } catch (Exception $e) {
                    $debugInfo['error_envio'] = $e->getMessage();
                }
            } else {
                $debugInfo['error_envio'] = 'OAuth no está habilitado en configuración';
            }
        } else {
            $debugInfo['error_envio'] = 'No existe configuración OAuth en el archivo';
        }
    } else {
        $debugInfo['error_envio'] = 'Archivo configuracion.json no existe';
    }
    
    // Respuesta con debug
    sendJSON([
        'success' => true,
        'message' => 'Cita registrada exitosamente',
        'cita_id' => $nuevoId,
        'debug_notificacion' => $debugInfo
    ], 200);
    
} catch (Exception $e) {
    sendJSON([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
}

function enviarNotificacionOAuth($cita, $oauth) {
    // Validar configuración completa
    if (empty($oauth['tenant_id'])) {
        return ['success' => false, 'message' => 'Falta tenant_id'];
    }
    if (empty($oauth['client_id'])) {
        return ['success' => false, 'message' => 'Falta client_id'];
    }
    if (empty($oauth['client_secret'])) {
        return ['success' => false, 'message' => 'Falta client_secret'];
    }
    if (empty($oauth['from_email'])) {
        return ['success' => false, 'message' => 'Falta from_email'];
    }
    
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
    
    $tokenResponse = curl_exec($ch);
    $tokenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($tokenHttpCode !== 200) {
        return [
            'success' => false, 
            'message' => "Error obteniendo token (HTTP $tokenHttpCode)",
            'curl_error' => $curlError
        ];
    }
    
    $tokenData = json_decode($tokenResponse, true);
    if (!isset($tokenData['access_token'])) {
        return [
            'success' => false, 
            'message' => 'No se obtuvo access_token',
            'response' => substr($tokenResponse, 0, 200)
        ];
    }
    
    // Construir correo
    $htmlBody = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
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
    
    // Enviar correo
    $emailData = [
        'message' => [
            'subject' => 'Confirmación de Cita - Auto Stok',
            'body' => [
                'contentType' => 'HTML',
                'content' => $htmlBody
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
    $sendHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($sendHttpCode === 202 || $sendHttpCode === 200) {
        return [
            'success' => true, 
            'message' => 'Correo enviado exitosamente',
            'http_code' => $sendHttpCode
        ];
    }
    
    return [
        'success' => false, 
        'message' => "Error enviando correo (HTTP $sendHttpCode)",
        'response' => substr($sendResponse, 0, 200)
    ];
}