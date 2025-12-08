<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$dataFile = '../../data/vehiculos.json';
$method = $_SERVER['REQUEST_METHOD'];

// Crear directorio si no existe
if (!file_exists('../../data')) {
    mkdir('../../data', 0755, true);
}

// Cargar datos
$data = file_exists($dataFile) 
    ? json_decode(file_get_contents($dataFile), true) 
    : ['vehiculos' => []];

if (!isset($data['vehiculos'])) {
    $data['vehiculos'] = [];
}

// Función para obtener el siguiente ID consecutivo
function getNextId($items) {
    if (empty($items)) {
        return 1;
    }
    
    $maxId = 0;
    foreach ($items as $item) {
        if (isset($item['id']) && is_numeric($item['id'])) {
            $maxId = max($maxId, (int)$item['id']);
        }
    }
    
    return $maxId + 1;
}

switch ($method) {
    case 'GET':
        echo json_encode(['success' => true, 'vehiculos' => $data['vehiculos']]);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['marca']) || !isset($input['modelo'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        // Generar ID consecutivo automáticamente (ignorar ID enviado desde frontend)
        $input['id'] = getNextId($data['vehiculos']);
        
        // Agregar vehículo
        $data['vehiculos'][] = $input;
        
        if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT))) {
            echo json_encode(['success' => true, 'message' => 'Vehículo creado', 'id' => $input['id']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar']);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit;
        }
        
        $found = false;
        foreach ($data['vehiculos'] as $key => $vehiculo) {
            if ($vehiculo['id'] == $input['id']) {
                $data['vehiculos'][$key] = $input;
                $found = true;
                break;
            }
        }
        
        if ($found) {
            if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT))) {
                echo json_encode(['success' => true, 'message' => 'Vehículo actualizado']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado']);
        }
        break;
        
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit;
        }
        
        $newVehiculos = array_filter($data['vehiculos'], function($v) use ($input) {
            return $v['id'] != $input['id'];
        });
        
        $data['vehiculos'] = array_values($newVehiculos);
        
        if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT))) {
            echo json_encode(['success' => true, 'message' => 'Vehículo eliminado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}
?>