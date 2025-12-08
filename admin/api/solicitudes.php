<?php
session_start();
header('Content-Type: application/json');

// Incluir sistema de logs y mailer
require_once __DIR__ . '/logs.php';
require_once __DIR__ . '/mailer.php';

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Permitir solo GET y POST sin autenticación para solicitudes públicas
    $method = $_SERVER['REQUEST_METHOD'];
    if (!in_array($method, ['GET', 'POST'])) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
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
    
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['nombre']) || !isset($input['tipo'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        // Crear solicitud
        $solicitud = [
            'id' => time(),
            'tipo' => $input['tipo'], // vehiculo, producto, servicio, general
            'nombre' => $input['nombre'],
            'telefono' => $input['telefono'] ?? '',
            'correo' => $input['correo'] ?? '',
            'mensaje' => $input['mensaje'] ?? '',
            'estado' => 'pendiente',
            'fecha_solicitud' => date('Y-m-d H:i:s')
        ];
        
        // Agregar datos específicos según el tipo
        if ($input['tipo'] === 'vehiculo' && isset($input['vehiculo_id'])) {
            $solicitud['vehiculo_id'] = $input['vehiculo_id'];
            $solicitud['vehiculo_nombre'] = $input['vehiculo_nombre'] ?? '';
        }
        
        if ($input['tipo'] === 'producto' && isset($input['producto_id'])) {
            $solicitud['producto_id'] = $input['producto_id'];
            $solicitud['producto_nombre'] = $input['producto_nombre'] ?? '';
        }
        
        if ($input['tipo'] === 'servicio' && isset($input['servicio_id'])) {
            $solicitud['servicio_id'] = $input['servicio_id'];
            $solicitud['servicio_nombre'] = $input['servicio_nombre'] ?? '';
        }
        
        $data['solicitudes'][] = $solicitud;
        
        if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            // Guardar log
            guardarLog('solicitud', 'crear', [
                'solicitud_id' => $solicitud['id'],
                'cliente' => $solicitud['nombre'],
                'tipo' => $solicitud['tipo']
            ], 'Sistema');
            
            // ENVIAR CORREOS AUTOMÁTICOS
            try {
                $resultadosEmail = enviarEmailNuevaSolicitud($solicitud);
                
                // Generar enlace de WhatsApp para notificación
                $enlaceWhatsApp = generarNotificacionWhatsApp('solicitud', $solicitud);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Solicitud registrada', 
                    'id' => $solicitud['id'],
                    'emails_enviados' => $resultadosEmail,
                    'whatsapp_link' => $enlaceWhatsApp
                ]);
            } catch (Exception $e) {
                // Si falla el envío de emails, aún así confirmamos la solicitud
                guardarLog('email', 'error_envio', [
                    'solicitud_id' => $solicitud['id'],
                    'error' => $e->getMessage()
                ], 'Sistema');
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Solicitud registrada (emails pendientes)', 
                    'id' => $solicitud['id'],
                    'email_error' => $e->getMessage()
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar']);
        }
        break;
        
    case 'PUT':
        // Verificar autenticación para PUT
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }
        
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
                    'referencia' => $solicitudActual['vehiculo_nombre'] ?? $solicitudActual['producto_nombre'] ?? 'N/A',
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $input['estado']
                ], $_SESSION['admin_username'] ?? 'Admin');
                
                // ENVIAR EMAIL DE CAMBIO DE ESTADO AL CLIENTE
                try {
                    $solicitudActualizada = array_merge($solicitudActual, ['estado' => $input['estado']]);
                    $resultadoEmail = enviarEmailCambioEstado('solicitud', $solicitudActualizada, $estadoAnterior, $input['estado']);
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Solicitud actualizada',
                        'email_enviado' => $resultadoEmail
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Solicitud actualizada (email pendiente)',
                        'email_error' => $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Solicitud no encontrada']);
        }
        break;
        
    case 'DELETE':
        // Verificar autenticación para DELETE
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }
        
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