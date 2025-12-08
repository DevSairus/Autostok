<?php
/**
 * Sistema de Envío de Correos y Notificaciones
 * Corregido para usar sucursales y configuración correcta
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Importar PHPMailer
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

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
 * Configurar y crear instancia de PHPMailer
 */
function crearMailer($config) {
    $mail = new PHPMailer(true);
    
    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = $config['general']['smtp_host'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['general']['smtp_user'] ?? '';
        $mail->Password   = $config['general']['smtp_password'] ?? '';
        $mail->SMTPSecure = ($config['general']['smtp_secure'] ?? 'tls') === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $config['general']['smtp_port'] ?? 587;
        $mail->CharSet    = 'UTF-8';
        
        // Remitente
        $mail->setFrom(
            $config['general']['email_from'] ?? $config['general']['smtp_user'],
            $config['general']['nombre_empresa'] ?? 'Autostok'
        );
        
        return $mail;
    } catch (Exception $e) {
        guardarLog('email', 'error_configuracion', [
            'error' => $e->getMessage()
        ], 'Sistema');
        return null;
    }
}

/**
 * Enviar email de nueva cita
 * Ahora envía a la sucursal correcta según la selección
 */
function enviarEmailNuevaCita($cita) {
    $config = cargarConfiguracion();
    if (!$config) {
        return ['success' => false, 'message' => 'No se pudo cargar la configuración'];
    }
    
    // ========== DEBUG: AGREGAR ESTAS LÍNEAS ==========
    error_log("===== DEBUG CITA =====");
    error_log("Cita completa: " . json_encode($cita));
    error_log("Sucursal en cita: " . ($cita['sucursal'] ?? 'NO DEFINIDA'));
    error_log("Config sucursales: " . json_encode($config['sucursales']));
    // =================================================
    
    $resultados = [];
    
    // 1. Email al cliente
    if (!empty($cita['correo'])) {
        $resultado = enviarEmail(
            $config,
            $cita['correo'],
            'Confirmación de Cita - ' . ($config['general']['nombre_empresa'] ?? 'Autostok'),
            generarTemplateClienteCita($cita, $config)
        );
        $resultados['cliente'] = $resultado;
    }
    
    // 2. Email a la SUCURSAL SELECCIONADA
    $sucursalId = $cita['sucursal'] ?? 'sucursal1'; // Por defecto sucursal1
    $emailSucursal = null;
    
    if (isset($config['sucursales'][$sucursalId]['correo'])) {
        $emailSucursal = $config['sucursales'][$sucursalId]['correo'];
    }
    
    if (!empty($emailSucursal)) {
        $resultado = enviarEmail(
            $config,
            $emailSucursal,
            '🔔 Nueva Cita Agendada - ' . $cita['nombre'],
            generarTemplateSucursalCita($cita, $config, $sucursalId)
        );
        $resultados['sucursal'] = $resultado;
    }
    
    // 3. Email al call center (copia)
    $emailCallCenter = $config['general']['correoCallCenter'] ?? null;
    if (!empty($emailCallCenter) && $emailCallCenter !== $emailSucursal) {
        $resultado = enviarEmail(
            $config,
            $emailCallCenter,
            '📋 Nueva Cita Registrada - ' . $cita['servicio_nombre'],
            generarTemplateCallCenterCita($cita, $config)
        );
        $resultados['callcenter'] = $resultado;
    }
    
    // Guardar log
    guardarLog('email', 'envio_cita', [
        'cita_id' => $cita['id'],
        'cliente' => $cita['nombre'],
        'sucursal' => $sucursalId,
        'resultados' => $resultados
    ], 'Sistema');
    
    return $resultados;
}

/**
 * Enviar email de nueva solicitud
 * Diferencia entre solicitud de vehículo y producto
 */
function enviarEmailNuevaSolicitud($solicitud) {
    $config = cargarConfiguracion();
    if (!$config) {
        return ['success' => false, 'message' => 'No se pudo cargar la configuración'];
    }
    
    $resultados = [];
    
    // 1. Email al cliente
    if (!empty($solicitud['correo'])) {
        $resultado = enviarEmail(
            $config,
            $solicitud['correo'],
            'Confirmación de Solicitud - ' . ($config['general']['nombre_empresa'] ?? 'Autostok'),
            generarTemplateClienteSolicitud($solicitud, $config)
        );
        $resultados['cliente'] = $resultado;
    }
    
    // 2. Email al departamento correspondiente según el tipo
    $emailDestino = null;
    $tipoNotificacion = '';
    
    switch ($solicitud['tipo']) {
        case 'producto':
            // Solicitud de productos va al ALMACÉN
            $emailDestino = $config['general']['correoAlmacen'] ?? $config['general']['correoNegocio'];
            $tipoNotificacion = 'almacen';
            break;
            
        case 'vehiculo':
            // Solicitud de vehículos va a VENTAS (correo general)
            $emailDestino = $config['general']['correoNegocio'];
            $tipoNotificacion = 'ventas';
            break;
            
        case 'servicio':
            // Solicitud de servicios va a la sucursal
            $sucursalId = $solicitud['sucursal'] ?? 'sucursal1';
            if (isset($config['sucursales'][$sucursalId]['correo'])) {
                $emailDestino = $config['sucursales'][$sucursalId]['correo'];
            }
            $tipoNotificacion = 'sucursal';
            break;
            
        default:
            // Solicitud general va al correo de negocio
            $emailDestino = $config['general']['correoNegocio'];
            $tipoNotificacion = 'general';
            break;
    }
    
    if (!empty($emailDestino)) {
        $resultado = enviarEmail(
            $config,
            $emailDestino,
            '🛒 Nueva Solicitud - ' . ucfirst($solicitud['tipo']) . ' - ' . $solicitud['nombre'],
            generarTemplateAlmacenSolicitud($solicitud, $config)
        );
        $resultados[$tipoNotificacion] = $resultado;
    }
    
    // 3. Email al call center (copia) - solo si es diferente
    $emailCallCenter = $config['general']['correoCallCenter'] ?? null;
    if (!empty($emailCallCenter) && $emailCallCenter !== $emailDestino) {
        $resultado = enviarEmail(
            $config,
            $emailCallCenter,
            '📋 Nueva Solicitud Registrada - ' . ($solicitud['tipo'] ?? 'General'),
            generarTemplateCallCenterSolicitud($solicitud, $config)
        );
        $resultados['callcenter'] = $resultado;
    }
    
    // Guardar log
    guardarLog('email', 'envio_solicitud', [
        'solicitud_id' => $solicitud['id'],
        'cliente' => $solicitud['nombre'],
        'tipo' => $solicitud['tipo'] ?? 'general',
        'destino' => $tipoNotificacion,
        'resultados' => $resultados
    ], 'Sistema');
    
    return $resultados;
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
        $asunto = 'Actualización de su Cita - ' . ucfirst($estadoNuevo);
        $contenido = generarTemplateEstadoCita($item, $estadoAnterior, $estadoNuevo, $config);
    } else {
        $asunto = 'Actualización de su Solicitud - ' . ucfirst($estadoNuevo);
        $contenido = generarTemplateEstadoSolicitud($item, $estadoAnterior, $estadoNuevo, $config);
    }
    
    $resultado = enviarEmail($config, $item['correo'], $asunto, $contenido);
    
    guardarLog('email', 'cambio_estado', [
        'tipo' => $tipo,
        'id' => $item['id'],
        'estado_anterior' => $estadoAnterior,
        'estado_nuevo' => $estadoNuevo,
        'resultado' => $resultado
    ], 'Sistema');
    
    return $resultado;
}

/**
 * Función base para enviar email
 */
function enviarEmail($config, $destinatario, $asunto, $contenidoHTML) {
    $mail = crearMailer($config);
    if (!$mail) {
        return ['success' => false, 'message' => 'Error al configurar mailer'];
    }
    
    try {
        // Destinatario
        $mail->addAddress($destinatario);
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $contenidoHTML;
        $mail->AltBody = strip_tags($contenidoHTML);
        
        // Enviar
        $mail->send();
        
        return [
            'success' => true, 
            'message' => 'Email enviado correctamente',
            'destinatario' => $destinatario
        ];
    } catch (Exception $e) {
        return [
            'success' => false, 
            'message' => 'Error al enviar: ' . $mail->ErrorInfo,
            'destinatario' => $destinatario,
            'error_detallado' => $e->getMessage()
        ];
    }
}

/**
 * Generar enlace de WhatsApp según el tipo
 */
function generarEnlaceWhatsApp($telefono, $mensaje) {
    $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);
    $mensajeCodificado = urlencode($mensaje);
    
    return "https://wa.me/{$telefonoLimpio}?text={$mensajeCodificado}";
}

/**
 * Generar notificación WhatsApp para admin
 * Retorna el enlace de WhatsApp según el tipo de notificación
 */
function generarNotificacionWhatsApp($tipo, $datos) {
    $config = cargarConfiguracion();
    if (!$config) return null;
    
    $whatsapp = null;
    $mensaje = '';
    
    if ($tipo === 'cita') {
        // WhatsApp de la sucursal seleccionada
        $sucursalId = $datos['sucursal'] ?? 'sucursal1';
        $whatsapp = $config['sucursales'][$sucursalId]['whatsapp'] ?? $config['general']['telefonoWhatsappServicios'];
        
        $mensaje = "*🔔 Nueva Cita Agendada*\n\n";
        $mensaje .= "*Cliente:* {$datos['nombre']}\n";
        $mensaje .= "*Servicio:* {$datos['servicio_nombre']}\n";
        $mensaje .= "*Fecha:* {$datos['fecha']}\n";
        $mensaje .= "*Hora:* {$datos['hora']}\n";
        $mensaje .= "*Teléfono:* {$datos['telefono']}\n";
        if (!empty($datos['notas'])) {
            $mensaje .= "*Notas:* {$datos['notas']}\n";
        }
        
    } else if ($tipo === 'solicitud') {
        // WhatsApp según el tipo de solicitud
        switch ($datos['tipo']) {
            case 'producto':
                $whatsapp = $config['general']['telefonoWhatsappAlmacen'];
                break;
            case 'vehiculo':
                $whatsapp = $config['general']['telefonoWhatsappVehiculos'];
                break;
            case 'servicio':
                $whatsapp = $config['general']['telefonoWhatsappServicios'];
                break;
            default:
                $whatsapp = $config['general']['whatsapp'];
                break;
        }
        
        $mensaje = "*🛒 Nueva Solicitud de " . ucfirst($datos['tipo']) . "*\n\n";
        $mensaje .= "*Cliente:* {$datos['nombre']}\n";
        if (isset($datos['vehiculo_nombre'])) {
            $mensaje .= "*Vehículo:* {$datos['vehiculo_nombre']}\n";
        }
        if (isset($datos['producto_nombre'])) {
            $mensaje .= "*Producto:* {$datos['producto_nombre']}\n";
        }
        $mensaje .= "*Teléfono:* {$datos['telefono']}\n";
        if (!empty($datos['mensaje'])) {
            $mensaje .= "*Mensaje:* {$datos['mensaje']}\n";
        }
    }
    
    if (!$whatsapp) return null;
    
    return generarEnlaceWhatsApp($whatsapp, $mensaje);
}
?>