<?php
// Deshabilitar COMPLETAMENTE cualquier salida de error
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Limpiar cualquier output buffer
while (ob_get_level()) {
    ob_end_clean();
}

// Iniciar nuevo buffer
ob_start();

session_start();

// Información de diagnóstico
$diagnostico = [
    'paso' => 1,
    'mensaje' => 'Inicio del script',
    'errores' => []
];

try {
    // PASO 1: Verificar sesión
    $diagnostico['paso'] = 1;
    $diagnostico['sesion_activa'] = isset($_SESSION['admin_logged_in']);
    $diagnostico['es_super_admin'] = isset($_SESSION['admin_rol']) && $_SESSION['admin_rol'] === 'super_admin';
    
    if (!isset($_SESSION['admin_logged_in'])) {
        throw new Exception('No hay sesión activa');
    }
    
    if ($_SESSION['admin_rol'] !== 'super_admin') {
        throw new Exception('Usuario no es super_admin');
    }
    
    // PASO 2: Verificar archivo de configuración
    $diagnostico['paso'] = 2;
    $configFile = __DIR__ . '/../../data/configuracion.json';
    $diagnostico['config_file'] = $configFile;
    $diagnostico['config_existe'] = file_exists($configFile);
    
    if (!file_exists($configFile)) {
        throw new Exception('Archivo de configuración no existe: ' . $configFile);
    }
    
    // PASO 3: Leer configuración
    $diagnostico['paso'] = 3;
    $configContent = @file_get_contents($configFile);
    $diagnostico['config_size'] = strlen($configContent);
    
    if ($configContent === false) {
        throw new Exception('No se pudo leer el archivo de configuración');
    }
    
    // PASO 4: Decodificar JSON
    $diagnostico['paso'] = 4;
    $config = json_decode($configContent, true);
    $diagnostico['json_error'] = json_last_error_msg();
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
    }
    
    // PASO 5: Verificar configuración SMTP
    $diagnostico['paso'] = 5;
    $diagnostico['tiene_smtp'] = isset($config['smtp']);
    
    if (!isset($config['smtp'])) {
        throw new Exception('No existe configuración SMTP en configuracion.json');
    }
    
    $smtp = $config['smtp'];
    $diagnostico['smtp_host'] = $smtp['host'] ?? 'NO DEFINIDO';
    $diagnostico['smtp_username'] = $smtp['username'] ?? 'NO DEFINIDO';
    $diagnostico['smtp_port'] = $smtp['port'] ?? 'NO DEFINIDO';
    $diagnostico['smtp_enabled'] = $smtp['enabled'] ?? false;
    $diagnostico['smtp_tiene_password'] = !empty($smtp['password']);
    
    // Validar campos
    if (empty($smtp['host'])) {
        throw new Exception('SMTP host no está configurado');
    }
    if (empty($smtp['username'])) {
        throw new Exception('SMTP username no está configurado');
    }
    if (empty($smtp['password'])) {
        throw new Exception('SMTP password no está configurado');
    }
    if (!$smtp['enabled']) {
        throw new Exception('El envío de correos está desactivado. Active la opción en configuración.');
    }
    
    // PASO 6: Verificar PHPMailer
    $diagnostico['paso'] = 6;
    $phpmailerPath = __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    $diagnostico['phpmailer_path'] = $phpmailerPath;
    $diagnostico['phpmailer_existe'] = file_exists($phpmailerPath);
    
    if (!file_exists($phpmailerPath)) {
        throw new Exception('PHPMailer no está instalado en: ' . $phpmailerPath);
    }
    
    // PASO 7: Cargar PHPMailer
    $diagnostico['paso'] = 7;
    require_once $phpmailerPath;
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
    
    $diagnostico['phpmailer_cargado'] = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
    
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        throw new Exception('PHPMailer no se pudo cargar correctamente');
    }
    
    // PASO 8: Crear instancia y configurar
    $diagnostico['paso'] = 8;
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    $mail->isSMTP();
    $mail->Host = $smtp['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp['username'];
    $mail->Password = $smtp['password'];
    $mail->SMTPSecure = $smtp['encryption'] === 'ssl' ? 'ssl' : 'tls';
    $mail->Port = intval($smtp['port']);
    $mail->CharSet = 'UTF-8';
    
    $diagnostico['mail_configurado'] = true;
    
    // PASO 9: Configurar remitente y destinatario
    $diagnostico['paso'] = 9;
    $mail->setFrom($smtp['username'], $smtp['from_name'] ?? 'Auto Stok');
    $mail->addAddress($smtp['username']);
    
    // PASO 10: Configurar contenido
    $diagnostico['paso'] = 10;
    $mail->isHTML(true);
    $mail->Subject = 'Test - Auto Stok';
    $mail->Body = '<h1>Prueba exitosa</h1><p>Tu configuración SMTP funciona.</p>';
    $mail->AltBody = 'Prueba exitosa. Tu configuración SMTP funciona.';
    
    // PASO 11: Enviar
    $diagnostico['paso'] = 11;
    $mail->send();
    
    $diagnostico['enviado'] = true;
    $diagnostico['paso'] = 12;
    
    // Limpiar buffer y enviar respuesta exitosa
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Correo enviado exitosamente a ' . $smtp['username'],
        'diagnostico' => $diagnostico
    ]);
    
} catch (Exception $e) {
    // Capturar error
    $diagnostico['error'] = $e->getMessage();
    $diagnostico['error_trace'] = $e->getTraceAsString();
    
    // Limpiar buffer y enviar error
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'diagnostico' => $diagnostico
    ]);
}

exit;