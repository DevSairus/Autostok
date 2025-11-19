<?php
// Detectar si estamos en una subcarpeta
$base = '';
if (strpos($_SERVER['PHP_SELF'], '/vehiculos/') !== false || strpos($_SERVER['PHP_SELF'], '/servicios/') !== false) {
    $base = '../';
}

$configData = file_exists($base . 'data/configuracion.json') 
    ? json_decode(file_get_contents($base . 'data/configuracion.json'), true) 
    : [];
$config = $configData['general'] ?? [];
$horarios = $configData['horarios'] ?? [];
?>
<style>
  .footer {
    background: linear-gradient(180deg, #000 0%, #0a0a0a 100%);
    border-top: 3px solid #FFD700;
    padding: 60px 20px 20px;
    margin-top: 80px;
  }

  .footer-container {
    max-width: 1400px;
    margin: 0 auto;
  }

  .footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 40px;
    margin-bottom: 40px;
  }

  .footer-section h3 {
    color: #FFD700;
    font-size: 1.5rem;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .footer-section p,
  .footer-section a {
    color: rgba(255,255,255,0.7);
    line-height: 1.8;
    font-size: 1rem;
    text-decoration: none;
    transition: color 0.3s ease;
  }

  .footer-section a:hover {
    color: #FFD700;
  }

  .footer-links {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .footer-links a {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .footer-links a::before {
    content: '→';
    color: #FFD700;
    font-weight: bold;
  }

  .contact-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 15px;
  }

  .contact-icon {
    color: #FFD700;
    font-size: 1.3rem;
    min-width: 25px;
  }

  .social-links {
    display: flex;
    gap: 15px;
    margin-top: 20px;
  }

  .social-link {
    width: 45px;
    height: 45px;
    background: rgba(255,215,0,0.1);
    border: 2px solid rgba(255,215,0,0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    transition: all 0.3s ease;
  }

  .social-link:hover {
    background: #FFD700;
    border-color: #FFD700;
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(255,215,0,0.4);
  }

  .social-link:hover * {
    color: #000 !important;
  }

  .footer-bottom {
    border-top: 1px solid rgba(255,215,0,0.2);
    padding-top: 30px;
    text-align: center;
    color: rgba(255,255,255,0.6);
  }

  .footer-bottom p {
    margin: 10px 0;
  }

  .footer-logo {
    font-size: 2.5rem;
    color: #FFD700;
    font-weight: bold;
    margin-bottom: 15px;
    text-shadow: 0 0 10px rgba(255,215,0,0.3);
  }

  @media (max-width: 768px) {
    .footer-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<footer class="footer">
  <div class="footer-container">
    <div class="footer-grid">
      <!-- Columna 1: Sobre Nosotros -->
      <div class="footer-section">
        <div class="footer-logo">🚗 <?php echo $config['nombreNegocio'] ?? 'AutoMarket'; ?></div>
        <p>
          Tu concesionario de confianza. Ofrecemos los mejores vehículos y servicios automotrices 
          con más de 10 años de experiencia en el mercado.
        </p>
        <div class="social-links">
          <a href="#" class="social-link" title="Facebook">
            <svg width="20" height="20" fill="#FFD700" viewBox="0 0 24 24">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
          </a>
          <a href="#" class="social-link" title="Instagram">
            <svg width="20" height="20" fill="#FFD700" viewBox="0 0 24 24">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
          </a>
          <a href="#" class="social-link" title="Twitter">
            <svg width="20" height="20" fill="#FFD700" viewBox="0 0 24 24">
              <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
            </svg>
          </a>
        </div>
      </div>

      <!-- Columna 2: Enlaces Rápidos -->
      <div class="footer-section">
        <h3>Enlaces Rápidos</h3>
        <div class="footer-links">
          <a href="../index.php">Inicio</a>
          <a href="../vehiculos/catalogo.php">Catálogo de Vehículos</a>
          <a href="../servicios/servicios.php">Servicios</a>
          <a href="../nosotros.php">Nosotros</a>
          <a href="../contacto.php">Contacto</a>
        </div>
      </div>

      <!-- Columna 3: Contacto -->
      <div class="footer-section">
        <h3>Contacto</h3>
        <div class="contact-item">
          <span class="contact-icon">📍</span>
          <div>
            <p><?php echo htmlspecialchars($config['direccion'] ?? 'Calle 123 #45-67, Medellín, Colombia'); ?></p>
          </div>
        </div>
        <div class="contact-item">
          <span class="contact-icon">📱</span>
          <div>
            <p><strong>Vehículos:</strong> <?php echo htmlspecialchars($config['telefonoWhatsappVehiculos'] ?? '+57 300 123 4567'); ?></p>
            <p><strong>Servicios:</strong> <?php echo htmlspecialchars($config['telefonoWhatsappServicios'] ?? '+57 300 765 4321'); ?></p>
          </div>
        </div>
        <div class="contact-item">
          <span class="contact-icon">📧</span>
          <div>
            <a href="mailto:<?php echo $config['correoNegocio'] ?? 'info@automarket.com'; ?>">
              <?php echo $config['correoNegocio'] ?? 'info@automarket.com'; ?>
            </a>
          </div>
        </div>
      </div>

      <!-- Columna 4: Horarios -->
      <div class="footer-section">
        <h3>Horarios</h3>
        <div class="contact-item">
          <span class="contact-icon">🕒</span>
          <div>
            <p><strong>Lunes - Viernes</strong></p>
            <p><?php echo $horarios['horarioSemana'] ?? '8:00 AM - 6:00 PM'; ?></p>
          </div>
        </div>
        <div class="contact-item">
          <span class="contact-icon">🕒</span>
          <div>
            <p><strong>Sábados</strong></p>
            <p><?php echo $horarios['horarioSabado'] ?? '9:00 AM - 2:00 PM'; ?></p>
          </div>
        </div>
        <div class="contact-item">
          <span class="contact-icon">🕒</span>
          <div>
            <p><strong>Domingos</strong></p>
            <p>Cerrado</p>
          </div>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> CodeGame Studio. Todos los derechos reservados.</p>
    </div>
  </div>
</footer>