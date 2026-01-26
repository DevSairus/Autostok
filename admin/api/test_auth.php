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
    $config = json_decode(file_get_contents($configFile), true);
    $smtp = $config['smtp'] ?? null;
    
    if (!$smtp || empty($smtp['host']) || empty($smtp['username']) || empty($smtp['password'])) {
        throw new Exception('Configuración SMTP incompleta');
    }
    
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    // Habilitar debug verbose
    $mail->SMTPDebug = 2; // Mostrar todos los detalles
    $mail->Debugoutput = function($str, $level) {
        global $debugOutput;
        $debugOutput[] = $str;
    };
    
    $debugOutput = [];
    
    $mail->isSMTP();
    $mail->Host = $smtp['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['username'];
    $mail->Password = $smtp['password'];
    $mail->SMTPSecure = $smtp['encryption'] === 'ssl' ? 'ssl' : 'tls';
    $mail->Port = intval($smtp['port']);
    $mail->CharSet = 'UTF-8';
    
    // Desactivar verificación SSL
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    $mail->setFrom($smtp['username'], $smtp['from_name'] ?? 'Auto Stok');
    $mail->addAddress($smtp['username']);
    
    $mail->isHTML(true);
    $mail->Subject = 'Test';
    $mail->Body = '<h1>Test</h1>';
    
    // Intentar enviar
    $mail->send();
    
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Correo enviado exitosamente',
        'debug' => $debugOutput
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    
    $errorMsg = $e->getMessage();
    $sugerencias = [];
    
    // Analizar el error y dar sugerencias
    if (strpos($errorMsg, 'Could not authenticate') !== false) {
        $sugerencias[] = '🔐 Las credenciales son incorrectas o la cuenta requiere configuración especial';
        
        if (stripos($smtp['host'], 'office365') !== false || stripos($smtp['host'], 'outlook') !== false) {
            $sugerencias[] = '📧 Para Office365/Outlook:';
            $sugerencias[] = '1. Verifica que SMTP está habilitado en tu cuenta';
            $sugerencias[] = '2. Habilita "Autenticación básica SMTP" en el portal de administración';
            $sugerencias[] = '3. O usa Gmail en su lugar (más fácil de configurar)';
            $sugerencias[] = '4. Verifica que la contraseña sea correcta';
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => $errorMsg,
        'sugerencias' => $sugerencias,
        'debug' => isset($debugOutput) ? $debugOutput : [],
        'config_info' => [
            'host' => $smtp['host'],
            'username' => $smtp['username'],
            'port' => $smtp['port'],
            'encryption' => $smtp['encryption'],
            'password_length' => strlen($smtp['password'])
        ]
    ]);
}

exit;