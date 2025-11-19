<?php
$configData = file_exists('data/configuracion.json') 
    ? json_decode(file_get_contents('data/configuracion.json'), true) 
    : [];
$config = $configData['general'] ?? [];
$horarios = $configData['horarios'] ?? [];
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
    
  </style>
</head>
<body>

  <header class="header">
    <div class="logo">🚗 <?php echo $config['nombreNegocio'] ?? 'AutoMarket'; ?></div>
    <nav>
      <a href="index.php">Inicio</a>
      <a href="../vehiculos/catalogo.php">Vehículos</a>
      <a href="../servicios/servicios.php">Servicios</a>
      <a href="nosotros.php">Nosotros</a>
      <a href="contacto.php">Contacto</a>
    </nav>
  </header>

  <main class="contacto-container">
    <div class="contacto-hero">
      <h1>Contáctanos</h1>
      <p>Estamos aquí para ayudarte. Envíanos un mensaje o contáctanos directamente por WhatsApp.</p>
    </div>

    <div class="contacto-grid">
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

      <div class="contacto-info-container">
        <div class="info-card">
          <h3><span class="icon">📍</span> Ubicación</h3>
          <div class="info-item">
            <?php echo htmlspecialchars($config['direccion'] ?? 'Calle 123 #45-67, Medellín, Colombia'); ?>
          </div>
        </div>

        <div class="info-card">
          <h3><span class="icon">📞</span> Contáctanos por WhatsApp</h3>
          <div class="whatsapp-buttons">
            <a href="https://wa.me/<?php echo str_replace(['+', ' '], '', $config['telefonoWhatsappVehiculos'] ?? '573001234567'); ?>?text=Hola, tengo una consulta sobre vehículos" 
               class="btn-whatsapp" target="_blank">
              <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
              </svg>
              Consultas sobre Vehículos
            </a>
            <a href="https://wa.me/<?php echo str_replace(['+', ' '], '', $config['telefonoWhatsappServicios'] ?? '573007654321'); ?>?text=Hola, tengo una consulta sobre servicios" 
               class="btn-whatsapp" target="_blank">
              <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
              </svg>
              Consultas sobre Servicios
            </a>
          </div>
        </div>

        <div class="info-card">
          <h3><span class="icon">📧</span> Correo Electrónico</h3>
          <div class="info-item">
            <a href="mailto:<?php echo $config['correoNegocio'] ?? 'info@automarket.com'; ?>" 
               style="color: #FFD700; text-decoration: none;">
              <?php echo $config['correoNegocio'] ?? 'info@automarket.com'; ?>
            </a>
          </div>
        </div>

        <div class="info-card">
          <h3><span class="icon">🕒</span> Horarios de Atención</h3>
          <div class="info-item">
            <strong>Lunes - Viernes:</strong> <?php echo $horarios['horarioSemana'] ?? '8:00 AM - 6:00 PM'; ?>
          </div>
          <div class="info-item">
            <strong>Sábados:</strong> <?php echo $horarios['horarioSabado'] ?? '9:00 AM - 2:00 PM'; ?>
          </div>
          <div class="info-item">
            <strong>Domingos:</strong> Cerrado
          </div>
        </div>
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
        mensaje: document.getElementById('mensaje').value
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

</body>
</html>