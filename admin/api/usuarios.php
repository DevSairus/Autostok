<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Solo super_admin puede gestionar usuarios
if ($_SESSION['admin_rol'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para gestionar usuarios']);
    exit;
}

$dataFile = '../../data/usuarios.json';
$method = $_SERVER['REQUEST_METHOD'];

// Crear directorio si no existe
if (!file_exists('../../data')) {
    mkdir('../../data', 0755, true);
}

// Cargar datos
$data = file_exists($dataFile) 
    ? json_decode(file_get_contents($dataFile), true) 
    : ['usuarios' => [], 'roles' => []];

if (!isset($data['usuarios'])) {
    $data['usuarios'] = [];
}

switch ($method) {
    case 'GET':
        // Ocultar contraseñas al retornar
        $usuariosSeguros = array_map(function($usuario) {
            unset($usuario['password']);
            return $usuario;
        }, $data['usuarios']);
        
        echo json_encode([
            'success' => true, 
            'usuarios' => $usuariosSeguros,
            'roles' => $data['roles']
        ]);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['username']) || !isset($input['password'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        // Verificar si el usuario ya existe
        foreach ($data['usuarios'] as $usuario) {
            if ($usuario['username'] === $input['username']) {
                echo json_encode(['success' => false, 'message' => 'El usuario ya existe']);
                exit;
            }
        }
        
        // Crear usuario
        $nuevoUsuario = [
            'id' => time(),
            'username' => htmlspecialchars($input['username']),
            'password' => password_hash($input['password'], PASSWORD_DEFAULT),
            'nombre' => htmlspecialchars($input['nombre']),
            'email' => htmlspecialchars($input['email']),
            'rol' => $input['rol'] ?? 'visualizador',
            'activo' => true,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'ultimo_acceso' => null
        ];
        
        $data['usuarios'][] = $nuevoUsuario;
        
        // Guardar logs
        require_once __DIR__ . '/logs.php';
        guardarLog('usuario', 'crear', [
            'usuario_id' => $nuevoUsuario['id'],
            'username' => $nuevoUsuario['username'],
            'rol' => $nuevoUsuario['rol']
        ], $_SESSION['admin_username']);
        
        if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente', 'id' => $nuevoUsuario['id']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar']);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit;
        }
        
        $found = false;
        foreach ($data['usuarios'] as $key => $usuario) {
            if ($usuario['id'] == $input['id']) {
                // No permitir cambiar el super admin
                if ($usuario['rol'] === 'super_admin' && $input['rol'] !== 'super_admin') {
                    echo json_encode(['success' => false, 'message' => 'No se puede cambiar el rol del super admin']);
                    exit;
                }
                
                // Actualizar datos
                $data['usuarios'][$key]['nombre'] = htmlspecialchars($input['nombre']);
                $data['usuarios'][$key]['email'] = htmlspecialchars($input['email']);
                $data['usuarios'][$key]['rol'] = $input['rol'];
                $data['usuarios'][$key]['activo'] = $input['activo'] ?? true;
                
                // Actualizar contraseña solo si se proporciona
                if (!empty($input['password'])) {
                    $data['usuarios'][$key]['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
                }
                
                $found = true;
                
                // Guardar log
                require_once __DIR__ . '/logs.php';
                guardarLog('usuario', 'actualizar', [
                    'usuario_id' => $input['id'],
                    'username' => $usuario['username'],
                    'cambios' => array_keys($input)
                ], $_SESSION['admin_username']);
                
                break;
            }
        }
        
        if ($found) {
            if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                echo json_encode(['success' => true, 'message' => 'Usuario actualizado']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
        break;
        
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit;
        }
        
        // No permitir eliminar al super admin o a sí mismo
        foreach ($data['usuarios'] as $usuario) {
            if ($usuario['id'] == $input['id']) {
                if ($usuario['rol'] === 'super_admin') {
                    echo json_encode(['success' => false, 'message' => 'No se puede eliminar al super admin']);
                    exit;
                }
                if ($usuario['username'] === $_SESSION['admin_username']) {
                    echo json_encode(['success' => false, 'message' => 'No puedes eliminarte a ti mismo']);
                    exit;
                }
            }
        }
        
        // Buscar usuario para log
        $usuarioEliminado = null;
        foreach ($data['usuarios'] as $usuario) {
            if ($usuario['id'] == $input['id']) {
                $usuarioEliminado = $usuario;
                break;
            }
        }
        
        $newUsuarios = array_filter($data['usuarios'], function($u) use ($input) {
            return $u['id'] != $input['id'];
        });
        
        $data['usuarios'] = array_values($newUsuarios);
        
        // Guardar log
        if ($usuarioEliminado) {
            require_once __DIR__ . '/logs.php';
            guardarLog('usuario', 'eliminar', [
                'usuario_id' => $input['id'],
                'username' => $usuarioEliminado['username'],
                'rol' => $usuarioEliminado['rol']
            ], $_SESSION['admin_username']);
        }
        
        if (file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            echo json_encode(['success' => true, 'message' => 'Usuario eliminado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}
?>