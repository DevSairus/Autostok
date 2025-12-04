<?php
session_start();
header('Content-Type: application/json');

// Incluir sistema de logs
require_once 'logs.php';

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$dataFile = '../../data/solicitudes.json';
$method = $_SERVER['REQUEST_METHOD'];

// Crear directorio si no existe
if (!file_exists('../../data')) {
    mkdir('../../data', 0755, true);
}

// Cargar datos
$data = file_exists($dataFile) 
    ? json_decode(file_get_contents($dataFile), true) 
    : ['solicitudes' => []];

if (!isset($data['solicitudes'])) {
    $data['solicitudes'] = [];
}

switch ($method) {
    case 'GET':
        echo json_encode(['success' => true, 'solicitudes' => $data['solicitudes']]);
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit;
        }
        
        $found = false;
        $estadoAnterior = null;
        $solicitudActual = null;
        
        foreach ($data['solicitudes'] as $key => $solicitud) {
            if ($solicitud['id'] == $input['id']) {
                $estadoAnterior = $solicitud['estado'] ?? 'pendiente';
                $solicitudActual = $solicitud;
                
                // Actualizar estado
                if (isset($input['estado'])) {
                    $data['solicitudes'][$key]['estado'] = $input['estado'];
                }
                $found = true;
                break;
            }
        }
        
        if ($found) {
            if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                // Guardar log de cambio de estado
                guardarLog('solicitud', 'cambio_estado', [
                    'solicitud_id' => $input['id'],
                    'cliente' => $solicitudActual['nombre'] ?? 'Desconocido',
                    'tipo' => $solicitudActual['tipo'] ?? 'general',
                    'referencia' => $solicitudActual['vehiculo_nombre'] ?? 'N/A',
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $input['estado']
                ], $_SESSION['admin_username'] ?? 'Admin');
                
                echo json_encode(['success' => true, 'message' => 'Solicitud actualizada']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Solicitud no encontrada']);
        }
        break;
        
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit;
        }
        
        // Buscar la solicitud antes de eliminar
        $solicitudEliminada = null;
        foreach ($data['solicitudes'] as $solicitud) {
            if ($solicitud['id'] == $input['id']) {
                $solicitudEliminada = $solicitud;
                break;
            }
        }
        
        $newSolicitudes = array_filter($data['solicitudes'], function($s) use ($input) {
            return $s['id'] != $input['id'];
        });
        
        $data['solicitudes'] = array_values($newSolicitudes);
        
        if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            // Guardar log
            if ($solicitudEliminada) {
                guardarLog('solicitud', 'eliminar', [
                    'solicitud_id' => $input['id'],
                    'cliente' => $solicitudEliminada['nombre'] ?? 'Desconocido',
                    'tipo' => $solicitudEliminada['tipo'] ?? 'general'
                ], $_SESSION['admin_username'] ?? 'Admin');
            }
            
            echo json_encode(['success' => true, 'message' => 'Solicitud eliminada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}
?>