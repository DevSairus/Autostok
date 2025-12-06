<?php
session_start();

// Si ya está logueado, redirigir al panel
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Cargar usuarios
    $usuariosFile = '../data/usuarios.json';
    
    if (file_exists($usuariosFile)) {
        $data = json_decode(file_get_contents($usuariosFile), true);
        $usuarios = $data['usuarios'] ?? [];
        
        // Buscar usuario
        foreach ($usuarios as &$usuario) {
            if ($usuario['username'] === $username && $usuario['activo']) {
                // Verificar contraseña
                if (password_verify($password, $usuario['password'])) {
                    // Login exitoso
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username'] = $usuario['username'];
                    $_SESSION['admin_nombre'] = $usuario['nombre'];
                    $_SESSION['admin_rol'] = $usuario['rol'];
                    $_SESSION['admin_id'] = $usuario['id'];
                    
                    // Actualizar último acceso
                    $usuario['ultimo_acceso'] = date('Y-m-d H:i:s');
                    file_put_contents($usuariosFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    
                    // Registrar log
                    require_once 'api/logs.php';
                    guardarLog('sistema', 'login', [
                        'usuario' => $username,
                        'rol' => $usuario['rol']
                    ], $username);
                    
                    header('Location: index.php');
                    exit;
                }
            }
        }
        
        $error = 'Usuario o contraseña incorrectos';
    } else {
        // Si no existe el archivo, usar credenciales por defecto
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = 'admin';
            $_SESSION['admin_nombre'] = 'Administrador';
            $_SESSION['admin_rol'] = 'super_admin';
            $_SESSION['admin_id'] = 1;
            
            header('Location: index.php');
            exit;
        }
        
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AUTO STOK Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background: rgba(0,0,0,0.8);
            border: 2px solid #FFD700;
            border-radius: 20px;
            padding: 50px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(255,215,0,0.3);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            font-size: 2.5rem;
            color: #FFD700;
            margin-bottom: 10px;
        }
        
        .logo p {
            color: rgba(255,255,255,0.7);
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #FFD700;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(255,215,0,0.3);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #FFD700;
            background: rgba(255,255,255,0.1);
            box-shadow: 0 0 15px rgba(255,215,0,0.2);
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255,215,0,0.4);
        }
        
        .error {
            background: rgba(255,0,0,0.2);
            border: 1px solid rgba(255,0,0,0.5);
            color: #ff6b6b;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🚗 AUTO STOK</h1>
            <p>Panel de Administración</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> AUTO STOK. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>