<?php
session_start();

// Si ya está autenticado, redirigir al panel
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Procesar login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // IMPORTANTE: Cambiar estas credenciales en producción
    $admin_user = 'admin';
    $admin_pass = 'admin123'; // Usar password_hash() en producción
    
    if ($username === $admin_user && $password === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Panel Administrador</title>
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="css/style.css">
  <link rel=icon href="../favicon.ico" type="image/x-icon">
</head>
<body>
  <div class="login-container">
    <div class="logo">
      <h1>AUTOSTOK</h1>
      <p>Panel de Administración</p>
    </div>

    <?php if ($error): ?>
      <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="username">Usuario</label>
        <input type="text" id="username" name="username" required autofocus>
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>
      </div>

      <button type="submit" class="btn-login">Iniciar Sesión</button>
    </form>

    <div class="back-link">
      <a href="../index.php">← Volver al sitio</a>
    </div>
  </div>
</body>
</html>