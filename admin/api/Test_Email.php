<?php
/**
 * Script de prueba para emails de citas a sucursales
 */
session_start();
header('Content-Type: text/html; charset=UTF-8');

// Verificar admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("⛔ Debes estar logueado como admin. <a href='../login.php'>Ir al login</a>");
}

require_once __DIR__ . '/mailer.php';

echo "<html><head><title>Prueba Email Citas</title><style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
h1 { color: #333; }
.test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9; }
.success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
.error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin: 5px; }
button:hover { background: #0056b3; }
.info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 4px; margin: 10px 0; }
</style></head><body><div class='container'>";

echo "<h1>🧪 Prueba de Emails de Citas a Sucursales</h1>";

// Cargar configuración
$config = cargarConfiguracion();

if (!$config) {
    echo "<div class='test-section error'>";
    echo "<h2>❌ Error Fatal</h2>";
    echo "<p>No se pudo cargar configuracion.json</p>";
    echo "</div></div></body></html>";
    exit;
}

// Mostrar configuración de sucursales
echo "<div class='test-section'>";
echo "<h2>📋 Configuración de Sucursales</h2>";

foreach ($config['sucursales'] as $key => $sucursal) {
    echo "<div class='info'>";
    echo "<strong>{$sucursal['nombre']}</strong><br>";
    echo "📧 Email: " . ($sucursal['correo'] ?? '❌ NO CONFIGURADO') . "<br>";
    echo "📱 WhatsApp: " . ($sucursal['whatsapp'] ?? '❌ NO CONFIGURADO') . "<br>";
    echo "🔑 ID: {$key}";
    echo "</div>";
}

echo "</div>";

// Formulario de prueba
echo "<div class='test-section'>";
echo "<h2>✉️ Enviar Email de Prueba</h2>";
echo "<form method='POST'>";
echo "<p><strong>Selecciona la sucursal:</strong></p>";

foreach ($config['sucursales'] as $key => $sucursal) {
    $checked = $key === 'sucursal1' ? 'checked' : '';
    echo "<label style='display: block; margin: 10px 0;'>";
    echo "<input type='radio' name='sucursal' value='{$key}' {$checked}> ";
    echo "{$sucursal['nombre']} ({$sucursal['correo']})";
    echo "</label>";
}

echo "<p style='margin-top: 20px;'><strong>Email del cliente (para notificación):</strong></p>";
echo "<input type='email' name='email_cliente' placeholder='cliente@example.com' style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";

echo "<p style='margin-top: 20px;'>";
echo "<button type='submit' name='enviar'>📤 Enviar Email de Prueba</button>";
echo "</p>";
echo "</form>";
echo "</div>";

// Procesar envío
if (isset($_POST['enviar'])) {
    $sucursalSeleccionada = $_POST['sucursal'] ?? 'sucursal1';
    $emailCliente = $_POST['email_cliente'] ?? '';
    
    echo "<div class='test-section'>";
    echo "<h2>🚀 Resultado del Envío</h2>";
    
    // Crear cita de prueba
    $citaPrueba = [
        'id' => time(),
        'servicio_id' => 999,
        'servicio_nombre' => 'Mantenimiento General (PRUEBA)',
        'nombre' => 'Cliente de Prueba',
        'telefono' => '+57 300 123 4567',
        'correo' => $emailCliente ?: 'test@example.com',
        'fecha' => date('Y-m-d', strtotime('+3 days')),
        'hora' => '10:00 AM',
        'sucursal' => $sucursalSeleccionada,
        'notas' => 'Esta es una cita de prueba para verificar el sistema de notificaciones',
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s')
    ];
    
    echo "<div class='info'>";
    echo "<strong>📝 Datos de la cita de prueba:</strong>";
    echo "<pre>" . json_encode($citaPrueba, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    echo "</div>";
    
    // Intentar enviar
    try {
        $resultados = enviarEmailNuevaCita($citaPrueba);
        
        echo "<h3>📊 Resultados del Envío:</h3>";
        
        foreach ($resultados as $destino => $resultado) {
            $clase = $resultado['success'] ? 'success' : 'error';
            $icono = $resultado['success'] ? '✅' : '❌';
            
            echo "<div class='test-section {$clase}'>";
            echo "<h4>{$icono} Destino: " . ucfirst($destino) . "</h4>";
            echo "<p><strong>Destinatario:</strong> " . ($resultado['destinatario'] ?? 'N/A') . "</p>";
            echo "<p><strong>Mensaje:</strong> " . $resultado['message'] . "</p>";
            
            if (!$resultado['success'] && isset($resultado['error_detallado'])) {
                echo "<details>";
                echo "<summary>Ver error detallado</summary>";
                echo "<pre>" . htmlspecialchars($resultado['error_detallado']) . "</pre>";
                echo "</details>";
            }
            
            echo "</div>";
        }
        
        // Mostrar link de WhatsApp
        $enlaceWhatsApp = generarNotificacionWhatsApp('cita', $citaPrueba);
        if ($enlaceWhatsApp) {
            echo "<div class='test-section success'>";
            echo "<h4>💬 Notificación WhatsApp Generada</h4>";
            echo "<p><a href='{$enlaceWhatsApp}' target='_blank' style='background: #25D366; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Abrir en WhatsApp</a></p>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='test-section error'>";
        echo "<h3>❌ Excepción Capturada</h3>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    }
    
    echo "</div>";
}

// Verificación de logs
echo "<div class='test-section'>";
echo "<h2>📄 Verificar Logs del Sistema</h2>";
echo "<p>Revisa los logs del sistema para ver información de depuración:</p>";

$logFile = __DIR__ . '/../../data/logs.json';
if (file_exists($logFile)) {
    $logs = json_decode(file_get_contents($logFile), true);
    $logsEmail = array_filter($logs['logs'] ?? [], function($log) {
        return $log['tipo'] === 'email';
    });
    
    $logsRecientes = array_slice(array_reverse($logsEmail), 0, 5);
    
    if (!empty($logsRecientes)) {
        echo "<h4>Últimos 5 logs de email:</h4>";
        foreach ($logsRecientes as $log) {
            echo "<div class='info'>";
            echo "<strong>{$log['fecha']}</strong> - {$log['accion']}<br>";
            echo "<pre style='margin-top: 5px;'>" . json_encode($log['datos'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
            echo "</div>";
        }
    } else {
        echo "<p>No hay logs de email registrados.</p>";
    }
} else {
    echo "<p>No existe el archivo de logs.</p>";
}

echo "</div>";

// Instrucciones
echo "<div class='test-section'>";
echo "<h2>📖 Instrucciones</h2>";
echo "<ol>";
echo "<li>Verifica que los correos de las sucursales estén configurados correctamente arriba</li>";
echo "<li>Selecciona la sucursal a la que quieres enviar la prueba</li>";
echo "<li>Opcionalmente, ingresa un email de cliente para recibir la confirmación</li>";
echo "<li>Haz clic en 'Enviar Email de Prueba'</li>";
echo "<li>Revisa tu bandeja de entrada (y spam) del correo de la sucursal</li>";
echo "<li>Si no llega, revisa los errores detallados arriba</li>";
echo "</ol>";
echo "</div>";

echo "</div></body></html>";
?>