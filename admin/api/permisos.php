<?php
// Sistema de verificación de permisos

function tienePermiso($permiso) {
    if (!isset($_SESSION['admin_rol'])) {
        return false;
    }
    
    $rol = $_SESSION['admin_rol'];
    
    // Super admin tiene todos los permisos
    if ($rol === 'super_admin') {
        return true;
    }
    
    // Cargar roles y permisos
    $usuariosFile = __DIR__ . '/../data/usuarios.json';
    if (!file_exists($usuariosFile)) {
        return false;
    }
    
    $data = json_decode(file_get_contents($usuariosFile), true);
    $roles = $data['roles'] ?? [];
    
    if (!isset($roles[$rol])) {
        return false;
    }
    
    $permisos = $roles[$rol]['permisos'] ?? [];
    
    // Verificar si tiene el permiso exacto
    if (in_array($permiso, $permisos)) {
        return true;
    }
    
    // Verificar permisos con wildcard (ejemplo: vehiculos.*)
    foreach ($permisos as $permisoRol) {
        if (str_ends_with($permisoRol, '.*')) {
            $prefijo = str_replace('.*', '', $permisoRol);
            if (str_starts_with($permiso, $prefijo)) {
                return true;
            }
        }
    }
    
    return false;
}

function verificarPermiso($permiso) {
    if (!tienePermiso($permiso)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción']);
        exit;
    }
}

function obtenerRolNombre($rol) {
    $nombres = [
        'super_admin' => 'Super Administrador',
        'administrador' => 'Administrador',
        'ventas' => 'Vendedor',
        'taller' => 'Taller',
        'visualizador' => 'Visualizador'
    ];
    
    return $nombres[$rol] ?? $rol;
}
?>