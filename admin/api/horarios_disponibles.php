<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $fecha = $_GET['fecha'] ?? null;
    $sucursal = $_GET['sucursal'] ?? null;
    
    if (!$fecha || !$sucursal) {
        echo json_encode([
            'success' => false,
            'message' => 'Fecha y sucursal son requeridos'
        ]);
        exit;
    }
    
    // Cargar citas
    $citasFile = __DIR__ . '/../../data/citas.json';
    
    if (!file_exists($citasFile)) {
        echo json_encode([
            'success' => true,
            'horarios_ocupados' => []
        ]);
        exit;
    }
    
    $citasData = json_decode(file_get_contents($citasFile), true);
    $citas = $citasData['citas'] ?? [];
    
    // Filtrar citas de esa fecha, sucursal y que estén confirmadas o pendientes
    $horariosOcupados = [];
    
    foreach ($citas as $cita) {
        // Verificar fecha
        if ($cita['fecha'] !== $fecha) {
            continue;
        }
        
        // Verificar sucursal
        if ($cita['sucursal'] !== $sucursal) {
            continue;
        }
        
        // Verificar estado (solo confirmadas y pendientes bloquean el horario)
        $estado = $cita['estado'] ?? 'pendiente';
        if ($estado !== 'confirmada' && $estado !== 'pendiente') {
            continue;
        }
        
        // Agregar hora ocupada
        if (!empty($cita['hora'])) {
            $horariosOcupados[] = $cita['hora'];
        }
    }
    
    // Eliminar duplicados
    $horariosOcupados = array_unique($horariosOcupados);
    $horariosOcupados = array_values($horariosOcupados);
    
    echo json_encode([
        'success' => true,
        'horarios_ocupados' => $horariosOcupados,
        'fecha' => $fecha,
        'sucursal' => $sucursal,
        'total_ocupados' => count($horariosOcupados)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}