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
    
    $smtp = $config['smtp'] ?? null;
    
    if (!$smtp) {
        throw new Exception('No hay configuración SMTP');
    }
    
    if (empty($smtp['host']) || empty($smtp['username']) || empty($smtp['password'])) {
        throw new Exception('Configuración SMTP incompleta');
    }
    
    if (!$smtp['enabled']) {
        throw new Exception('El envío de correos está desactivado');
    }
    
    $phpmailerPath = __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    
    if (!file_exists($phpmailerPath)) {
        throw new Exception('PHPMailer no está instalado');
    }
    
    require_once $phpmailerPath;
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    // ============== CONFIGURACIÓN CON SSL DESACTIVADO ==============
    $mail->isSMTP();
    $mail->Host = $smtp['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['username'];
    $mail->Password = $smtp['password'];
    $mail->SMTPSecure = $smtp['encryption'] === 'ssl' ? 'ssl' : 'tls';
    $mail->Port = intval($smtp['port']);
    $mail->CharSet = 'UTF-8';
    
    // DESACTIVAR VERIFICACIÓN SSL (para desarrollo/localhost)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Habilitar debug para ver detalles (opcional)
    // $mail->SMTPDebug = 2;
    
    // ============== FIN CONFIGURACIÓN ==============
    
    $mail->setFrom($smtp['username'], $smtp['from_name'] ?? 'Auto Stok');
    $mail->addAddress($smtp['username']);
    
    $mail->isHTML(true);
    $mail->Subject = 'Correo de Prueba - Auto Stok';
    $mail->Body = '
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
        .info strong { color: #333; }
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
        <h1>✓ Configuración SMTP Correcta</h1>
        <p class="success">¡Felicitaciones! Tu configuración de correo electrónico está funcionando perfectamente.</p>
        
        <div class="info">
            <strong>📋 Detalles de configuración:</strong><br><br>
            <strong>Servidor SMTP:</strong> ' . htmlspecialchars($smtp['host']) . '<br>
            <strong>Puerto:</strong> ' . $smtp['port'] . '<br>
            <strong>Usuario:</strong> ' . htmlspecialchars($smtp['username']) . '<br>
            <strong>Cifrado:</strong> ' . strtoupper($smtp['encryption']) . '<br>
            <strong>Fecha de prueba:</strong> ' . date('d/m/Y H:i:s') . '
        </div>
        
        <p>Este correo fue enviado automáticamente desde el panel de administración de <strong>Auto Stok</strong> para verificar que tu configuración SMTP funciona correctamente.</p>
        
        <p>Ahora puedes utilizar esta configuración para enviar notificaciones automáticas a tus clientes.</p>
        
        <div class="footer">
            <strong>Auto Stok</strong> - Sistema de Gestión<br>
            Este es un correo de prueba. No es necesario responder.
        </div>
    </div>
</body>
</html>';
    
    $mail->AltBody = 'Correo de prueba desde Auto Stok. Tu configuración SMTP funciona correctamente. Servidor: ' . $smtp['host'] . ' - Fecha: ' . date('d/m/Y H:i:s');
    
    // Enviar
    $mail->send();
    
    // Registrar log exitoso
    registrarLog(
        'sistema',
        'prueba_correo_exitosa',
        $_SESSION['admin_username'] ?? 'Admin',
        [
            'destinatario' => $smtp['username'],
            'servidor' => $smtp['host'],
            'puerto' => $smtp['port']
        ]
    );
    
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => '✓ Correo enviado exitosamente a ' . $smtp['username'] . '. Revisa tu bandeja de entrada.'
    ]);
    
} catch (Exception $e) {
    // Registrar log de error
    registrarLog(
        'sistema',
        'prueba_correo_error',
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