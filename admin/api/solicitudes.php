<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Rutas de archivos
$solicitudesFile = __DIR__ . '/../../data/solicitudes.json';
$productosFile = __DIR__ . '/../../data/productos.json';

// Cargar datos
$solicitudesData = file_exists($solicitudesFile) 
    ? json_decode(file_get_contents($solicitudesFile), true) 
    : ['solicitudes' => []];
$solicitudes = $solicitudesData['solicitudes'] ?? [];

try {
    if ($method === 'PUT') {
        // Actualizar estado de solicitud
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id']) || !isset($data['estado'])) {
            throw new Exception('Faltan datos requeridos');
        }
        
        $id = $data['id'];
        $nuevoEstado = $data['estado'];
        $encontrado = false;
        
        foreach ($solicitudes as &$solicitud) {
            if ($solicitud['id'] == $id) {
                $estadoAnterior = $solicitud['estado'] ?? 'pendiente';
                $solicitud['estado'] = $nuevoEstado;
                $encontrado = true;
                
                // SI ES SOLICITUD DE PRODUCTO Y SE MARCA COMO COMPLETADA
                if ($solicitud['tipo'] === 'producto' && 
                    $nuevoEstado === 'completada' && 
                    $estadoAnterior !== 'completada' &&
                    isset($solicitud['producto_id']) && 
                    isset($solicitud['cantidad'])) {
                    
                    // Descontar del stock
                    $productosData = file_exists($productosFile) 
                        ? json_decode(file_get_contents($productosFile), true) 
                        : ['productos' => []];
                    
                    $productos = $productosData['productos'] ?? [];
                    $stockDescontado = false;
                    
                    foreach ($productos as &$producto) {
                        if ($producto['id'] == $solicitud['producto_id']) {
                            $cantidadSolicitada = intval($solicitud['cantidad']);
                            $stockActual = intval($producto['stock'] ?? 0);
                            
                            if ($stockActual >= $cantidadSolicitada) {
                                $producto['stock'] = $stockActual - $cantidadSolicitada;
                                $stockDescontado = true;
                                
                                // Guardar productos actualizados
                                file_put_contents(
                                    $productosFile,
                                    json_encode(['productos' => $productos], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                );
                                
                                // Registrar log
                                registrarLog(
                                    'producto',
                                    'descuento_stock',
                                    $_SESSION['admin_username'] ?? 'Sistema',
                                    [
                                        'producto_id' => $producto['id'],
                                        'producto_nombre' => $producto['nombre'],
                                        'cantidad_descontada' => $cantidadSolicitada,
                                        'stock_anterior' => $stockActual,
                                        'stock_nuevo' => $producto['stock'],
                                        'solicitud_id' => $id
                                    ]
                                );
                            } else {
                                // Stock insuficiente - marcar en la solicitud
                                $solicitud['nota_sistema'] = "Advertencia: Stock insuficiente al completar. Disponible: {$stockActual}, Solicitado: {$cantidadSolicitada}";
                            }
                            break;
                        }
                    }
                }
                
                break;
            }
        }
        
        if (!$encontrado) {
            throw new Exception('Solicitud no encontrada');
        }
        
        // Guardar solicitudes
        file_put_contents(
            $solicitudesFile,
            json_encode(['solicitudes' => $solicitudes], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        // Registrar log
        registrarLog(
            'solicitud',
            'cambio_estado',
            $_SESSION['admin_username'] ?? 'Admin',
            ['solicitud_id' => $id, 'nuevo_estado' => $nuevoEstado]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Estado actualizado correctamente'
        ]);
        
    } elseif ($method === 'DELETE') {
        // Eliminar solicitud
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            throw new Exception('ID no proporcionado');
        }
        
        $id = $data['id'];
        $solicitudes = array_filter($solicitudes, fn($s) => $s['id'] != $id);
        $solicitudes = array_values($solicitudes);
        
        file_put_contents(
            $solicitudesFile,
            json_encode(['solicitudes' => $solicitudes], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        // Registrar log
        registrarLog(
            'solicitud',
            'eliminar',
            $_SESSION['admin_username'] ?? 'Admin',
            ['solicitud_id' => $id]
        );
        
        echo json_encode(['success' => true, 'message' => 'Solicitud eliminada']);
        
    } else {
        throw new Exception('Método no soportado');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function registrarLog($tipo, $accion, $usuario, $datos) {
    $logFile = __DIR__ . '/../../data/logs.json';
    $logsData = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : ['logs' => []];
    
    $logsData['logs'][] = [
        'fecha' => date('Y-m-d H:i:s'),
        'tipo' => $tipo,
        'accion' => $accion,
        'usuario' => $usuario,
        'datos' => $datos,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Mantener solo los últimos 500 logs
    if (count($logsData['logs']) > 500) {
        $logsData['logs'] = array_slice($logsData['logs'], -500);
    }
    
    file_put_contents($logFile, json_encode($logsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}