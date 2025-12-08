<?php
/**
 * Plantillas de Email HTML Profesionales
 * Templates responsivos para notificaciones del sistema
 */

/**
 * Template base HTML
 */
function getTemplateBase($contenido, $config) {
    $nombreEmpresa = $config['general']['nombre_empresa'] ?? 'AutoColombia';
    $colorPrimario = $config['general']['color_primario'] ?? '#007bff';
    $logo = $config['general']['logo'] ?? '';
    $direccion = $config['general']['direccion'] ?? '';
    $telefono = $config['general']['telefono'] ?? '';
    $email = $config['general']['email'] ?? '';
    
    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$nombreEmpresa}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: {$colorPrimario}; padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">{$nombreEmpresa}</h1>
                        </td>
                    </tr>
                    
                    <!-- Contenido -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            {$contenido}
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 5px 0; color: #6c757d; font-size: 13px;">
                                📍 {$direccion}
                            </p>
                            <p style="margin: 0 0 5px 0; color: #6c757d; font-size: 13px;">
                                📞 {$telefono} | 📧 {$email}
                            </p>
                            <p style="margin: 15px 0 0 0; color: #adb5bd; font-size: 12px;">
                                Este es un correo automático, por favor no responder.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * Template: Nueva cita para cliente
 */
function generarTemplateClienteCita($cita, $config) {
    $contenido = <<<HTML
<div style="text-align: center; margin-bottom: 30px;">
    <div style="display: inline-block; background-color: #28a745; color: white; padding: 10px 20px; border-radius: 20px; font-size: 14px; font-weight: bold;">
        ✓ CITA CONFIRMADA
    </div>
</div>

<h2 style="color: #333; margin: 0 0 20px 0;">¡Hola {$cita['nombre']}!</h2>

<p style="color: #555; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
    Tu cita ha sido registrada exitosamente. A continuaciÃ³n los detalles:
</p>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 6px; overflow: hidden; margin-bottom: 25px;">
    <tr>
        <td style="padding: 20px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="8">
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Servicio:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$cita['servicio_nombre']}</td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Fecha:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$cita['fecha']}</td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Hora:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$cita['hora']}</td>
                </tr>
HTML;

    if (!empty($cita['notas'])) {
        $contenido .= <<<HTML
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Notas:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$cita['notas']}</td>
                </tr>
HTML;
    }

    $contenido .= <<<HTML
            </table>
        </td>
    </tr>
</table>

<div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
    <p style="margin: 0; color: #856404; font-size: 14px;">
        <strong>⚠️ Importante:</strong> Por favor llega 10 minutos antes de tu cita.
    </p>
</div>

<p style="color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 10px 0;">
    Si necesitas reagendar o cancelar tu cita, contÃ¡ctanos:
</p>

<div style="text-align: center; margin-top: 30px;">
    <a href="tel:{$config['general']['telefono']}" style="display: inline-block; background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 5px;">
        📞 Llamar
    </a>
    <a href="https://wa.me/{$config['general']['whatsapp']}" style="display: inline-block; background-color: #25D366; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 5px;">
        💬 WhatsApp
    </a>
</div>

<p style="color: #555; font-size: 14px; line-height: 1.6; margin: 30px 0 0 0; text-align: center;">
    ¡Gracias por confiar en nosotros!
</p>
HTML;

    return getTemplateBase($contenido, $config);
}

/**
 * Template: Nueva cita para sucursal
 */
/**
 * Template: Nueva cita para sucursal (ACTUALIZADO)
 */
function generarTemplateSucursalCita($cita, $config, $sucursalId = 'sucursal1') {
    $sucursal = $config['sucursales'][$sucursalId] ?? [];
    $nombreSucursal = $sucursal['nombre'] ?? 'Sucursal';
    
    $contenido = <<<HTML
<div style="text-align: center; margin-bottom: 30px;">
    <div style="display: inline-block; background-color: #007bff; color: white; padding: 10px 20px; border-radius: 20px; font-size: 14px; font-weight: bold;">
        📅 NUEVA CITA - {$nombreSucursal}
    </div>
</div>

<h2 style="color: #333; margin: 0 0 20px 0;">Nueva Cita Registrada</h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 6px; overflow: hidden; margin-bottom: 25px;">
    <tr>
        <td style="padding: 20px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="8">
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0; width: 30%;"><strong>Cliente:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$cita['nombre']}</td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Teléfono:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">
                        <a href="tel:{$cita['telefono']}" style="color: #007bff; text-decoration: none;">{$cita['telefono']}</a>
                    </td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Email:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">
                        <a href="mailto:{$cita['correo']}" style="color: #007bff; text-decoration: none;">{$cita['correo']}</a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 15px 0 8px 0; border-top: 1px solid #dee2e6;"></td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Servicio:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;"><strong>{$cita['servicio_nombre']}</strong></td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Fecha:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;"><strong>{$cita['fecha']}</strong></td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Hora:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;"><strong>{$cita['hora']}</strong></td>
                </tr>
HTML;

    if (!empty($cita['notas'])) {
        $contenido .= <<<HTML
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Notas:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$cita['notas']}</td>
                </tr>
HTML;
    }

    $contenido .= <<<HTML
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Estado:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">
                        <span style="background-color: #ffc107; color: #000; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                            PENDIENTE
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="color: #555; font-size: 14px; line-height: 1.6; margin: 20px 0;">
    Por favor, confirma la disponibilidad y contacta al cliente para confirmar la cita.
</p>
HTML;

    return getTemplateBase($contenido, $config);
}

/**
 * Template: Nueva cita para call center
 */
function generarTemplateCallCenterCita($cita, $config) {
    $contenido = <<<HTML
<h2 style="color: #333; margin: 0 0 20px 0;">Nueva Cita - Seguimiento</h2>

<p style="color: #555; font-size: 14px; line-height: 1.6;">
    <strong>Cliente:</strong> {$cita['nombre']}<br>
    <strong>Servicio:</strong> {$cita['servicio_nombre']}<br>
    <strong>Fecha/Hora:</strong> {$cita['fecha']} a las {$cita['hora']}<br>
    <strong>Contacto:</strong> {$cita['telefono']} / {$cita['correo']}<br>
    <strong>ID Cita:</strong> #{$cita['id']}
</p>
HTML;

    return getTemplateBase($contenido, $config);
}

/**
 * Template: Nueva solicitud para cliente
 */
function generarTemplateClienteSolicitud($solicitud, $config) {
    $tipoSolicitud = ucfirst($solicitud['tipo'] ?? 'general');
    
    $contenido = <<<HTML
<div style="text-align: center; margin-bottom: 30px;">
    <div style="display: inline-block; background-color: #28a745; color: white; padding: 10px 20px; border-radius: 20px; font-size: 14px; font-weight: bold;">
        ✓ SOLICITUD RECIBIDA
    </div>
</div>

<h2 style="color: #333; margin: 0 0 20px 0;">¡Hola {$solicitud['nombre']}!</h2>

<p style="color: #555; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
    Hemos recibido tu solicitud de <strong>{$tipoSolicitud}</strong>. Nuestro equipo la estÃ¡ revisando.
</p>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 6px; overflow: hidden; margin-bottom: 25px;">
    <tr>
        <td style="padding: 20px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="8">
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Tipo:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$tipoSolicitud}</td>
                </tr>
HTML;

    if (isset($solicitud['vehiculo_nombre'])) {
        $contenido .= <<<HTML
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>VehÃ­culo:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$solicitud['vehiculo_nombre']}</td>
                </tr>
HTML;
    }

    if (isset($solicitud['producto_nombre'])) {
        $contenido .= <<<HTML
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Producto:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$solicitud['producto_nombre']}</td>
                </tr>
HTML;
    }

    $contenido .= <<<HTML
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>NÃºmero de referencia:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">#{$solicitud['id']}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div style="background-color: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
    <p style="margin: 0; color: #0c5460; font-size: 14px;">
        <strong>ℹ️ Tiempo de respuesta:</strong> Nos contactaremos contigo en las prÃ³ximas 24-48 horas.
    </p>
</div>

<p style="color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 10px 0;">
    Si tienes preguntas, contÃ¡ctanos:
</p>

<div style="text-align: center; margin-top: 30px;">
    <a href="tel:{$config['general']['telefono']}" style="display: inline-block; background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 5px;">
        📞 Llamar
    </a>
    <a href="https://wa.me/{$config['general']['whatsapp']}" style="display: inline-block; background-color: #25D366; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 5px;">
        💬 WhatsApp
    </a>
</div>

<p style="color: #555; font-size: 14px; line-height: 1.6; margin: 30px 0 0 0; text-align: center;">
    ¡Gracias por tu preferencia!
</p>
HTML;

    return getTemplateBase($contenido, $config);
}

/**
 * Template: Nueva solicitud para almacÃ©n
 */
function generarTemplateAlmacenSolicitud($solicitud, $config) {
    $tipoSolicitud = ucfirst($solicitud['tipo'] ?? 'general');
    
    $contenido = <<<HTML
<div style="text-align: center; margin-bottom: 30px;">
    <div style="display: inline-block; background-color: #dc3545; color: white; padding: 10px 20px; border-radius: 20px; font-size: 14px; font-weight: bold;">
        🛒 NUEVA SOLICITUD DE COMPRA
    </div>
</div>

<h2 style="color: #333; margin: 0 0 20px 0;">Nueva Solicitud - {$tipoSolicitud}</h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 6px; overflow: hidden; margin-bottom: 25px;">
    <tr>
        <td style="padding: 20px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="8">
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0; width: 30%;"><strong>Cliente:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$solicitud['nombre']}</td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>TelÃ©fono:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">
                        <a href="tel:{$solicitud['telefono']}" style="color: #007bff; text-decoration: none;">{$solicitud['telefono']}</a>
                    </td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Email:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">
                        <a href="mailto:{$solicitud['correo']}" style="color: #007bff; text-decoration: none;">{$solicitud['correo']}</a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 15px 0 8px 0; border-top: 1px solid #dee2e6;"></td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Tipo:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;"><strong>{$tipoSolicitud}</strong></td>
                </tr>
HTML;

    if (isset($solicitud['vehiculo_nombre'])) {
        $contenido .= <<<HTML
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>VehÃ­culo:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$solicitud['vehiculo_nombre']}</td>
                </tr>
HTML;
    }

    if (isset($solicitud['producto_nombre'])) {
        $contenido .= <<<HTML
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Producto:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$solicitud['producto_nombre']}</td>
                </tr>
HTML;
    }

    if (isset($solicitud['mensaje'])) {
        $contenido .= <<<HTML
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Mensaje:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$solicitud['mensaje']}</td>
                </tr>
HTML;
    }

    $contenido .= <<<HTML
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Estado:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">
                        <span style="background-color: #ffc107; color: #000; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                            PENDIENTE
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>ID:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">#{$solicitud['id']}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
    <p style="margin: 0; color: #856404; font-size: 14px;">
        <strong>⚠️ AcciÃ³n requerida:</strong> Por favor revisa el inventario y contacta al cliente.
    </p>
</div>
HTML;

    return getTemplateBase($contenido, $config);
}

/**
 * Template: Nueva solicitud para call center
 */
function generarTemplateCallCenterSolicitud($solicitud, $config) {
    $contenido = <<<HTML
<h2 style="color: #333; margin: 0 0 20px 0;">Nueva Solicitud - Seguimiento</h2>

<p style="color: #555; font-size: 14px; line-height: 1.6;">
    <strong>Cliente:</strong> {$solicitud['nombre']}<br>
    <strong>Tipo:</strong> {$solicitud['tipo']}<br>
    <strong>Contacto:</strong> {$solicitud['telefono']} / {$solicitud['correo']}<br>
    <strong>ID Solicitud:</strong> #{$solicitud['id']}
</p>
HTML;

    return getTemplateBase($contenido, $config);
}

/**
 * Template: Cambio de estado - Cita
 */
function generarTemplateEstadoCita($cita, $estadoAnterior, $estadoNuevo, $config) {
    $colorEstado = '#007bff';
    $iconoEstado = 'ℹ️';
    $mensajeEstado = '';
    
    switch ($estadoNuevo) {
        case 'confirmada':
            $colorEstado = '#28a745';
            $iconoEstado = '✓';
            $mensajeEstado = 'Tu cita ha sido confirmada. Te esperamos en la fecha y hora indicada.';
            break;
        case 'cancelada':
            $colorEstado = '#dc3545';
            $iconoEstado = '✗';
            $mensajeEstado = 'Tu cita ha sido cancelada. Si deseas reagendar, contÃ¡ctanos.';
            break;
        case 'completada':
            $colorEstado = '#17a2b8';
            $iconoEstado = '✓';
            $mensajeEstado = 'Tu cita ha sido completada. ¡Gracias por visitarnos!';
            break;
        default:
            $mensajeEstado = 'El estado de tu cita ha sido actualizado.';
    }
    
    $contenido = <<<HTML
<div style="text-align: center; margin-bottom: 30px;">
    <div style="display: inline-block; background-color: {$colorEstado}; color: white; padding: 10px 20px; border-radius: 20px; font-size: 14px; font-weight: bold;">
        {$iconoEstado} ESTADO: {$estadoNuevo}
    </div>
</div>

<h2 style="color: #333; margin: 0 0 20px 0;">ActualizaciÃ³n de tu Cita</h2>

<p style="color: #555; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
    Hola {$cita['nombre']}, {$mensajeEstado}
</p>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 6px; overflow: hidden; margin-bottom: 25px;">
    <tr>
        <td style="padding: 20px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="8">
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Servicio:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$cita['servicio_nombre']}</td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Fecha:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$cita['fecha']}</td>
                </tr>
                <tr>
                    <td style="color: #6c757d; font-size: 14px; padding: 8px 0;"><strong>Hora:</strong></td>
                    <td style="color: #333; font-size: 14px; padding: 8px 0;">{$cita['hora']}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div style="text-align: center; margin-top: 30px;">
    <a href="tel:{$config['general']['telefono']}" style="display: inline-block; background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 5px;">
        📞 Contactar
    </a>
</div>
HTML;

    return getTemplateBase($contenido, $config);
}

/**
 * Template: Cambio de estado - Solicitud
 */
function generarTemplateEstadoSolicitud($solicitud, $estadoAnterior, $estadoNuevo, $config) {
    $colorEstado = '#007bff';
    $iconoEstado = 'ℹ️';
    $mensajeEstado = '';
    
    switch ($estadoNuevo) {
        case 'aprobada':
            $colorEstado = '#28a745';
            $iconoEstado = '✓';
            $mensajeEstado = 'Tu solicitud ha sido aprobada. Pronto nos contactaremos contigo.';
            break;
        case 'rechazada':
            $colorEstado = '#dc3545';
            $iconoEstado = '✗';
            $mensajeEstado = 'Lamentamos informarte que tu solicitud no pudo ser procesada.';
            break;
        case 'en_proceso':
            $colorEstado = '#ffc107';
            $iconoEstado = '⏳';
            $mensajeEstado = 'Tu solicitud estÃ¡ siendo procesada por nuestro equipo.';
            break;
        case 'completada':
            $colorEstado = '#17a2b8';
            $iconoEstado = '✓';
            $mensajeEstado = 'Tu solicitud ha sido completada exitosamente.';
            break;
        default:
            $mensajeEstado = 'El estado de tu solicitud ha sido actualizado.';
    }
    
    $contenido = <<<HTML
<div style="text-align: center; margin-bottom: 30px;">
    <div style="display: inline-block; background-color: {$colorEstado}; color: white; padding: 10px 20px; border-radius: 20px; font-size: 14px; font-weight: bold;">
        {$iconoEstado} ESTADO: {$estadoNuevo}
    </div>
</div>

<h2 style="color: #333; margin: 0 0 20px 0;">ActualizaciÃ³n de tu Solicitud</h2>

<p style="color: #555; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
    Hola {$solicitud['nombre']}, {$mensajeEstado}
</p>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 6px; overflow: hidden; margin-bottom: 25px;">
    <tr>
        <td style="padding: 20px;">
            <p style="color: #6c757d; font-size: 14px; margin: 0;">
                <strong>NÃºmero de referencia:</strong> #{$solicitud['id']}
            </p>
        </td>
    </tr>
</table>

<div style="text-align: center; margin-top: 30px;">
    <a href="tel:{$config['general']['telefono']}" style="display: inline-block; background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 5px;">
        📞 Contactar
    </a>
</div>
HTML;

    return getTemplateBase($contenido, $config);
}
?>