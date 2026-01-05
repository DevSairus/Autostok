<?php
// Evitar cualquier output antes del JSON
ob_start();

// Headers JSON
header('Content-Type: application/json');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Directorio para guardar imágenes
    $uploadDir = __DIR__ . '/../../uploads/';
    
    // Crear directorio si no existe
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de uploads');
        }
    }

    // Cargar configuración actual
    $rutaConfig = __DIR__ . '/../../data/configuracion.json';
    if (!file_exists($rutaConfig)) {
        throw new Exception('Archivo de configuración no encontrado');
    }

    $config = json_decode(file_get_contents($rutaConfig), true);
    if (!isset($config['imagenes'])) {
        $config['imagenes'] = [];
    }

    // Procesar imagen 1
    if (isset($_POST['titulo1'], $_POST['descripcion1'], $_POST['enlace1'])) {
        $imagen1Path = null;

        // Si hay archivo nuevo
        if (isset($_FILES['imagen1']) && $_FILES['imagen1']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['imagen1'];
            
            // Validar tipo
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($file['type'], $tiposPermitidos)) {
                throw new Exception('Tipo de imagen no válido. Solo JPG, PNG, WebP');
            }

            // Validar tamaño (5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('Imagen demasiado grande. Máximo 5MB');
            }

            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $nombreArchivo = 'index_vehiculos.' . $extension;
            $rutaDestino = $uploadDir . $nombreArchivo;

            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
                throw new Exception('Error al subir la imagen');
            }

            $imagen1Path = 'uploads/' . $nombreArchivo;
        }

        // Actualizar config
        $config['imagenes']['index_seccion1'] = [
            'titulo' => $_POST['titulo1'],
            'descripcion' => $_POST['descripcion1'],
            'imagen' => $imagen1Path ?: ($config['imagenes']['index_seccion1']['imagen'] ?? 'uploads/index_vehiculos.jpg'),
            'enlace' => $_POST['enlace1']
        ];
    }

    // Procesar imagen 2
    if (isset($_POST['titulo2'], $_POST['descripcion2'], $_POST['enlace2'])) {
        $imagen2Path = null;

        // Si hay archivo nuevo
        if (isset($_FILES['imagen2']) && $_FILES['imagen2']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['imagen2'];
            
            // Validar tipo
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($file['type'], $tiposPermitidos)) {
                throw new Exception('Tipo de imagen no válido. Solo JPG, PNG, WebP');
            }

            // Validar tamaño (5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('Imagen demasiado grande. Máximo 5MB');
            }

            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $nombreArchivo = 'index_servicios.' . $extension;
            $rutaDestino = $uploadDir . $nombreArchivo;

            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
                throw new Exception('Error al subir la imagen');
            }

            $imagen2Path = 'uploads/' . $nombreArchivo;
        }

        // Actualizar config
        $config['imagenes']['index_seccion2'] = [
            'titulo' => $_POST['titulo2'],
            'descripcion' => $_POST['descripcion2'],
            'imagen' => $imagen2Path ?: ($config['imagenes']['index_seccion2']['imagen'] ?? 'uploads/index_servicios.jpg'),
            'enlace' => $_POST['enlace2']
        ];
    }

    // Guardar configuración
    if (file_put_contents($rutaConfig, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Imágenes guardadas correctamente'
        ]);
    } else {
        throw new Exception('Error al guardar la configuración');
    }

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>