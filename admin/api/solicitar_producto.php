<?php
/**
 * Endpoint público para solicitar productos/repuestos desde el frontend
 * Envía notificaciones al CORREO DEL ALMACÉN
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Incluir sistema de logs y mailer
require_once __DIR__ . '/logs.php';
require_once __DIR__ . '/mailer.php';

$dataFile = '../../data/solicitudes.json';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    if (!$input || !isset($input['nombre']) || !isset($input['telefono'])) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }
    
    // Crear solicitud
    $solicitud = [
        'id' => time(),
        'tipo' => 'producto', // ← Tipo correcto: envía al ALMACÉN
        'producto_id' => $input['producto_id'] ?? null,
        'producto_nombre' => htmlspecialchars($input['producto_nombre'] ?? ''),
        'nombre' => htmlspecialchars($input['nombre']),
        'telefono' => htmlspecialchars($input['telefono']),
        'correo' => htmlspecialchars($input['correo'] ?? ''),
        'cantidad' => (int)($input['cantidad'] ?? 1),
        'notas' => htmlspecialchars($input['notas'] ?? ''),
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s')
    ];
    
    $data['solicitudes'][] = $solicitud;
    
    if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        // Guardar log
        guardarLog('solicitud', 'crear', [
            'solicitud_id' => $solicitud['id'],
            'tipo' => 'producto',
            'producto' => $solicitud['producto_nombre'],
            'cliente' => $solicitud['nombre'],
            'cantidad' => $solicitud['cantidad']
        ], 'Sistema');
        
        // ENVIAR CORREOS AUTOMÁTICOS AL ALMACÉN
        try {
            $resultadosEmail = enviarEmailNuevaSolicitud($solicitud);
            
            // Generar enlace de WhatsApp para almacén
            $enlaceWhatsApp = generarNotificacionWhatsApp('solicitud', $solicitud);
            
            // Verificar si los emails se enviaron exitosamente
            $emailsEnviados = [];
            $emailsFallidos = [];
            
            foreach ($resultadosEmail as $destino => $resultado) {
                if ($resultado['success']) {
                    $emailsEnviados[] = $destino;
                } else {
                    $emailsFallidos[] = $destino;
                }
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Solicitud enviada al almacén. Te contactaremos pronto.',
                'id' => $solicitud['id'],
                'emails_enviados' => $emailsEnviados,
                'emails_fallidos' => $emailsFallidos,
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
                'message' => 'Solicitud registrada. Las notificaciones se enviarán pronto.',
                'id' => $solicitud['id'],
                'email_error' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la solicitud']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>