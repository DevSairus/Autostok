<?php
/**
 * Instalador de PHPMailer SIN ZipArchive
 * Descarga los archivos directamente desde GitHub
 */

set_time_limit(300);

$vendorDir = __DIR__ . '/../../vendor';
$phpmailerDir = $vendorDir . '/phpmailer/phpmailer/src';

echo "<html><head><meta charset='utf-8'></head><body style='font-family: Arial; padding: 20px; background: #1a1a1a; color: #fff;'>";
echo "<h1 style='color: #FFD700;'>📦 Instalación Manual de PHPMailer</h1>";

try {
    // Crear directorios
    echo "<p>📁 Creando directorios...</p>";
    if (!file_exists($phpmailerDir)) {
        mkdir($phpmailerDir, 0755, true);
    }
    
    // URLs de archivos individuales en GitHub
    $files = [
        'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/v6.9.1/src/PHPMailer.php',
        'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/v6.9.1/src/SMTP.php',
        'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/v6.9.1/src/Exception.php',
        'OAuth.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/v6.9.1/src/OAuth.php',
        'POP3.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/v6.9.1/src/POP3.php'
    ];
    
    $success = 0;
    $failed = 0;
    
    foreach ($files as $filename => $url) {
        echo "<p>⬇️ Descargando $filename...</p>";
        
        $content = @file_get_contents($url, false, stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]));
        
        if ($content === false) {
            echo "<p style='color: #ff9800;'>⚠️ No se pudo descargar $filename</p>";
            $failed++;
            continue;
        }
        
        $destFile = $phpmailerDir . '/' . $filename;
        if (file_put_contents($destFile, $content)) {
            echo "<p style='color: #0f0;'>✓ $filename instalado (" . round(strlen($content) / 1024, 2) . " KB)</p>";
            $success++;
        } else {
            echo "<p style='color: #f00;'>✗ Error al guardar $filename</p>";
            $failed++;
        }
        
        flush();
        ob_flush();
    }
    
    if ($success >= 3) {
        echo "<hr>";
        echo "<h2 style='color: #0f0;'>✓ ¡Instalación Exitosa!</h2>";
        echo "<p>✓ Archivos instalados: $success de " . count($files) . "</p>";
        echo "<p>📂 Ubicación: <code style='background: #333; padding: 5px; border-radius: 3px;'>$phpmailerDir</code></p>";
        echo "<hr>";
        echo "<h3>✅ Siguiente paso:</h3>";
        echo "<ol style='line-height: 2;'>";
        echo "<li>Cierra esta ventana</li>";
        echo "<li>Regresa al panel de administración</li>";
        echo "<li>Ve a Configuración → Correo Electrónico</li>";
        echo "<li>Haz clic en 'Enviar Correo de Prueba'</li>";
        echo "</ol>";
        echo "<p><a href='../../index.php' style='color: #FFD700; text-decoration: none; background: #333; padding: 15px 30px; border-radius: 8px; display: inline-block; margin-top: 20px; font-weight: bold;'>← Volver al Panel Admin</a></p>";
    } else {
        throw new Exception("Solo se instalaron $success archivos de " . count($files) . ". Se necesitan al menos 3.");
    }
    
} catch (Exception $e) {
    echo "<hr>";
    echo "<p style='color: #f00; font-weight: bold; font-size: 1.2rem;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<hr>";
    echo "<h3 style='color: #FFD700;'>🔧 Instalación Manual Alternativa:</h3>";
    echo "<ol style='line-height: 2; font-size: 1.1rem;'>";
    echo "<li><strong>Descarga PHPMailer:</strong><br>";
    echo "<a href='https://github.com/PHPMailer/PHPMailer/releases/download/v6.9.1/PHPMailer-6.9.1.zip' style='color: #FFD700; font-size: 1.1rem;' target='_blank'>📥 Descargar ZIP desde GitHub</a></li>";
    echo "<li><strong>Extrae el ZIP</strong> en tu computadora</li>";
    echo "<li><strong>Copia la carpeta <code>src</code></strong> completa a:<br>";
    echo "<code style='background: #333; padding: 10px; border-radius: 5px; display: block; margin: 10px 0;'>$phpmailerDir</code></li>";
    echo "<li><strong>Recarga esta página</strong> para verificar la instalación</li>";
    echo "</ol>";
    
    echo "<hr>";
    echo "<h3 style='color: #FFD700;'>📋 O usa Composer:</h3>";
    echo "<ol style='line-height: 2;'>";
    echo "<li>Descarga Composer: <a href='https://getcomposer.org/download/' style='color: #FFD700;' target='_blank'>getcomposer.org</a></li>";
    echo "<li>Abre CMD en: <code style='background: #333; padding: 5px; border-radius: 3px;'>C:\\xampp\\htdocs\\AutoStok</code></li>";
    echo "<li>Ejecuta: <code style='background: #333; padding: 5px; border-radius: 3px;'>composer require phpmailer/phpmailer</code></li>";
    echo "</ol>";
}

echo "</body></html>";