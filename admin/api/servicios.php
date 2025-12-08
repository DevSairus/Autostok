<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$dataFile = '../../data/servicios.json';
$method = $_SERVER['REQUEST_METHOD'];

// Crear directorio si no existe
if (!file_exists('../../data')) {
    mkdir('../../data', 0755, true);
}

// Cargar datos
$data = file_exists($dataFile) 
    ? json_decode(file_get_contents($dataFile), true) 
    : ['servicios' => []];

if (!isset($data['servicios'])) {
    $data['servicios'] = [];
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
        echo json_encode(['success' => true, 'servicios' => $data['servicios']]);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['nombre'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        // Generar ID consecutivo automáticamente (ignorar ID enviado desde frontend)
        $input['id'] = getNextId($data['servicios']);
        
        // Agregar servicio
        $data['servicios'][] = $input;
        
        if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT))) {
            echo json_encode(['success' => true, 'message' => 'Servicio creado', 'id' => $input['id']]);
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
        foreach ($data['servicios'] as $key => $servicio) {
            if ($servicio['id'] == $input['id']) {
                $data['servicios'][$key] = $input;
                $found = true;
                break;
            }
        }
        
        if ($found) {
            if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT))) {
                echo json_encode(['success' => true, 'message' => 'Servicio actualizado']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Servicio no encontrado']);
        }
        break;
        
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit;
        }
        
        $newServicios = array_filter($data['servicios'], function($s) use ($input) {
            return $s['id'] != $input['id'];
        });
        
        $data['servicios'] = array_values($newServicios);
        
        if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT))) {
            echo json_encode(['success' => true, 'message' => 'Servicio eliminado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}
?>