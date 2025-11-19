<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación para métodos protegidos
$method = $_SERVER['REQUEST_METHOD'];
if (in_array($method, ['PUT', 'DELETE']) && (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true)) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$dataFile = '../../data/citas.json';

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

switch ($method) {
    case 'GET':
        echo json_encode(['success' => true, 'citas' => $data['citas']]);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['nombre']) || !isset($input['fecha'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        // Crear cita
        $cita = [
            'id' => time(),
            'servicio_id' => $input['servicio_id'] ?? null,
            'servicio_nombre' => $input['servicio_nombre'] ?? '',
            'nombre' => $input['nombre'],
            'telefono' => $input['telefono'] ?? '',
            'correo' => $input['correo'] ?? '',
            'fecha' => $input['fecha'],
            'hora' => $input['hora'] ?? '',
            'notas' => $input['notas'] ?? '',
            'estado' => 'pendiente',
            'fecha_solicitud' => date('Y-m-d H:i:s')
        ];
        
        $data['citas'][] = $cita;
        
        if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT))) {
            // Aquí podrías enviar correos de notificación
            echo json_encode(['success' => true, 'message' => 'Cita solicitada', 'id' => $cita['id']]);
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
        foreach ($data['citas'] as $key => $cita) {
            if ($cita['id'] == $input['id']) {
                // Actualizar solo el estado
                if (isset($input['estado'])) {
                    $data['citas'][$key]['estado'] = $input['estado'];
                } else {
                    $data['citas'][$key] = array_merge($data['citas'][$key], $input);
                }
                $found = true;
                break;
            }
        }
        
        if ($found) {
            if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT))) {
                echo json_encode(['success' => true, 'message' => 'Cita actualizada']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Cita no encontrada']);
        }
        break;
        
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit;
        }
        
        $newCitas = array_filter($data['citas'], function($c) use ($input) {
            return $c['id'] != $input['id'];
        });
        
        $data['citas'] = array_values($newCitas);
        
        if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT))) {
            echo json_encode(['success' => true, 'message' => 'Cita eliminada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}
?>