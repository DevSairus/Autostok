<?php
// Cargar configuración
$configData = file_exists(__DIR__ . '/data/configuracion.json') 
    ? json_decode(file_get_contents(__DIR__ . '/data/configuracion.json'), true) 
    : [];

$general = $configData['general'] ?? [];
$sucursales = $configData['sucursales'] ?? [];
$horarios = $configData['horarios'] ?? [];
?>

<footer class="footer">
  <div class="footer-container">
    <!-- Información General -->
    <div class="footer-col">
      <h3>🚗 <?php echo htmlspecialchars($general['nombreNegocio'] ?? 'Autostok'); ?></h3>
      <p>Tu concesionario de confianza</p>
      <div class="footer-socials">
        <a href="mailto:<?php echo htmlspecialchars($general['correoNegocio'] ?? 'contacto@autostok.com'); ?>" class="social-link">
          📧 <?php echo htmlspecialchars($general['correoNegocio'] ?? 'contacto@autostok.com'); ?>
        </a>
      </div>
    </div>

    <!-- Sucursal Norte -->
    <div class="footer-col">
      <h3>📍 <?php echo htmlspecialchars($sucursales['sucursal1']['nombre'] ?? 'Sucursal Norte'); ?></h3>
      <p>
        <strong>Dirección:</strong><br>
        <?php echo htmlspecialchars($sucursales['sucursal1']['direccion'] ?? 'Por configurar'); ?>
      </p>
      <p>
        <strong>📞 Teléfono:</strong><br>
        <a href="tel:<?php echo htmlspecialchars($sucursales['sucursal1']['telefono'] ?? ''); ?>">
          <?php echo htmlspecialchars($sucursales['sucursal1']['telefono'] ?? 'Por configurar'); ?>
        </a>
      </p>
      <p>
        <strong>📧 Correo:</strong><br>
        <a href="mailto:<?php echo htmlspecialchars($sucursales['sucursal1']['correo'] ?? ''); ?>">
          <?php echo htmlspecialchars($sucursales['sucursal1']['correo'] ?? 'Por configurar'); ?>
        </a>
      </p>
      <p>
        <strong>⏰ Horarios:</strong><br>
        Lun-Vie: <?php echo htmlspecialchars($sucursales['sucursal1']['horarioSemana'] ?? '8:00 AM - 6:00 PM'); ?><br>
        Sábados: <?php echo htmlspecialchars($sucursales['sucursal1']['horarioSabado'] ?? '9:00 AM - 2:00 PM'); ?>
      </p>
      <?php if (!empty($sucursales['sucursal1']['whatsapp'])): ?>
        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $sucursales['sucursal1']['whatsapp']); ?>" 
           target="_blank" class="btn-whatsapp-footer">
          💬 WhatsApp Norte
        </a>
      <?php endif; ?>
    </div>

    <!-- Sucursal Sur -->
    <div class="footer-col">
      <h3>📍 <?php echo htmlspecialchars($sucursales['sucursal2']['nombre'] ?? 'Sucursal Sur'); ?></h3>
      <p>
        <strong>Dirección:</strong><br>
        <?php echo htmlspecialchars($sucursales['sucursal2']['direccion'] ?? 'Por configurar'); ?>
      </p>
      <p>
        <strong>📞 Teléfono:</strong><br>
        <a href="tel:<?php echo htmlspecialchars($sucursales['sucursal2']['telefono'] ?? ''); ?>">
          <?php echo htmlspecialchars($sucursales['sucursal2']['telefono'] ?? 'Por configurar'); ?>
        </a>
      </p>
      <p>
        <strong>📧 Correo:</strong><br>
        <a href="mailto:<?php echo htmlspecialchars($sucursales['sucursal2']['correo'] ?? ''); ?>">
          <?php echo htmlspecialchars($sucursales['sucursal2']['correo'] ?? 'Por configurar'); ?>
        </a>
      </p>
      <p>
        <strong>⏰ Horarios:</strong><br>
        Lun-Vie: <?php echo htmlspecialchars($sucursales['sucursal2']['horarioSemana'] ?? '8:00 AM - 6:00 PM'); ?><br>
        Sábados: <?php echo htmlspecialchars($sucursales['sucursal2']['horarioSabado'] ?? '9:00 AM - 2:00 PM'); ?>
      </p>
      <?php if (!empty($sucursales['sucursal2']['whatsapp'])): ?>
        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $sucursales['sucursal2']['whatsapp']); ?>" 
           target="_blank" class="btn-whatsapp-footer">
          💬 WhatsApp Sur
        </a>
      <?php endif; ?>
    </div>

    <!-- Enlaces Rápidos -->
    <div class="footer-col">
      <h3>Enlaces Rápidos</h3>
      <ul class="footer-links">
        <li><a href="/index.php">Inicio</a></li>
        <li><a href="/vehiculos/catalogo.php">Vehículos</a></li>
        <li><a href="/servicios/servicios.php">Servicios</a></li>
        <li><a href="/nosotros.php">Nosotros</a></li>
        <li><a href="/contacto.php">Contacto</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($general['nombreNegocio'] ?? 'Autostok'); ?>. Todos los derechos reservados.</p>
  </div>
</footer>

<style>
.footer {
  background: linear-gradient(135deg, rgba(0,0,0,0.95), rgba(20,20,20,0.95));
  border-top: 3px solid #FFD700;
  padding: 60px 20px 20px;
  margin-top: 80px;
}

.footer-container {
  max-width: 1400px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 40px;
  margin-bottom: 40px;
}

.footer-col h3 {
  color: #FFD700;
  font-size: 1.3rem;
  margin-bottom: 20px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.footer-col p {
  color: rgba(255,255,255,0.8);
  line-height: 1.8;
  margin-bottom: 15px;
}

.footer-col strong {
  color: #FFD700;
  font-weight: 600;
}

.footer-col a {
  color: rgba(255,255,255,0.9);
  text-decoration: none;
  transition: color 0.3s ease;
}

.footer-col a:hover {
  color: #FFD700;
}

.btn-whatsapp-footer {
  display: inline-block;
  margin-top: 15px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #25D366, #128C7E);
  color: #fff;
  border-radius: 25px;
  font-weight: 600;
  text-align: center;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(37,211,102,0.3);
}

.btn-whatsapp-footer:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(37,211,102,0.5);
}

.footer-socials {
  margin-top: 15px;
}

.social-link {
  display: block;
  padding: 8px 0;
  color: rgba(255,255,255,0.8);
  font-size: 1rem;
}

.footer-links {
  list-style: none;
  padding: 0;
}

.footer-links li {
  margin-bottom: 12px;
}

.footer-links a {
  color: rgba(255,255,255,0.8);
  text-decoration: none;
  transition: all 0.3s ease;
  display: inline-block;
}

.footer-links a:hover {
  color: #FFD700;
  transform: translateX(5px);
}

.footer-bottom {
  text-align: center;
  padding-top: 30px;
  border-top: 1px solid rgba(255,215,0,0.2);
  color: rgba(255,255,255,0.6);
}

@media (max-width: 768px) {
  .footer-container {
    grid-template-columns: 1fr;
    gap: 30px;
  }
  
  .footer-col {
    text-align: center;
  }
  
  .btn-whatsapp-footer {
    display: block;
    max-width: 250px;
    margin: 15px auto 0;
  }
}
</style>