<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$dataFile = '../../data/citas.json';
$configFile = '../../data/configuracion.json';

// Crear directorio si no existe
if (!file_exists('../../data')) {
    mkdir('../../data', 0755, true);
}

// Cargar datos
$data = file_exists($dataFile) 
    ? json_decode(file_get_contents($dataFile), true) 
    : ['citas' => []];

if (!isset($data['citas'])) {
    $data['citas'] = [];
}

// Cargar configuración
$config = file_exists($configFile) 
    ? json_decode(file_get_contents($configFile), true) 
    : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    if (!$input || !isset($input['nombre']) || !isset($input['telefono']) || !isset($input['correo']) || !isset($input['fecha']) || !isset($input['hora'])) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }
    
    // Validar email
    if (!filter_var($input['correo'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Correo electrónico no válido']);
        exit;
    }
    
    // Crear cita
    $cita = [
        'id' => time(),
        'servicio_id' => $input['servicio_id'] ?? null,
        'servicio_nombre' => $input['servicio_nombre'] ?? '',
        'nombre' => htmlspecialchars($input['nombre']),
        'telefono' => htmlspecialchars($input['telefono']),
        'correo' => htmlspecialchars($input['correo']),
        'fecha' => $input['fecha'],
        'hora' => $input['hora'],
        'notas' => htmlspecialchars($input['notas'] ?? ''),
        'sucursal' => $input['sucursal'] ?? '',
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s')
    ];
    
    $data['citas'][] = $cita;
    
    if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        // Obtener información de la sucursal
        $nombreSucursal = 'N/A';
        if (!empty($cita['sucursal']) && isset($config['sucursales'][$cita['sucursal']])) {
            $nombreSucursal = $config['sucursales'][$cita['sucursal']]['nombre'] ?? 'N/A';
        }
        
        // Enviar correo al call center
        $correoCallCenter = $config['general']['correoCallCenter'] ?? '';
        
        if (!empty($correoCallCenter)) {
            $asunto = "Nueva Cita Solicitada - " . $cita['servicio_nombre'];
            
            $mensaje = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
        .header { background: #FFD700; color: #000; padding: 20px; text-align: center; }
        .content { background: #fff; padding: 30px; margin: 20px 0; border-radius: 8px; }
        .info-row { margin: 15px 0; padding: 10px; background: #f5f5f5; border-left: 4px solid #FFD700; }
        .info-label { font-weight: bold; color: #000; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>🚗 Nueva Cita Solicitada - Autostok</h2>
        </div>
        <div class='content'>
            <h3>Información de la Cita:</h3>
            
            <div class='info-row'>
                <span class='info-label'>Servicio:</span> {$cita['servicio_nombre']}
            </div>
            
            <div class='info-row'>
                <span class='info-label'>Sucursal:</span> $nombreSucursal
            </div>
            
            <div class='info-row'>
                <span class='info-label'>Fecha:</span> " . date('d/m/Y', strtotime($cita['fecha'])) . "
            </div>
            
            <div class='info-row'>
                <span class='info-label'>Hora:</span> {$cita['hora']}
            </div>
            
            <h3>Información del Cliente:</h3>
            
            <div class='info-row'>
                <span class='info-label'>Nombre:</span> {$cita['nombre']}
            </div>
            
            <div class='info-row'>
                <span class='info-label'>Teléfono:</span> {$cita['telefono']}
            </div>
            
            <div class='info-row'>
                <span class='info-label'>Correo:</span> {$cita['correo']}
            </div>
            
            <div class='info-row'>
                <span class='info-label'>Notas:</span> " . ($cita['notas'] ?: 'Sin notas adicionales') . "
            </div>
            
            <div class='info-row'>
                <span class='info-label'>Fecha de solicitud:</span> " . date('d/m/Y H:i', strtotime($cita['fecha_solicitud'])) . "
            </div>
        </div>
        <div class='footer'>
            <p>Este correo fue generado automáticamente por el sistema Autostok</p>
        </div>
    </div>
</body>
</html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: Autostok <noreply@autostok.com>" . "\r\n";
            
            // Intentar enviar el correo
            $emailEnviado = @mail($correoCallCenter, $asunto, $mensaje, $headers);
            
            if (!$emailEnviado) {
                error_log("Error al enviar correo de cita #" . $cita['id'] . " a " . $correoCallCenter);
            }
        }
        
        // Enviar correo de confirmación al cliente
        $mensajeCliente = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
        .header { background: #FFD700; color: #000; padding: 20px; text-align: center; }
        .content { background: #fff; padding: 30px; margin: 20px 0; border-radius: 8px; }
        .info-row { margin: 15px 0; padding: 10px; background: #f5f5f5; border-left: 4px solid #FFD700; }
        .info-label { font-weight: bold; color: #000; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>🚗 Confirmación de Cita - Autostok</h2>
        </div>
        <div class='content'>
            <p>Hola <strong>{$cita['nombre']}</strong>,</p>
            
            <p>Hemos recibido tu solicitud de cita para el servicio de <strong>{$cita['servicio_nombre']}</strong>.</p>
            
            <h3>Detalles de tu cita:</h3>
            
            <div class='info-row'>
                <span class='info-label'>Sucursal:</span> $nombreSucursal
            </div>
            
            <div class='info-row'>
                <span class='info-label'>Fecha:</span> " . date('d/m/Y', strtotime($cita['fecha'])) . "
            </div>
            
            <div class='info-row'>
                <span class='info-label'>Hora:</span> {$cita['hora']}
            </div>
            
            <p>Nuestro equipo revisará tu solicitud y te contactaremos pronto para confirmar tu cita.</p>
            
            <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
            
            <p><strong>¡Gracias por confiar en Autostok!</strong></p>
        </div>
        <div class='footer'>
            <p>Este es un correo automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>
        ";
        
        $headersCliente = "MIME-Version: 1.0" . "\r\n";
        $headersCliente .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headersCliente .= "From: Autostok <noreply@autostok.com>" . "\r\n";
        
        @mail($cita['correo'], "Confirmación de Cita - Autostok", $mensajeCliente, $headersCliente);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Cita solicitada exitosamente. Recibirás un correo de confirmación.',
            'id' => $cita['id']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la cita']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>