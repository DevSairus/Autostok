<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$dataFile = '../../data/citas.json';

// Crear directorio si no existe
if (!file_exists('../data')) {
    mkdir('../data', 0755, true);
}

// Cargar datos
$data = file_exists($dataFile) 
    ? json_decode(file_get_contents($dataFile), true) 
    : ['citas' => []];

if (!isset($data['citas'])) {
    $data['citas'] = [];
}

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
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s')
    ];
    
    $data['citas'][] = $cita;
    
    if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT))) {
        // Enviar correo de notificación (opcional)
        $asunto = "Nueva cita solicitada - " . $cita['servicio_nombre'];
        $mensaje = "
        Nueva cita solicitada:
        
        Cliente: {$cita['nombre']}
        Teléfono: {$cita['telefono']}
        Correo: {$cita['correo']}
        Servicio: {$cita['servicio_nombre']}
        Fecha: {$cita['fecha']}
        Hora: {$cita['hora']}
        Notas: {$cita['notas']}
        ";
        
        // Descomentar para enviar correos
        // mail('aguirre984@gmail.com', $asunto, $mensaje);
        // mail($cita['correo'], 'Confirmación de cita - Autostok', "Hemos recibido tu solicitud de cita. Te contactaremos pronto.");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Cita solicitada exitosamente',
            'id' => $cita['id']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la cita']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>