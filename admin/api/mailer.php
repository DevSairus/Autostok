<?php
/**
 * Sistema de Envío de Correos con OAuth2
 * Notifica a clientes Y administradores
 */

require_once __DIR__ . '/logs.php';

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
            return ['success' => false, 'message' => 'Error token'];
        }
        
        $tokenData = json_decode($response, true);
        if (!isset($tokenData['access_token'])) {
            return ['success' => false, 'message' => 'Sin token'];
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
        
        return ($httpCode === 202 || $httpCode === 200) 
            ? ['success' => true, 'message' => 'Enviado'] 
            : ['success' => false, 'message' => "HTTP $httpCode"];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Función base de envío
 */
function enviarEmail($config, $destinatario, $asunto, $contenidoHTML) {
    if (isset($config['oauth']) && $config['oauth']['enabled']) {
        return enviarEmailOAuth2($config['oauth'], $destinatario, $asunto, $contenidoHTML);
    }
    return ['success' => false, 'message' => 'OAuth2 no configurado'];
}

/**
 * ==================================================================
 * CITAS DE SERVICIO
 * Notifica al cliente Y al correo de servicio al cliente
 * ==================================================================
 */
function enviarEmailNuevaCita($cita) {
    $config = cargarConfiguracion();
    if (!$config) {
        return ['success' => false, 'message' => 'Sin configuración'];
    }
    
    $resultados = [];
    
    // 1. EMAIL AL CLIENTE
    if (!empty($cita['correo'])) {
        $asuntoCliente = '✓ Cita Registrada - Auto Stok';
        $contenidoCliente = generarEmailClienteCita($cita, $config);
        
        $resultado = enviarEmail($config, $cita['correo'], $asuntoCliente, $contenidoCliente);
        $resultados['cliente'] = $resultado;
        
        guardarLog('email', 'cita_cliente', [
            'destinatario' => $cita['correo'],
            'resultado' => $resultado
        ], 'Sistema');
    }
    
    // 2. EMAIL A SERVICIO AL CLIENTE (ADMINISTRADOR)
    $correoAdmin = $config['general']['correoNegocio'] ?? 'servicioalcliente@autostok.com';
    
    if (!empty($correoAdmin)) {
        $asuntoAdmin = '🔔 Nueva Cita de Servicio - ' . $cita['nombre'];
        $contenidoAdmin = generarEmailAdminCita($cita, $config);
        
        $resultado = enviarEmail($config, $correoAdmin, $asuntoAdmin, $contenidoAdmin);
        $resultados['admin'] = $resultado;
        
        guardarLog('email', 'cita_admin', [
            'destinatario' => $correoAdmin,
            'resultado' => $resultado
        ], 'Sistema');
    }
    
    return $resultados;
}

/**
 * ==================================================================
 * SOLICITUDES DE PRODUCTO
 * Notifica al cliente Y al correo de almacén
 * ==================================================================
 */
function enviarEmailNuevaSolicitudProducto($solicitud) {
    $config = cargarConfiguracion();
    if (!$config) {
        return ['success' => false, 'message' => 'Sin configuración'];
    }
    
    $resultados = [];
    
    // 1. EMAIL AL CLIENTE
    if (!empty($solicitud['correo'])) {
        $asuntoCliente = '✓ Solicitud Recibida - Auto Stok';
        $contenidoCliente = generarEmailClienteProducto($solicitud, $config);
        
        $resultado = enviarEmail($config, $solicitud['correo'], $asuntoCliente, $contenidoCliente);
        $resultados['cliente'] = $resultado;
    }
    
    // 2. EMAIL A ALMACÉN (ADMINISTRADOR)
    // Usar telefonoWhatsappAlmacen como referencia, pero necesitamos un correo
    // Por ahora usar callcenter o el correo principal
    $correoAlmacen = $config['general']['correoCallCenter'] ?? $config['general']['correoNegocio'] ?? 'servicioalcliente@autostok.com';
    
    if (!empty($correoAlmacen)) {
        $asuntoAdmin = '📦 Nueva Solicitud de Producto - ' . $solicitud['nombre'];
        $contenidoAdmin = generarEmailAdminProducto($solicitud, $config);
        
        $resultado = enviarEmail($config, $correoAlmacen, $asuntoAdmin, $contenidoAdmin);
        $resultados['admin'] = $resultado;
        
        guardarLog('email', 'producto_admin', [
            'destinatario' => $correoAlmacen,
            'producto' => $solicitud['producto_nombre'] ?? 'N/A',
            'resultado' => $resultado
        ], 'Sistema');
    }
    
    return $resultados;
}

/**
 * ==================================================================
 * CONTACTO PARA VEHÍCULOS
 * Notifica al cliente Y al correo de ventas/vehículos
 * ==================================================================
 */
function enviarEmailContactoVehiculo($contacto) {
    $config = cargarConfiguracion();
    if (!$config) {
        return ['success' => false, 'message' => 'Sin configuración'];
    }
    
    $resultados = [];
    
    // 1. EMAIL AL CLIENTE
    if (!empty($contacto['correo'])) {
        $asuntoCliente = '✓ Solicitud Recibida - Auto Stok';
        $contenidoCliente = generarEmailClienteVehiculo($contacto, $config);
        
        $resultado = enviarEmail($config, $contacto['correo'], $asuntoCliente, $contenidoCliente);
        $resultados['cliente'] = $resultado;
    }
    
    // 2. EMAIL A VENTAS/VEHÍCULOS (ADMINISTRADOR)
    $correoVentas = $config['general']['correoNegocio'] ?? 'servicioalcliente@autostok.com';
    
    if (!empty($correoVentas)) {
        $asuntoAdmin = '🚗 Nueva Consulta de Vehículo - ' . $contacto['nombre'];
        $contenidoAdmin = generarEmailAdminVehiculo($contacto, $config);
        
        $resultado = enviarEmail($config, $correoVentas, $asuntoAdmin, $contenidoAdmin);
        $resultados['admin'] = $resultado;
        
        guardarLog('email', 'vehiculo_admin', [
            'destinatario' => $correoVentas,
            'vehiculo' => $contacto['vehiculo_nombre'] ?? 'N/A',
            'resultado' => $resultado
        ], 'Sistema');
    }
    
    return $resultados;
}

/**
 * ==================================================================
 * CAMBIO DE ESTADO (CLIENTE)
 * Solo notifica al cliente
 * ==================================================================
 */
function enviarEmailCambioEstado($tipo, $item, $estadoAnterior, $estadoNuevo) {
    $config = cargarConfiguracion();
    if (!$config || empty($item['correo'])) {
        return ['success' => false, 'message' => 'Sin email'];
    }
    
    $asunto = 'Actualización de Estado - Auto Stok';
    $contenido = generarEmailCambioEstado($item, $estadoAnterior, $estadoNuevo, $config);
    
    $resultado = enviarEmail($config, $item['correo'], $asunto, $contenido);
    
    guardarLog('email', 'cambio_estado', [
        'tipo' => $tipo,
        'destinatario' => $item['correo'],
        'estado_nuevo' => $estadoNuevo,
        'resultado' => $resultado
    ], 'Sistema');
    
    return $resultado;
}

/**
 * Generar notificación WhatsApp
 */
function generarNotificacionWhatsApp($tipo, $item) {
    $config = cargarConfiguracion();
    
    $telefono = '';
    if ($tipo === 'cita' || $tipo === 'servicio') {
        $telefono = $config['general']['telefonoWhatsappServicios'] ?? '';
    } elseif ($tipo === 'producto') {
        $telefono = $config['general']['telefonoWhatsappAlmacen'] ?? '';
    } elseif ($tipo === 'vehiculo') {
        $telefono = $config['general']['telefonoWhatsappVehiculos'] ?? '';
    }
    
    if (empty($telefono)) {
        return null;
    }
    
    $mensaje = "Nueva solicitud:\nCliente: {$item['nombre']}\nTel: {$item['telefono']}";
    
    return "https://wa.me/" . preg_replace('/[^0-9]/', '', $telefono) . "?text=" . urlencode($mensaje);
}

// ========== PLANTILLAS HTML ==========

function generarEmailClienteCita($cita, $config) {
    $fecha = date('d/m/Y', strtotime($cita['fecha']));
    return "
<!DOCTYPE html>
<html><head><meta charset='utf-8'><style>
body{font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;margin:0}
.container{background:white;padding:30px;border-radius:10px;max-width:600px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
h1{color:#FFD700;border-bottom:3px solid #FFD700;padding-bottom:10px}
.info{background:#f9f9f9;padding:20px;border-left:4px solid #FFD700;margin:20px 0;border-radius:4px}
.footer{color:#666;font-size:0.9rem;margin-top:30px;padding-top:20px;border-top:1px solid #ddd;text-align:center}
</style></head><body>
<div class='container'>
<h1>✓ Cita Registrada</h1>
<p>Hola <strong>{$cita['nombre']}</strong>,</p>
<p>Tu cita ha sido registrada exitosamente.</p>
<div class='info'>
<strong>Servicio:</strong> {$cita['servicio_nombre']}<br>
<strong>Fecha:</strong> {$fecha}<br>
<strong>Hora:</strong> {$cita['hora']}<br>
<strong>Teléfono:</strong> {$cita['telefono']}
</div>
<p>Nos contactaremos contigo pronto para confirmar.</p>
<div class='footer'><strong>Auto Stok</strong><br><small>Correo automático</small></div>
</div></body></html>";
}

function generarEmailAdminCita($cita, $config) {
    $fecha = date('d/m/Y', strtotime($cita['fecha']));
    $sucursal = $config['sucursales'][$cita['sucursal']]['nombre'] ?? $cita['sucursal'];
    return "
<!DOCTYPE html>
<html><head><meta charset='utf-8'><style>
body{font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;margin:0}
.container{background:white;padding:30px;border-radius:10px;max-width:600px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
h1{color:#dc3545;border-bottom:3px solid #dc3545;padding-bottom:10px}
.info{background:#fff3cd;padding:20px;border-left:4px solid #ffc107;margin:20px 0;border-radius:4px}
.datos{background:#f9f9f9;padding:15px;margin:10px 0;border-radius:4px}
</style></head><body>
<div class='container'>
<h1>🔔 Nueva Cita de Servicio</h1>
<div class='info'>
<strong>⚠️ ACCIÓN REQUERIDA:</strong> Confirmar disponibilidad y contactar al cliente
</div>
<div class='datos'>
<strong>Cliente:</strong> {$cita['nombre']}<br>
<strong>Teléfono:</strong> {$cita['telefono']}<br>
<strong>Email:</strong> {$cita['correo']}<br>
<strong>Servicio:</strong> {$cita['servicio_nombre']}<br>
<strong>Fecha:</strong> {$fecha}<br>
<strong>Hora:</strong> {$cita['hora']}<br>
<strong>Sucursal:</strong> {$sucursal}<br>
<strong>Comentarios:</strong> " . ($cita['comentarios'] ?: 'Ninguno') . "
</div>
<p><strong>Siguiente paso:</strong> Ir al panel admin y confirmar la cita.</p>
</div></body></html>";
}

function generarEmailClienteProducto($solicitud, $config) {
    return "
<!DOCTYPE html>
<html><head><meta charset='utf-8'><style>
body{font-family:Arial,sans-serif;background:#f4f4f4;padding:20px}
.container{background:white;padding:30px;border-radius:10px;max-width:600px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
h1{color:#FFD700;border-bottom:3px solid #FFD700;padding-bottom:10px}
.info{background:#f9f9f9;padding:20px;border-left:4px solid #FFD700;margin:20px 0;border-radius:4px}
</style></head><body>
<div class='container'>
<h1>✓ Solicitud Recibida</h1>
<p>Hola <strong>{$solicitud['nombre']}</strong>,</p>
<p>Hemos recibido tu solicitud sobre <strong>{$solicitud['producto_nombre']}</strong>.</p>
<div class='info'>
<strong>Producto:</strong> {$solicitud['producto_nombre']}<br>
<strong>Cantidad:</strong> {$solicitud['cantidad']}<br>
<strong>Teléfono:</strong> {$solicitud['telefono']}
</div>
<p>Nuestro equipo de almacén se contactará contigo pronto.</p>
</div></body></html>";
}

function generarEmailAdminProducto($solicitud, $config) {
    return "
<!DOCTYPE html>
<html><head><meta charset='utf-8'><style>
body{font-family:Arial,sans-serif;background:#f4f4f4;padding:20px}
.container{background:white;padding:30px;border-radius:10px;max-width:600px;margin:0 auto}
h1{color:#dc3545;border-bottom:3px solid #dc3545;padding-bottom:10px}
.info{background:#fff3cd;padding:20px;border-left:4px solid #ffc107;margin:20px 0}
.datos{background:#f9f9f9;padding:15px;margin:10px 0}
</style></head><body>
<div class='container'>
<h1>📦 Nueva Solicitud de Producto</h1>
<div class='info'><strong>⚠️ ALMACÉN:</strong> Contactar al cliente para cotización</div>
<div class='datos'>
<strong>Cliente:</strong> {$solicitud['nombre']}<br>
<strong>Teléfono:</strong> {$solicitud['telefono']}<br>
<strong>Email:</strong> {$solicitud['correo']}<br>
<strong>Producto:</strong> {$solicitud['producto_nombre']}<br>
<strong>Cantidad:</strong> {$solicitud['cantidad']}<br>
<strong>Mensaje:</strong> " . ($solicitud['mensaje'] ?: 'Ninguno') . "
</div>
</div></body></html>";
}

function generarEmailClienteVehiculo($contacto, $config) {
    return "
<!DOCTYPE html>
<html><head><meta charset='utf-8'><style>
body{font-family:Arial,sans-serif;background:#f4f4f4;padding:20px}
.container{background:white;padding:30px;border-radius:10px;max-width:600px;margin:0 auto}
h1{color:#FFD700;border-bottom:3px solid #FFD700;padding-bottom:10px}
.info{background:#f9f9f9;padding:20px;border-left:4px solid #FFD700;margin:20px 0}
</style></head><body>
<div class='container'>
<h1>✓ Solicitud Recibida</h1>
<p>Hola <strong>{$contacto['nombre']}</strong>,</p>
<p>Hemos recibido tu consulta sobre <strong>{$contacto['vehiculo_nombre']}</strong>.</p>
<div class='info'>
<strong>Vehículo:</strong> {$contacto['vehiculo_nombre']}<br>
<strong>Teléfono:</strong> {$contacto['telefono']}
</div>
<p>Nuestro equipo de ventas se contactará contigo pronto.</p>
</div></body></html>";
}

function generarEmailAdminVehiculo($contacto, $config) {
    return "
<!DOCTYPE html>
<html><head><meta charset='utf-8'><style>
body{font-family:Arial,sans-serif;background:#f4f4f4;padding:20px}
.container{background:white;padding:30px;border-radius:10px;max-width:600px;margin:0 auto}
h1{color:#dc3545;border-bottom:3px solid #dc3545;padding-bottom:10px}
.info{background:#fff3cd;padding:20px;border-left:4px solid #ffc107;margin:20px 0}
.datos{background:#f9f9f9;padding:15px;margin:10px 0}
</style></head><body>
<div class='container'>
<h1>🚗 Nueva Consulta de Vehículo</h1>
<div class='info'><strong>⚠️ VENTAS:</strong> Cliente interesado en un vehículo</div>
<div class='datos'>
<strong>Cliente:</strong> {$contacto['nombre']}<br>
<strong>Teléfono:</strong> {$contacto['telefono']}<br>
<strong>Email:</strong> {$contacto['correo']}<br>
<strong>Vehículo:</strong> {$contacto['vehiculo_nombre']}<br>
<strong>Mensaje:</strong> " . ($contacto['mensaje'] ?: 'Ninguno') . "
</div>
</div></body></html>";
}

function generarEmailCambioEstado($item, $estadoAnterior, $estadoNuevo, $config) {
    $estados = [
        'pendiente' => ['color' => '#ffc107', 'texto' => 'Pendiente'],
        'confirmada' => ['color' => '#28a745', 'texto' => 'Confirmada'],
        'completada' => ['color' => '#007bff', 'texto' => 'Completada'],
        'cancelada' => ['color' => '#dc3545', 'texto' => 'Cancelada']
    ];
    
    $info = $estados[$estadoNuevo] ?? ['color' => '#6c757d', 'texto' => ucfirst($estadoNuevo)];
    $fecha = isset($item['fecha']) ? date('d/m/Y', strtotime($item['fecha'])) : '';
    
    return "
<!DOCTYPE html>
<html><head><meta charset='utf-8'><style>
body{font-family:Arial,sans-serif;background:#f4f4f4;padding:20px}
.container{background:white;padding:30px;border-radius:10px;max-width:600px;margin:0 auto}
h1{color:{$info['color']};border-bottom:3px solid {$info['color']};padding-bottom:10px}
.estado{background:{$info['color']};color:white;padding:10px 20px;border-radius:20px;display:inline-block;font-weight:bold}
.info{background:#f9f9f9;padding:20px;border-left:4px solid {$info['color']};margin:20px 0}
</style></head><body>
<div class='container'>
<h1>Actualización de tu Cita</h1>
<p>Hola <strong>{$item['nombre']}</strong>,</p>
<p>El estado de tu cita ha cambiado a:</p>
<div style='text-align:center;margin:20px 0'>
<span class='estado'>{$info['texto']}</span>
</div>
<div class='info'>
<strong>Servicio:</strong> {$item['servicio_nombre']}<br>
<strong>Fecha:</strong> {$fecha}<br>
<strong>Hora:</strong> {$item['hora']}
</div>
<p>Si tienes alguna duda, contáctanos.</p>
</div></body></html>";
}