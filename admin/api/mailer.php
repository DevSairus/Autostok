<?php
/**
 * Sistema de Envío de Correos con OAuth2
 */

require_once __DIR__ . '/logs.php';
require_once __DIR__ . '/email-templates.php';

/**
 * Cargar configuración del sistema
 */
function cargarConfiguracion() {
    $configFile = __DIR__ . '/../../data/configuracion.json';
    if (file_exists($configFile)) {
        return json_decode(file_get_contents($configFile), true);
    }
    return null;
}

/**
 * Enviar email usando OAuth2 (Microsoft Graph API)
 */
function enviarEmailOAuth2($oauth, $destinatario, $asunto, $contenidoHTML) {
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
            return ['success' => false, 'message' => 'Error obteniendo token OAuth2'];
        }
        
        $tokenData = json_decode($response, true);
        if (!isset($tokenData['access_token'])) {
            return ['success' => false, 'message' => 'No se obtuvo access token'];
        }
        
        // Enviar correo
        $emailData = [
            'message' => [
                'subject' => $asunto,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $contenidoHTML
                ],
                'toRecipients' => [
                    ['emailAddress' => ['address' => $destinatario]]
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
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 202 || $httpCode === 200) {
            return ['success' => true, 'message' => 'Correo enviado'];
        }
        
        return ['success' => false, 'message' => "Error HTTP: $httpCode"];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Función principal de envío (detecta OAuth2 o SMTP)
 */
function enviarEmail($config, $destinatario, $asunto, $contenidoHTML) {
    // Intentar OAuth2 primero
    if (isset($config['oauth']) && $config['oauth']['enabled']) {
        return enviarEmailOAuth2($config['oauth'], $destinatario, $asunto, $contenidoHTML);
    }
    
    // Fallback a SMTP si está disponible
    if (isset($config['smtp']) && $config['smtp']['enabled']) {
        return enviarEmailSMTP($config['smtp'], $destinatario, $asunto, $contenidoHTML);
    }
    
    return ['success' => false, 'message' => 'No hay método de envío configurado'];
}

/**
 * Enviar email usando SMTP (fallback)
 */
function enviarEmailSMTP($smtp, $destinatario, $asunto, $contenidoHTML) {
    $phpmailerPath = __DIR__ . '/PHPMailer/src/PHPMailer.php';
    
    if (!file_exists($phpmailerPath)) {
        return ['success' => false, 'message' => 'PHPMailer no instalado'];
    }
    
    try {
        require_once $phpmailerPath;
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';
        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        
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
        $mail->addAddress($destinatario);
        
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $contenidoHTML;
        $mail->AltBody = strip_tags($contenidoHTML);
        
        $mail->send();
        return ['success' => true, 'message' => 'Correo enviado'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Enviar email de nueva cita
 */
function enviarEmailNuevaCita($cita) {
    $config = cargarConfiguracion();
    if (!$config || empty($cita['correo'])) {
        return ['success' => false, 'message' => 'Sin configuración o email'];
    }
    
    $asunto = 'Confirmación de Cita - Auto Stok';
    $contenido = generarTemplateNuevaCita($cita, $config);
    
    $resultado = enviarEmail($config, $cita['correo'], $asunto, $contenido);
    
    guardarLog('email', 'nueva_cita', [
        'cita_id' => $cita['id'],
        'destinatario' => $cita['correo'],
        'resultado' => $resultado
    ], 'Sistema');
    
    return $resultado;
}

/**
 * Enviar email de cambio de estado
 */
function enviarEmailCambioEstado($tipo, $item, $estadoAnterior, $estadoNuevo) {
    $config = cargarConfiguracion();
    if (!$config || empty($item['correo'])) {
        return ['success' => false, 'message' => 'Sin configuración o email'];
    }
    
    $asunto = '';
    $contenido = '';
    
    if ($tipo === 'cita') {
        $asunto = 'Actualización de tu Cita - ' . ucfirst($estadoNuevo) . ' - Auto Stok';
        $contenido = generarTemplateEstadoCita($item, $estadoAnterior, $estadoNuevo, $config);
    } else {
        $asunto = 'Actualización de tu Solicitud - ' . ucfirst($estadoNuevo) . ' - Auto Stok';
        $contenido = generarTemplateEstadoSolicitud($item, $estadoAnterior, $estadoNuevo, $config);
    }
    
    $resultado = enviarEmail($config, $item['correo'], $asunto, $contenido);
    
    guardarLog('email', 'cambio_estado', [
        'tipo' => $tipo,
        'id' => $item['id'],
        'estado_anterior' => $estadoAnterior,
        'estado_nuevo' => $estadoNuevo,
        'destinatario' => $item['correo'],
        'resultado' => $resultado
    ], 'Sistema');
    
    return $resultado;
}

/**
 * Generar notificación WhatsApp
 */
function generarNotificacionWhatsApp($tipo, $item) {
    $config = cargarConfiguracion();
    $telefono = $config['general']['whatsapp_numero'] ?? '';
    
    if (empty($telefono)) {
        return null;
    }
    
    $mensaje = '';
    if ($tipo === 'cita') {
        $mensaje = "Nueva cita:\n";
        $mensaje .= "Cliente: {$item['nombre']}\n";
        $mensaje .= "Servicio: {$item['servicio_nombre']}\n";
        $mensaje .= "Fecha: {$item['fecha']} {$item['hora']}\n";
        $mensaje .= "Tel: {$item['telefono']}";
    }
    
    return "https://wa.me/{$telefono}?text=" . urlencode($mensaje);
}