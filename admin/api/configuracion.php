<?php
session_start();
header('Content-Type: application/json');

$dataFile = '../../data/configuracion.json';
$method = $_SERVER['REQUEST_METHOD'];

// Crear directorio si no existe
if (!file_exists('../../data')) {
    mkdir('../../data', 0755, true);
}

// Cargar datos
$data = file_exists($dataFile) 
    ? json_decode(file_get_contents($dataFile), true) 
    : ['general' => [], 'pagos' => [], 'horarios' => [], 'sucursales' => [], 'nosotros' => []];

// Asegurar que existan las claves principales
if (!isset($data['general'])) $data['general'] = [];
if (!isset($data['pagos'])) $data['pagos'] = [];
if (!isset($data['horarios'])) $data['horarios'] = [];
if (!isset($data['nosotros'])) $data['nosotros'] = [];
if (!isset($data['sucursales'])) {
    $data['sucursales'] = [
        'sucursal1' => [],
        'sucursal2' => []
    ];
}

switch ($method) {
    case 'GET':
        echo json_encode($data);
        break;
        
    case 'POST':
        // Verificar autenticación
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['tipo'])) {
            echo json_encode(['success' => false, 'message' => 'Tipo no especificado']);
            exit;
        }
        
        $tipo = $input['tipo'];
        $datos = $input['datos'] ?? [];
        
        // Tipos válidos
        $tiposValidos = ['general', 'sucursal1', 'sucursal2'];
        
        if (!in_array($tipo, $tiposValidos)) {
            echo json_encode(['success' => false, 'message' => 'Tipo no válido: ' . $tipo]);
            exit;
        }
        
        // Manejar sucursales dentro del objeto sucursales
        if ($tipo === 'sucursal1' || $tipo === 'sucursal2') {
            if (!isset($data['sucursales'])) {
                $data['sucursales'] = [];
            }
            $data['sucursales'][$tipo] = $datos;
        } else {
            // Tipos normales
            $data[$tipo] = $datos;
        }
        
        if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            echo json_encode(['success' => true, 'message' => 'Configuración guardada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en el archivo']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}
?>