<?php
// Función para guardar logs
function guardarLog($tipo, $accion, $datos, $usuario) {
    // Detectar la ruta correcta según desde dónde se llame
    $logFile = file_exists(__DIR__ . '/../../data/logs.json') 
        ? __DIR__ . '/../../data/logs.json' 
        : (file_exists('../../data/logs.json') ? '../../data/logs.json' : '../data/logs.json');
    
    // Crear directorio si no existe
    if (!file_exists(__DIR__ . '/../../data')) {
        mkdir(__DIR__ . '/../../data', 0755, true);
    }
    
    // Cargar logs existentes
    $logs = file_exists($logFile) 
        ? json_decode(file_get_contents($logFile), true) 
        : ['logs' => []];
    
    if (!isset($logs['logs'])) {
        $logs['logs'] = [];
    }
    
    // Crear entrada de log
    $logEntry = [
        'id' => time() . '_' . rand(1000, 9999),
        'tipo' => $tipo, // 'cita', 'solicitud', 'vehiculo', 'servicio', etc.
        'accion' => $accion, // 'crear', 'actualizar', 'eliminar', 'cambio_estado'
        'datos' => $datos,
        'usuario' => $usuario,
        'fecha' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Agregar log
    $logs['logs'][] = $logEntry;
    
    // Mantener solo los últimos 1000 logs para no saturar
    if (count($logs['logs']) > 1000) {
        $logs['logs'] = array_slice($logs['logs'], -1000);
    }
    
    // Guardar
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return $logEntry['id'];
}

// Si se llama directamente al archivo
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    session_start();
    header('Content-Type: application/json');
    
    // Verificar autenticación
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Leer logs
        $logFile = __DIR__ . '/../../data/logs.json';
        $logs = file_exists($logFile) 
            ? json_decode(file_get_contents($logFile), true) 
            : ['logs' => []];
        
        // Filtros opcionales
        $tipo = $_GET['tipo'] ?? null;
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 100;
        
        $logsArray = $logs['logs'] ?? [];
        
        // Filtrar por tipo si se especifica
        if ($tipo) {
            $logsArray = array_filter($logsArray, function($log) use ($tipo) {
                return $log['tipo'] === $tipo;
            });
        }
        
        // Ordenar por fecha descendente
        usort($logsArray, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });
        
        // Limitar resultados
        $logsArray = array_slice($logsArray, 0, $limite);
        
        echo json_encode([
            'success' => true,
            'logs' => array_values($logsArray),
            'total' => count($logsArray)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
}
?>