<?php
$configData = file_exists('data/configuracion.json') 
    ? json_decode(file_get_contents('data/configuracion.json'), true) 
    : [];
$config = $configData['general'] ?? [];
$sucursales = $configData['sucursales'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contacto - <?php echo $config['nombreNegocio'] ?? 'AutoMarket'; ?></title>
  <link rel="stylesheet" href="style.css">
  <link rel=icon href="favicon.ico" type="image/x-icon">
  <style>
    /* Header Responsive */
    .header {
      position: fixed;
      top: 0;
      width: 100%;
      background: rgba(0,0,0,0.95);
      backdrop-filter: blur(10px);
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 1000;
      border-bottom: 2px solid #FFD700;
      box-shadow: 0 4px 20px rgba(255,215,0,0.2);
    }

    .logo {
      font-size: 1.5rem;
      font-weight: bold;
      color: #FFD700;
      text-shadow: 0 0 10px rgba(255,215,0,0.5);
      cursor: pointer;
      white-space: nowrap;
    }

    .header nav {
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .header nav a {
      color: #fff;
      text-decoration: none;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      position: relative;
      white-space: nowrap;
    }

    .header nav a::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 0;
      height: 2px;
      background: #FFD700;
      transition: width 0.3s ease;
    }

    .header nav a:hover::after {
      width: 100%;
    }

    .header nav a.active {
      color: #FFD700;
      font-weight: 600;
    }

    .menu-toggle {
      display: none;
      flex-direction: column;
      cursor: pointer;
      gap: 5px;
      z-index: 1001;
    }

    .menu-toggle span {
      width: 25px;
      height: 3px;
      background: #FFD700;
      border-radius: 2px;
      transition: all 0.3s ease;
    }

    .menu-toggle.active span:nth-child(1) {
      transform: rotate(45deg) translate(8px, 8px);
    }

    .menu-toggle.active span:nth-child(2) {
      opacity: 0;
    }

    .menu-toggle.active span:nth-child(3) {
      transform: rotate(-45deg) translate(7px, -7px);
    }

    /* Responsive Menu */
    @media (max-width: 768px) {
      .menu-toggle {
        display: flex;
      }

      .header nav {
        position: fixed;
        top: 0;
        right: -100%;
        width: 70%;
        max-width: 300px;
        height: 100vh;
        background: rgba(0,0,0,0.98);
        flex-direction: column;
        justify-content: center;
        gap: 30px;
        padding: 20px;
        transition: right 0.4s ease;
        border-left: 2px solid #FFD700;
      }

      .header nav.active {
        right: 0;
      }

      .header nav a {
        font-size: 1.2rem;
        text-align: center;
        padding: 10px 0;
      }

      .header nav a::after {
        bottom: 0;
      }
    }

    @media (max-width: 480px) {
      .logo {
        font-size: 1.2rem;
      }

      .header {
        padding: 12px 15px;
      }

      .header nav {
        width: 80%;
        max-width: 250px;
      }

      .header nav a {
        font-size: 1.1rem;
      }
    }

    .contacto-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }

    .contacto-hero {
      text-align: center;
      margin-bottom: 3rem;
    }

    .contacto-hero h1 {
      font-size: 2.5rem;
      color: #FFD700;
      margin-bottom: 1rem;
    }

    .contacto-hero p {
      font-size: 1.1rem;
      color: #e0e0e0;
    }

    /* SUCURSALES EN LA PARTE SUPERIOR */
    .sucursales-header {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      margin-bottom: 4rem;
    }

    .sucursal-card {
      background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
      border: 2px solid #FFD700;
      border-radius: 12px;
      padding: 2rem;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .sucursal-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
    }

    .sucursal-card h3 {
      color: #FFD700;
      margin-bottom: 1.5rem;
      font-size: 1.3rem;
    }

    .sucursal-info {
      margin-bottom: 1rem;
    }

    .sucursal-info p {
      color: #e0e0e0;
      margin: 0.5rem 0;
    }

    .sucursal-info strong {
      color: #FFD700;
    }

    .sucursal-botones {
      display: flex;
      gap: 0.75rem;
      margin-top: 1.5rem;
      flex-wrap: wrap;
    }

    .btn-whatsapp-sucursal {
      flex: 1;
      min-width: 120px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.75rem;
      background-color: #25D366;
      color: white;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s ease;
      font-size: 0.9rem;
    }

    .btn-whatsapp-sucursal:hover {
      background-color: #1ea856;
    }

    .btn-llamar {
      flex: 1;
      min-width: 120px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.75rem;
      background-color: #0066cc;
      color: white;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s ease;
      font-size: 0.9rem;
    }

    .btn-llamar:hover {
      background-color: #0052a3;
    }

    .contacto-form-container {
      background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
      border: 2px solid #FFD700;
      border-radius: 12px;
      padding: 2rem;
    }

    .contacto-form-container h2 {
      color: #FFD700;
      margin-bottom: 1.5rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      color: #e0e0e0;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 0.75rem;
      background-color: #1a1a1a;
      border: 1px solid #FFD700;
      border-radius: 6px;
      color: #e0e0e0;
      font-family: inherit;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #FFD700;
      box-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
    }

    .btn-enviar {
      width: 100%;
      padding: 1rem;
      background-color: #FFD700;
      color: #000;
      border: none;
      border-radius: 6px;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      transition: opacity 0.3s ease, transform 0.2s ease;
    }

    .btn-enviar:hover {
      opacity: 0.9;
      transform: scale(1.02);
    }

    .btn-enviar:active {
      transform: scale(0.98);
    }

    .mensaje-exito {
      display: none;
      background-color: #25D366;
      color: white;
      padding: 1rem;
      border-radius: 6px;
      margin-bottom: 1.5rem;
      text-align: center;
      font-weight: 600;
    }

    /* INFORMACIÓN GENERAL */
    .contacto-info-container {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .info-card {
      background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
      border: 2px solid #FFD700;
      border-radius: 12px;
      padding: 1.5rem;
    }

    .info-card h3 {
      color: #FFD700;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .info-item {
      color: #e0e0e0;
      margin-bottom: 0.75rem;
    }

    .info-item a {
      color: #FFD700;
      text-decoration: none;
      transition: opacity 0.3s ease;
    }

    .info-item a:hover {
      opacity: 0.8;
      text-decoration: underline;
    }

    .icon {
      font-size: 1.3rem;
    }
  </style>
</head>
<body>

  <header class="header">
    <div class="logo">🚗 <?php echo $config['nombreNegocio'] ?? 'AutoMarket'; ?></div>
    
    <div class="menu-toggle" id="menuToggle">
      <span></span>
      <span></span>
      <span></span>
    </div>
    
    <nav id="navMenu">
      <a href="index.php" class="nav-link">Inicio</a>
      <a href="../vehiculos/catalogo.php" class="nav-link">Vehículos</a>
      <a href="../servicios/servicios.php" class="nav-link">Servicios</a>
      <a href="nosotros.php" class="nav-link">Nosotros</a>
      <a href="contacto.php" class="nav-link active">Contacto</a>
    </nav>
  </header>

  <main class="contacto-container">
    <div class="contacto-hero">
      <h1>Contáctanos</h1>
      <p>Estamos aquí para ayudarte. Visita cualquiera de nuestras sucursales o envíanos un mensaje.</p>
    </div>

    <!-- SUCURSALES EN LA PARTE SUPERIOR -->
    <?php if (!empty($sucursales)): ?>
    <div class="sucursales-header">
      <?php foreach ($sucursales as $sucursal): ?>
      <div class="sucursal-card">
        <h3>📍 <?php echo htmlspecialchars($sucursal['nombre']); ?></h3>
        
        <div class="sucursal-info">
          <p><?php echo htmlspecialchars($sucursal['direccion']); ?></p>
        </div>

        <div class="sucursal-info">
          <p>
            <strong>Teléfono:</strong> 
            <a href="tel:<?php echo str_replace(' ', '', $sucursal['telefono']); ?>">
              <?php echo htmlspecialchars($sucursal['telefono']); ?>
            </a>
          </p>
        </div>

        <div class="sucursal-info">
          <p>
            <strong>L-V:</strong> <?php echo htmlspecialchars($sucursal['horarioSemana']); ?><br>
            <strong>Sáb:</strong> <?php echo htmlspecialchars($sucursal['horarioSabado']); ?>
          </p>
        </div>

        <!-- Botones de contacto rápido -->
        <div class="sucursal-botones">
          <a href="https://wa.me/<?php echo str_replace(['+', ' '], '', $config['telefonoWhatsappServicios'] ?? '573007654321'); ?>?text=Hola, quiero consultar sobre la sucursal <?php echo urlencode($sucursal['nombre']); ?>" 
             class="btn-whatsapp-sucursal" target="_blank">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            WhatsApp
          </a>
          <a href="tel:<?php echo str_replace(' ', '', $sucursal['telefono']); ?>" class="btn-llamar">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
              <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"/>
            </svg>
            Llamar
          </a>
        </div>

        <!-- Mapa -->
        <?php if (!empty($sucursal['mapa'])): ?>
        <div style="margin-top: 1.5rem; border-radius: 8px; overflow: hidden; height: 250px;">
          <?php echo $sucursal['mapa']; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- FORMULARIO -->
    <div style="margin-top: 3rem;">
      <div class="contacto-form-container">
        <h2>Envíanos un Mensaje</h2>
        <div id="mensajeExito" class="mensaje-exito">
          ✓ Mensaje enviado exitosamente. Te contactaremos pronto.
        </div>
        <form id="formContacto">
          <div class="form-group">
            <label>Nombre Completo *</label>
            <input type="text" id="nombre" required>
          </div>
          <div class="form-group">
            <label>Teléfono *</label>
            <input type="tel" id="telefono" required>
          </div>
          <div class="form-group">
            <label>Correo Electrónico *</label>
            <input type="email" id="correo" required>
          </div>
          <div class="form-group">
            <label>Mensaje *</label>
            <textarea id="mensaje" rows="5" required></textarea>
          </div>
          <button type="submit" class="btn-enviar">Enviar Mensaje</button>
        </form>
      </div>
    </div>
  </main>

  <?php include 'footer.php'; ?>

  <script>
    document.getElementById('formContacto').addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const datos = {
        tipo: 'contacto',
        nombre: document.getElementById('nombre').value,
        telefono: document.getElementById('telefono').value,
        correo: document.getElementById('correo').value,
        mensaje: document.getElementById('mensaje').value,
        correos_destino: [
          '<?php echo $config['correoNegocio'] ?? 'contacto@autostok.com'; ?>',
          '<?php echo $config['correoCallCenter'] ?? 'callcenter@autostok.com'; ?>'
        ]
      };
      
      try {
        const response = await fetch('api/guardar_solicitud.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(datos)
        });
        
        const result = await response.json();
        
        if (result.success) {
          document.getElementById('mensajeExito').style.display = 'block';
          document.getElementById('formContacto').reset();
          setTimeout(() => {
            document.getElementById('mensajeExito').style.display = 'none';
          }, 5000);
        } else {
          alert('Error al enviar el mensaje. Por favor intenta nuevamente.');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Error al enviar el mensaje. Por favor intenta nuevamente.');
      }
    });
  </script>

  <script>
    // Menú mobile responsive
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');

    menuToggle.addEventListener('click', () => {
      menuToggle.classList.toggle('active');
      navMenu.classList.toggle('active');
    });

    // Cerrar menú al hacer clic en un enlace
    navMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        menuToggle.classList.remove('active');
        navMenu.classList.remove('active');
      });
    });

    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.header')) {
        menuToggle.classList.remove('active');
        navMenu.classList.remove('active');
      }
    });
  </script>

</body>
</html>