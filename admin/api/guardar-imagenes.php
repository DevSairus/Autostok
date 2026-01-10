<?php
/**
 * API para guardar/actualizar imágenes del index
 * Elimina imágenes anteriores y actualiza configuración
 */

// Limpiar cualquier output previo
ob_start();

// Headers JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Rutas
    $uploadDir = __DIR__ . '/../../uploads/imagenes-home/';
    $rutaConfig = __DIR__ . '/../../data/configuracion.json';
    
    // Crear directorio si no existe
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de uploads');
        }
    }

    // Cargar configuración actual
    $config = [];
    if (file_exists($rutaConfig)) {
        $config = json_decode(file_get_contents($rutaConfig), true);
        if (!$config) $config = [];
    }
    
    if (!isset($config['imagenes'])) {
        $config['imagenes'] = [];
    }

    $seccionActualizada = null;

    // ===== PROCESAR SECCIÓN 1 =====
    if (isset($_POST['titulo1']) || isset($_POST['descripcion1']) || isset($_POST['enlace1']) || isset($_FILES['imagen1'])) {
        $seccion = 'index_seccion1';
        
        // Inicializar si no existe
        if (!isset($config['imagenes'][$seccion])) {
            $config['imagenes'][$seccion] = [
                'titulo' => '',
                'descripcion' => '',
                'enlace' => '',
                'imagen' => ''
            ];
        }
        
        // Actualizar textos
        if (isset($_POST['titulo1'])) {
            $config['imagenes'][$seccion]['titulo'] = trim($_POST['titulo1']);
        }
        if (isset($_POST['descripcion1'])) {
            $config['imagenes'][$seccion]['descripcion'] = trim($_POST['descripcion1']);
        }
        if (isset($_POST['enlace1'])) {
            $config['imagenes'][$seccion]['enlace'] = trim($_POST['enlace1']);
        }
        
        // Procesar nueva imagen
        if (isset($_FILES['imagen1']) && $_FILES['imagen1']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['imagen1'];
            
            // Validar tipo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($mimeType, $tiposPermitidos)) {
                throw new Exception('Tipo de imagen no válido. Solo JPG, PNG, WebP, GIF');
            }

            // Validar tamaño (5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('Imagen demasiado grande. Máximo 5MB');
            }

            // ELIMINAR imagen anterior si existe
            if (!empty($config['imagenes'][$seccion]['imagen'])) {
                $imagenAnterior = __DIR__ . '/../../' . $config['imagenes'][$seccion]['imagen'];
                if (file_exists($imagenAnterior)) {
                    @unlink($imagenAnterior);
                }
            }

            // Generar nombre único
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $nombreArchivo = 'home-vehiculos-' . time() . '.' . $extension;
            $rutaDestino = $uploadDir . $nombreArchivo;

            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
                throw new Exception('Error al subir la imagen al servidor');
            }

            // Actualizar ruta en config
            $config['imagenes'][$seccion]['imagen'] = 'uploads/imagenes-home/' . $nombreArchivo;
        }
        
        $seccionActualizada = 1;
    }

    // ===== PROCESAR SECCIÓN 2 =====
    if (isset($_POST['titulo2']) || isset($_POST['descripcion2']) || isset($_POST['enlace2']) || isset($_FILES['imagen2'])) {
        $seccion = 'index_seccion2';
        
        // Inicializar si no existe
        if (!isset($config['imagenes'][$seccion])) {
            $config['imagenes'][$seccion] = [
                'titulo' => '',
                'descripcion' => '',
                'enlace' => '',
                'imagen' => ''
            ];
        }
        
        // Actualizar textos
        if (isset($_POST['titulo2'])) {
            $config['imagenes'][$seccion]['titulo'] = trim($_POST['titulo2']);
        }
        if (isset($_POST['descripcion2'])) {
            $config['imagenes'][$seccion]['descripcion'] = trim($_POST['descripcion2']);
        }
        if (isset($_POST['enlace2'])) {
            $config['imagenes'][$seccion]['enlace'] = trim($_POST['enlace2']);
        }
        
        // Procesar nueva imagen
        if (isset($_FILES['imagen2']) && $_FILES['imagen2']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['imagen2'];
            
            // Validar tipo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($mimeType, $tiposPermitidos)) {
                throw new Exception('Tipo de imagen no válido. Solo JPG, PNG, WebP, GIF');
            }

            // Validar tamaño (5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('Imagen demasiado grande. Máximo 5MB');
            }

            // ELIMINAR imagen anterior si existe
            if (!empty($config['imagenes'][$seccion]['imagen'])) {
                $imagenAnterior = __DIR__ . '/../../' . $config['imagenes'][$seccion]['imagen'];
                if (file_exists($imagenAnterior)) {
                    @unlink($imagenAnterior);
                }
            }

            // Generar nombre único
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $nombreArchivo = 'home-servicios-' . time() . '.' . $extension;
            $rutaDestino = $uploadDir . $nombreArchivo;

            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
                throw new Exception('Error al subir la imagen al servidor');
            }

            // Actualizar ruta en config
            $config['imagenes'][$seccion]['imagen'] = 'uploads/imagenes-home/' . $nombreArchivo;
        }
        
        $seccionActualizada = 2;
    }

    if (!$seccionActualizada) {
        throw new Exception('No se recibieron datos para actualizar');
    }

    // Guardar configuración actualizada
    $jsonGuardado = file_put_contents(
        $rutaConfig,
        json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
    
    if ($jsonGuardado === false) {
        throw new Exception('Error al guardar el archivo de configuración');
    }

    // Limpiar buffer y enviar respuesta exitosa
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => "Sección $seccionActualizada actualizada correctamente",
        'data' => $config['imagenes']["index_seccion$seccionActualizada"]
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}