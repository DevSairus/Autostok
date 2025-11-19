<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Crear directorio de uploads si no existe
$uploadDir = '../../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen'])) {
    $file = $_FILES['imagen'];
    
    // Validar errores
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error al subir archivo']);
        exit;
    }
    
    // Validar tipo de archivo
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido']);
        exit;
    }
    
    // Validar tamaño (máximo 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Archivo muy grande (máximo 5MB)']);
        exit;
    }
    
    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombreArchivo = uniqid() . '_' . time() . '.' . $extension;
    $rutaDestino = $uploadDir . $nombreArchivo;
    
    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
        // Retornar URL relativa
        $url = 'uploads/' . $nombreArchivo;
        echo json_encode([
            'success' => true,
            'url' => $url,
            'message' => 'Imagen subida exitosamente'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar archivo']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No se recibió archivo']);
}
?>