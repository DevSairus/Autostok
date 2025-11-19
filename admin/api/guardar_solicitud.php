<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$dataFile = '../../data/solicitudes.json';

// Crear directorio si no existe
if (!file_exists('../data')) {
    mkdir('../data', 0755, true);
}

// Cargar datos
$data = file_exists($dataFile) 
    ? json_decode(file_get_contents($dataFile), true) 
    : ['solicitudes' => []];

if (!isset($data['solicitudes'])) {
    $data['solicitudes'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['nombre']) || !isset($input['telefono'])) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }
    
    // Crear solicitud
    $solicitud = [
        'id' => $input['id'] ?? time(),
        'tipo' => $input['tipo'] ?? 'general',
        'vehiculo_id' => $input['vehiculo_id'] ?? null,
        'vehiculo_nombre' => $input['vehiculo_nombre'] ?? '',
        'nombre' => htmlspecialchars($input['nombre']),
        'telefono' => htmlspecialchars($input['telefono']),
        'correo' => htmlspecialchars($input['correo'] ?? ''),
        'mensaje' => htmlspecialchars($input['mensaje'] ?? ''),
        'estado' => 'pendiente',
        'fecha_solicitud' => $input['fecha_solicitud'] ?? date('Y-m-d H:i:s')
    ];
    
    $data['solicitudes'][] = $solicitud;
    
    if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT))) {
        echo json_encode([
            'success' => true, 
            'message' => 'Solicitud guardada exitosamente',
            'id' => $solicitud['id']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la solicitud']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>