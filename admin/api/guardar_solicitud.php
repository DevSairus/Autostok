<?php
error_reporting(0);
ini_set('display_errors', 0);

while (ob_get_level()) ob_end_clean();
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Incluir el sistema de correos
require_once __DIR__ . '/mailer.php';

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
        sendJSON(['success' => false, 'message' => 'JSON inválido'], 400);
    }
    
    // Validar tipo de solicitud
    $tipo = $data['tipo'] ?? 'general';
    
    // Validar campos
    $required = ['nombre', 'telefono', 'correo'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            sendJSON(['success' => false, 'message' => "Campo requerido: $field"], 400);
        }
    }
    
    if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
        sendJSON(['success' => false, 'message' => 'Email inválido'], 400);
    }
    
    // Guardar solicitud
    $solicitudesFile = __DIR__ . '/../../data/solicitudes.json';
    $dataDir = dirname($solicitudesFile);
    
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    $solicitudesData = ['solicitudes' => []];
    if (file_exists($solicitudesFile)) {
        $content = file_get_contents($solicitudesFile);
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['solicitudes'])) {
            $solicitudesData = $decoded;
        }
    }
    
    $maxId = 0;
    foreach ($solicitudesData['solicitudes'] as $sol) {
        if (isset($sol['id']) && $sol['id'] > $maxId) {
            $maxId = $sol['id'];
        }
    }
    $nuevoId = $maxId + 1;
    
    $nuevaSolicitud = [
        'id' => $nuevoId,
        'tipo' => $tipo,
        'nombre' => trim($data['nombre']),
        'telefono' => trim($data['telefono']),
        'correo' => trim($data['correo']),
        'mensaje' => $data['mensaje'] ?? '',
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s')
    ];
    
    // Campos específicos según tipo
    if ($tipo === 'producto') {
        $nuevaSolicitud['producto_id'] = $data['producto_id'] ?? null;
        $nuevaSolicitud['producto_nombre'] = $data['producto_nombre'] ?? '';
        $nuevaSolicitud['cantidad'] = $data['cantidad'] ?? 1;
    } elseif ($tipo === 'vehiculo') {
        $nuevaSolicitud['vehiculo_id'] = $data['vehiculo_id'] ?? null;
        $nuevaSolicitud['vehiculo_nombre'] = $data['vehiculo_nombre'] ?? '';
    }
    
    $solicitudesData['solicitudes'][] = $nuevaSolicitud;
    file_put_contents($solicitudesFile, json_encode($solicitudesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // ENVIAR NOTIFICACIONES según el tipo
    $resultadosEmail = [];
    try {
        if ($tipo === 'producto') {
            $resultadosEmail = enviarEmailNuevaSolicitudProducto($nuevaSolicitud);
        } elseif ($tipo === 'vehiculo') {
            $resultadosEmail = enviarEmailContactoVehiculo($nuevaSolicitud);
        }
    } catch (Exception $e) {
        error_log('Error notificación: ' . $e->getMessage());
    }
    
    sendJSON([
        'success' => true,
        'message' => 'Solicitud enviada exitosamente',
        'solicitud_id' => $nuevoId,
        'emails' => $resultadosEmail
    ]);
    
} catch (Exception $e) {
    sendJSON(['success' => false, 'message' => $e->getMessage()], 500);
}

function construirAsuntoEmail($solicitud) {
    $tipo = $solicitud['tipo'] ?? 'general';
    
    switch ($tipo) {
        case 'vehiculo':
            return 'Solicitud de Información - Vehículo - Auto Stok';
        case 'producto':
            return 'Solicitud de Producto - Auto Stok';
        default:
            return 'Nueva Solicitud - Auto Stok';
    }
}

function construirEmailSolicitud($solicitud) {
    $tipo = $solicitud['tipo'] ?? 'general';
    $nombre = htmlspecialchars($solicitud['nombre']);
    $telefono = htmlspecialchars($solicitud['telefono']);
    
    $contenidoInfo = '';
    
    if ($tipo === 'vehiculo') {
        $vehiculoNombre = htmlspecialchars($solicitud['vehiculo_nombre'] ?? 'Vehículo');
        $contenidoInfo = "
            <p>Hola <strong>{$nombre}</strong>,</p>
            <p>Hemos recibido tu solicitud de información sobre <strong>{$vehiculoNombre}</strong>.</p>
            <div class='info'>
                <strong>Vehículo:</strong> {$vehiculoNombre}<br>
                <strong>Teléfono:</strong> {$telefono}
            </div>
            <p>Nuestro equipo se pondrá en contacto contigo pronto para brindarte toda la información que necesites.</p>
        ";
    } elseif ($tipo === 'producto') {
        $productoNombre = htmlspecialchars($solicitud['producto_nombre'] ?? 'Producto');
        $cantidad = $solicitud['cantidad'] ?? 1;
        $contenidoInfo = "
            <p>Hola <strong>{$nombre}</strong>,</p>
            <p>Hemos recibido tu solicitud sobre <strong>{$productoNombre}</strong>.</p>
            <div class='info'>
                <strong>Producto:</strong> {$productoNombre}<br>
                <strong>Cantidad:</strong> {$cantidad}<br>
                <strong>Teléfono:</strong> {$telefono}
            </div>
            <p>Nos contactaremos contigo pronto.</p>
        ";
    } else {
        $mensaje = htmlspecialchars($solicitud['mensaje'] ?? '');
        $contenidoInfo = "
            <p>Hola <strong>{$nombre}</strong>,</p>
            <p>Hemos recibido tu solicitud.</p>
            <div class='info'>
                <strong>Teléfono:</strong> {$telefono}<br>
                " . ($mensaje ? "<strong>Mensaje:</strong> {$mensaje}" : "") . "
            </div>
            <p>Nos contactaremos contigo pronto.</p>
        ";
    }
    
    return '<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
body{font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;margin:0}
.container{background:white;padding:30px;border-radius:10px;max-width:600px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
h1{color:#FFD700;border-bottom:3px solid #FFD700;padding-bottom:10px}
.info{background:#f9f9f9;padding:20px;border-left:4px solid #FFD700;margin:20px 0;border-radius:4px}
.footer{color:#666;font-size:0.9rem;margin-top:30px;padding-top:20px;border-top:1px solid #ddd;text-align:center}
</style></head><body>
<div class="container">
<h1>✓ Solicitud Recibida</h1>
' . $contenidoInfo . '
<p>¡Gracias por confiar en nosotros!</p>
<div class="footer"><strong>Auto Stok</strong><br><small>Correo automático</small></div>
</div></body></html>';
}