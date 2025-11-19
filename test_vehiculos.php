<?php
/**
 * Script de prueba para verificar que los vehículos se cargan correctamente
 * Acceder: http://tu-sitio.com/test_vehiculos.php
 */

echo "<h1>Debug - Carga de Vehículos</h1>";
echo "<hr>";

// Verificar existencia del archivo
$archivo = 'data/vehiculos.json';
echo "<h2>1. Verificando archivo...</h2>";
if (file_exists($archivo)) {
    echo "✅ El archivo existe: $archivo<br>";
    echo "Permisos: " . substr(sprintf('%o', fileperms($archivo)), -4) . "<br>";
} else {
    echo "❌ El archivo NO existe: $archivo<br>";
    echo "Directorio actual: " . getcwd() . "<br>";
    exit;
}

// Leer contenido
echo "<h2>2. Leyendo contenido...</h2>";
$contenido = file_get_contents($archivo);
if ($contenido === false) {
    echo "❌ Error al leer el archivo<br>";
    exit;
}
echo "✅ Archivo leído correctamente<br>";
echo "Tamaño: " . strlen($contenido) . " bytes<br>";

// Decodificar JSON
echo "<h2>3. Decodificando JSON...</h2>";
$data = json_decode($contenido, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Error al decodificar JSON: " . json_last_error_msg() . "<br>";
    echo "<pre>Contenido del archivo:\n" . htmlspecialchars($contenido) . "</pre>";
    exit;
}
echo "✅ JSON decodificado correctamente<br>";

// Verificar estructura
echo "<h2>4. Verificando estructura...</h2>";
if (!isset($data['vehiculos'])) {
    echo "❌ No existe la clave 'vehiculos' en el JSON<br>";
    echo "<pre>" . print_r($data, true) . "</pre>";
    exit;
}
echo "✅ Estructura correcta<br>";

$vehiculos = $data['vehiculos'];
echo "Total de vehículos: " . count($vehiculos) . "<br>";

// Mostrar vehículos
if (count($vehiculos) > 0) {
    echo "<h2>5. Vehículos encontrados:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #FFD700; color: #000;'>";
    echo "<th>ID</th><th>Marca</th><th>Modelo</th><th>Año</th><th>Precio</th><th>Imágenes</th>";
    echo "</tr>";
    
    foreach ($vehiculos as $v) {
        echo "<tr>";
        echo "<td>" . ($v['id'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($v['marca'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($v['modelo'] ?? 'N/A') . "</td>";
        echo "<td>" . ($v['anio'] ?? 'N/A') . "</td>";
        echo "<td>$" . number_format($v['precio'] ?? 0) . "</td>";
        echo "<td>" . count($v['imagenes'] ?? []) . " imágenes</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>6. Test de carga en JavaScript:</h2>";
    echo "<div id='test-container' style='display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;'></div>";
    echo "<script>";
    echo "const vehiculos = " . json_encode($vehiculos) . ";";
    echo "console.log('Vehículos en JS:', vehiculos);";
    echo "const container = document.getElementById('test-container');";
    echo "container.innerHTML = vehiculos.map(v => `
        <div style='border: 2px solid #FFD700; padding: 15px; border-radius: 10px; background: #000; color: #fff;'>
            <h3 style='color: #FFD700;'>\${v.marca} \${v.modelo}</h3>
            <p>Año: \${v.anio}</p>
            <p>Precio: $\${Number(v.precio).toLocaleString()}</p>
        </div>
    `).join('');";
    echo "</script>";
    
    echo "<h2 style='color: green;'>✅ TODO FUNCIONA CORRECTAMENTE</h2>";
    echo "<p>Los vehículos se cargan sin problemas. Si no aparecen en catalogo.php, el problema está en ese archivo específico.</p>";
} else {
    echo "<h2 style='color: red;'>⚠️ No hay vehículos en el JSON</h2>";
    echo "<p>Ejecuta setup.php para crear vehículos de ejemplo.</p>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #000;
        color: #fff;
    }
    h1, h2 {
        color: #FFD700;
    }
    table {
        margin: 20px 0;
        background: #1a1a1a;
        color: #fff;
    }
    th, td {
        padding: 10px;
        text-align: left;
    }
</style>