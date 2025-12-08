<?php
/**
 * Endpoint público para solicitar citas desde el frontend
 * Envía notificaciones a la SUCURSAL SELECCIONADA
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    if (!$input || !isset($input['nombre']) || !isset($input['telefono']) || !isset($input['correo']) || !isset($input['fecha']) || !isset($input['hora'])) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos. Verifica que todos los campos estén llenos.']);
        exit;
    }
    
    // Validar que se haya seleccionado una sucursal
    if (empty($input['sucursal'])) {
        echo json_encode(['success' => false, 'message' => 'Por favor selecciona una sucursal para tu cita.']);
        exit;
    }
    
    // Validar email
    if (!filter_var($input['correo'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Correo electrónico no válido']);
        exit;
    }
    
    // Crear cita
    $cita = [
        'id' => time(),
        'servicio_id' => $input['servicio_id'] ?? null,
        'servicio_nombre' => htmlspecialchars($input['servicio_nombre'] ?? ''),
        'nombre' => htmlspecialchars($input['nombre']),
        'telefono' => htmlspecialchars($input['telefono']),
        'correo' => htmlspecialchars($input['correo']),
        'fecha' => $input['fecha'],
        'hora' => htmlspecialchars($input['hora']),
        'sucursal' => $input['sucursal'], // ← CRÍTICO: determina a qué sucursal enviar
        'notas' => htmlspecialchars($input['notas'] ?? ''),
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s')
    ];
    
    $data['citas'][] = $cita;
    
    if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        // Guardar log
        guardarLog('cita', 'crear', [
            'cita_id' => $cita['id'],
            'servicio' => $cita['servicio_nombre'],
            'cliente' => $cita['nombre'],
            'fecha' => $cita['fecha'],
            'sucursal' => $cita['sucursal']
        ], 'Sistema');
        
        // ENVIAR CORREOS AUTOMÁTICOS usando PHPMailer
        try {
            $resultadosEmail = enviarEmailNuevaCita($cita);
            
            // Generar enlace de WhatsApp para notificación
            $enlaceWhatsApp = generarNotificacionWhatsApp('cita', $cita);
            
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
                'message' => 'Cita solicitada exitosamente. Recibirás confirmación por correo.', 
                'id' => $cita['id'],
                'emails_enviados' => $emailsEnviados,
                'emails_fallidos' => $emailsFallidos,
                'whatsapp_link' => $enlaceWhatsApp
            ]);
        } catch (Exception $e) {
            // Si falla el envío de emails, aún así confirmamos la cita
            guardarLog('email', 'error_envio', [
                'cita_id' => $cita['id'],
                'error' => $e->getMessage()
            ], 'Sistema');
            
            echo json_encode([
                'success' => true, 
                'message' => 'Cita registrada. Las notificaciones se enviarán pronto.', 
                'id' => $cita['id'],
                'email_error' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la cita']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>