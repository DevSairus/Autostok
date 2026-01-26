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
    
    // INTENTAR CON DIFERENTES CONFIGURACIONES
    $configuraciones = [
        [
            'name' => 'TLS con Puerto 587 (Actual)',
            'port' => 587,
            'secure' => 'tls',
            'autotls' => true
        ],
        [
            'name' => 'STARTTLS con Puerto 587',
            'port' => 587,
            'secure' => '',
            'autotls' => true
        ],
        [
            'name' => 'SSL con Puerto 465',
            'port' => 465,
            'secure' => 'ssl',
            'autotls' => false
        ],
        [
            'name' => 'Sin cifrado con AutoTLS',
            'port' => 587,
            'secure' => '',
            'autotls' => true
        ]
    ];
    
    $resultados = [];
    
    foreach ($configuraciones as $conf) {
        try {
            $debugOutput = [];
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Debug output
            $mail->SMTPDebug = 0; // Desactivar para probar más rápido
            $mail->Debugoutput = function($str, $level) use (&$debugOutput) {
                $debugOutput[] = $str;
            };
            
            $mail->isSMTP();
            $mail->Host = $smtp['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtp['username'];
            $mail->Password = $smtp['password'];
            $mail->Port = $conf['port'];
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 10; // Timeout corto para probar rápido
            
            // Configuración de cifrado según el test
            if (!empty($conf['secure'])) {
                $mail->SMTPSecure = $conf['secure'];
            }
            
            $mail->SMTPAutoTLS = $conf['autotls'];
            
            // Desactivar verificación SSL
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            $mail->setFrom($smtp['username'], 'Auto Stok Test');
            $mail->addAddress($smtp['username']);
            
            $mail->isHTML(true);
            $mail->Subject = 'Test - ' . $conf['name'];
            $mail->Body = '<h1>Prueba exitosa</h1><p>Configuración: ' . $conf['name'] . '</p>';
            
            // Intentar enviar
            $mail->send();
            
            $resultados[] = [
                'configuracion' => $conf['name'],
                'resultado' => 'ÉXITO ✅',
                'detalles' => 'Correo enviado correctamente',
                'port' => $conf['port'],
                'secure' => $conf['secure'] ?: 'AUTO',
                'autotls' => $conf['autotls'] ? 'Sí' : 'No'
            ];
            
            // Si uno funciona, salir del loop
            break;
            
        } catch (Exception $e) {
            $resultados[] = [
                'configuracion' => $conf['name'],
                'resultado' => 'FALLÓ ❌',
                'detalles' => $e->getMessage(),
                'port' => $conf['port'],
                'secure' => $conf['secure'] ?: 'AUTO',
                'autotls' => $conf['autotls'] ? 'Sí' : 'No'
            ];
        }
    }
    
    // Verificar si alguna funcionó
    $exito = false;
    $configExitosa = null;
    
    foreach ($resultados as $r) {
        if (strpos($r['resultado'], 'ÉXITO') !== false) {
            $exito = true;
            $configExitosa = $r;
            break;
        }
    }
    
    ob_end_clean();
    header('Content-Type: application/json');
    
    if ($exito) {
        echo json_encode([
            'success' => true,
            'message' => '✓ ¡Configuración exitosa encontrada!',
            'configuracion_exitosa' => $configExitosa,
            'todas_las_pruebas' => $resultados,
            'recomendacion' => 'Actualiza tu configuración SMTP con: Puerto ' . $configExitosa['port'] . ', Cifrado: ' . ($configExitosa['secure'] ?: 'TLS automático')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '✗ Ninguna configuración funcionó. El problema es de autenticación.',
            'todas_las_pruebas' => $resultados,
            'sugerencias' => [
                '1. Verifica que la contraseña sea correcta',
                '2. Verifica que SMTP esté habilitado en la cuenta de Office365',
                '3. Habilita "Autenticación básica SMTP" en el portal de Microsoft 365',
                '4. Considera usar Gmail temporalmente (más fácil de configurar)'
            ]
        ]);
    }
    
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit;