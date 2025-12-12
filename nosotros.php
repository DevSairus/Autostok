<?php
// Cargar configuración
$configData = file_exists(__DIR__ . '/data/configuracion.json') 
    ? json_decode(file_get_contents(__DIR__ . '/data/configuracion.json'), true) 
    : [];

$nosotros = $configData['nosotros'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nosotros - AUTO STOK</title>
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #000;
      color: #fff;
      overflow-x: hidden;
    }

    /* Header */
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

    .menu-toggle {
      display: none;
      flex-direction: column;
      cursor: pointer;
      gap: 5px;
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

    /* Navegación de secciones */
    .section-nav {
      position: fixed;
      top: 70px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 999;
      display: flex;
      gap: 8px;
      background: rgba(0,0,0,0.8);
      padding: 12px 15px;
      border-radius: 30px;
      border: 2px solid rgba(255,215,0,0.3);
      backdrop-filter: blur(10px);
      flex-wrap: wrap;
      justify-content: center;
      max-width: 90%;
      max-height: 100px;
      overflow-y: auto;
    }

    .section-btn {
      padding: 8px 16px;
      background: rgba(255,215,0,0.1);
      color: #fff;
      border: 1px solid rgba(255,215,0,0.3);
      border-radius: 20px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.85rem;
      white-space: nowrap;
      flex-shrink: 0;
    }

    .section-btn:hover {
      background: rgba(255,215,0,0.2);
      transform: translateY(-2px);
    }

    .section-btn.active {
      background: linear-gradient(135deg, #FFD700, #FFA500);
      color: #000;
      font-weight: bold;
      border-color: #FFD700;
    }

    /* Contenedor principal */
    .nosotros-container {
      position: relative;
      min-height: 100vh;
      padding-top: 160px;
    }

    /* Secciones */
    .section {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      min-height: 100vh;
      padding: 160px 20px 50px;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.8s ease, visibility 0.8s ease;
    }

    .section.active {
      opacity: 1;
      visibility: visible;
      position: relative;
      top: auto;
    }

    /* Sección Bienvenida */
    .welcome-section {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      background: linear-gradient(135deg, rgba(255,215,0,0.1), transparent);
      padding: 40px 20px;
    }

    .welcome-section h1 {
      font-size: clamp(2rem, 6vw, 4rem);
      color: #FFD700;
      margin-bottom: 20px;
      text-transform: uppercase;
      letter-spacing: 2px;
    }

    .welcome-section p {
      font-size: clamp(1rem, 3vw, 1.5rem);
      color: rgba(255,255,255,0.8);
      max-width: 800px;
      margin-bottom: 50px;
      line-height: 1.8;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      max-width: 900px;
      margin-top: 50px;
      width: 100%;
    }

    .stat-card {
      background: rgba(255,215,0,0.05);
      padding: 20px;
      border-radius: 15px;
      border: 2px solid rgba(255,215,0,0.3);
      text-align: center;
      transition: all 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-10px);
      border-color: #FFD700;
      box-shadow: 0 10px 30px rgba(255,215,0,0.3);
    }

    .stat-number {
      font-size: clamp(2rem, 4vw, 3rem);
      color: #FFD700;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .stat-label {
      font-size: 0.95rem;
      color: rgba(255,255,255,0.7);
    }

    /* Sección Timeline */
    .timeline-section {
      max-width: 1000px;
      margin: 0 auto;
      position: relative;
      padding: 40px 20px;
    }

    .timeline-section h2 {
      font-size: clamp(1.8rem, 5vw, 3rem);
      color: #FFD700;
      margin-bottom: 20px;
      text-transform: uppercase;
    }

    .timeline-years {
      font-size: 1.2rem;
      color: rgba(255,215,0,0.7);
      margin-bottom: 40px;
    }

    /* Timeline vertical */
    .timeline {
      position: relative;
      padding: 20px 0 20px 30px;
    }

    .timeline::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 3px;
      background: linear-gradient(180deg, #FFD700, rgba(255,215,0,0.2));
    }

    .timeline-item {
      position: relative;
      padding-left: 30px;
      margin-bottom: 40px;
    }

    .timeline-dot {
      position: absolute;
      left: -12px;
      top: 0;
      width: 24px;
      height: 24px;
      background: #FFD700;
      border-radius: 50%;
      border: 3px solid #000;
      box-shadow: 0 0 20px rgba(255,215,0,0.5);
      z-index: 2;
    }

    .timeline-content {
      background: rgba(255,215,0,0.05);
      padding: 20px;
      border-radius: 12px;
      border-left: 3px solid #FFD700;
    }

    .timeline-content h3 {
      color: #FFD700;
      font-size: clamp(1.3rem, 3vw, 1.8rem);
      margin-bottom: 12px;
    }

    .timeline-content p {
      color: rgba(255,255,255,0.85);
      line-height: 1.8;
      font-size: 0.95rem;
      margin-bottom: 15px;
      text-align: justify;
    }

    .timeline-content ul {
      list-style: none;
      padding-left: 0;
      margin-top: 15px;
    }

    .timeline-content li {
      color: rgba(255,255,255,0.85);
      padding: 8px 0;
      padding-left: 25px;
      position: relative;
      line-height: 1.6;
      font-size: 0.95rem;
    }

    .timeline-content li::before {
      content: '▸';
      position: absolute;
      left: 0;
      color: #FFD700;
      font-size: 1rem;
    }

    /* Sección Actualidad */
    .today-section {
      max-width: 1000px;
      margin: 0 auto;
      text-align: center;
      padding: 40px 20px;
    }

    .today-section h2 {
      font-size: clamp(1.8rem, 5vw, 3.5rem);
      color: #FFD700;
      margin-bottom: 30px;
      text-transform: uppercase;
    }

    .today-content {
      background: rgba(255,215,0,0.05);
      padding: 30px;
      border-radius: 15px;
      border: 2px solid rgba(255,215,0,0.3);
      margin-bottom: 40px;
      text-align: left;
    }

    .today-content p {
      font-size: 1rem;
      line-height: 1.8;
      color: rgba(255,255,255,0.85);
      margin-bottom: 20px;
    }

    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }

    .service-card {
      background: rgba(0,0,0,0.5);
      padding: 20px;
      border-radius: 12px;
      border: 2px solid rgba(255,215,0,0.3);
      transition: all 0.3s ease;
      text-align: center;
    }

    .service-card:hover {
      transform: translateY(-8px);
      border-color: #FFD700;
      box-shadow: 0 10px 25px rgba(255,215,0,0.3);
    }

    .service-card h4 {
      color: #FFD700;
      font-size: 1.1rem;
      margin-bottom: 8px;
    }

    /* Sección Misión y Visión */
    .mission-vision-section {
      max-width: 1000px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    .mission-vision-section h2 {
      font-size: clamp(1.8rem, 5vw, 3.5rem);
      color: #FFD700;
      margin-bottom: 40px;
      text-align: center;
      text-transform: uppercase;
    }

    .mv-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
    }

    .mv-card {
      background: rgba(255,215,0,0.05);
      padding: 30px;
      border-radius: 15px;
      border: 2px solid rgba(255,215,0,0.3);
      transition: all 0.3s ease;
    }

    .mv-card:hover {
      transform: scale(1.02);
      border-color: #FFD700;
      box-shadow: 0 20px 40px rgba(255,215,0,0.3);
    }

    .mv-card h3 {
      font-size: clamp(1.8rem, 4vw, 2.5rem);
      color: #FFD700;
      margin-bottom: 20px;
      text-align: center;
    }

    .mv-card p {
      font-size: 0.95rem;
      line-height: 1.8;
      color: rgba(255,255,255,0.85);
      text-align: justify;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .header {
        padding: 12px 15px;
      }

      .logo {
        font-size: 1.2rem;
      }

      .header nav {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        flex-direction: column;
        background: rgba(0,0,0,0.95);
        padding: 20px;
        gap: 10px;
        border-bottom: 2px solid #FFD700;
      }

      .header nav.active {
        display: flex;
      }

      .menu-toggle {
        display: flex;
      }

      .header nav a {
        font-size: 1rem;
        padding: 10px 0;
      }

      .section-nav {
        top: 65px;
        max-width: 95%;
        max-height: 80px;
        padding: 10px 12px;
        gap: 6px;
      }

      .section-btn {
        font-size: 0.75rem;
        padding: 6px 12px;
      }

      .nosotros-container {
        padding-top: 150px;
      }

      .section {
        padding: 150px 15px 40px;
      }

      .welcome-section {
        padding: 30px 15px;
      }

      .timeline {
        padding: 15px 0 15px 20px;
      }

      .timeline-item {
        margin-bottom: 30px;
        padding-left: 20px;
      }

      .timeline-dot {
        left: -10px;
      }

      .timeline::before {
        left: -3px;
      }

      .timeline-content {
        padding: 15px;
      }

      .today-content {
        padding: 20px;
      }

      .today-content p {
        font-size: 0.95rem;
        text-align: left;
      }

      .services-grid {
        grid-template-columns: 1fr;
        gap: 15px;
      }

      .mv-grid {
        grid-template-columns: 1fr;
      }

      .mv-card {
        padding: 20px;
      }

      .stats-grid {
        grid-template-columns: 1fr;
        gap: 15px;
      }
    }

    @media (max-width: 480px) {
      .logo {
        font-size: 1rem;
      }

      .header {
        padding: 10px 12px;
      }

      .section-nav {
        top: 60px;
        padding: 8px 10px;
      }

      .section-btn {
        font-size: 0.7rem;
        padding: 5px 10px;
      }

      .nosotros-container {
        padding-top: 130px;
      }

      .section {
        padding: 130px 12px 30px;
      }

      .welcome-section h1 {
        font-size: 1.8rem;
      }

      .welcome-section p {
        font-size: 0.95rem;
      }

      .stat-number {
        font-size: 1.8rem;
      }

      .stat-label {
        font-size: 0.8rem;
      }

      .timeline-content h3 {
        font-size: 1.1rem;
      }

      .timeline-content p {
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body>

  <header class="header">
    <div class="logo">🚗 AUTO STOK</div>
    
    <div class="menu-toggle" id="menuToggle">
      <span></span>
      <span></span>
      <span></span>
    </div>
    
    <nav id="navMenu">
      <a href="index.php">Inicio</a>
      <a href="vehiculos/catalogo.php">Vehículos</a>
      <a href="servicios/servicios.php">Servicios</a>
      <a href="nosotros.php">Nosotros</a>
      <a href="autostok-team.php">Team</a>
      <a href="contacto.php">Contacto</a>
    </nav>
  </header>

  <!-- Navegación de secciones -->
  <div class="section-nav">
    <button class="section-btn active" onclick="cambiarSeccion(0)">Inicio</button>
    <button class="section-btn" onclick="cambiarSeccion(1)">1982-1984</button>
    <button class="section-btn" onclick="cambiarSeccion(2)">1984-1998</button>
    <button class="section-btn" onclick="cambiarSeccion(3)">2000-2007</button>
    <button class="section-btn" onclick="cambiarSeccion(4)">2008-2011</button>
    <button class="section-btn" onclick="cambiarSeccion(5)">Hoy</button>
    <button class="section-btn" onclick="cambiarSeccion(6)">Misión & Visión</button>
  </div>

  <div class="nosotros-container">
    
    <!-- Sección 0: Bienvenida -->
    <section class="section welcome-section active" data-section="0">
      <h1>AUTO STOK</h1>
      <p>Más de 40 años construyendo sueños sobre ruedas. Una historia de pasión, compromiso y excelencia en el sector automotriz colombiano.</p>
      
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number">40+</div>
          <div class="stat-label">Años de Experiencia</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">1982</div>
          <div class="stat-label">Año de Fundación</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">Nacional</div>
          <div class="stat-label">Presencia</div>
        </div>
      </div>
    </section>

    <!-- Sección 1: 1982-1984 -->
    <section class="section" data-section="1">
      <div class="timeline-section">
        <h2>El Inicio de un Sueño</h2>
        <p class="timeline-years">1982 - 1984</p>
        
        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <h3>1982: Nacimiento de AUTO STOK</h3>
              <p>En AUTO STOK creemos en los sueños que se construyen con dedicación, conocimiento y visión. Nuestra trayectoria comenzó en 1982, impulsada por el espíritu emprendedor de nuestro Gerente General, Alirio Alarcón Cepeda, quien convirtió años de experiencia en los Servicios Autorizados Renault en la base de un proyecto que hoy es referente nacional.</p>
            </div>
          </div>

          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <h3>Taller Especializado</h3>
              <p>Lo que comenzó como un taller especializado en mecánica Renault, ubicado inicialmente en la Av. Boyacá con 66, pronto se consolidó por su calidad y compromiso con el cliente.</p>
            </div>
          </div>

          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <h3>1984: Servicio Autorizado Renault</h3>
              <p>Gracias a nuestro compromiso con la excelencia, SOFASA S.A. nos otorgó la distinción de Servicio Autorizado Renault en 1984, marcando el inicio de una alianza que ha fortalecido nuestro crecimiento durante décadas.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 2: 1984-1998 -->
    <section class="section" data-section="2">
      <div class="timeline-section">
        <h2>Crecimiento y Ampliación</h2>
        <p class="timeline-years">1984 - 1998</p>
        
        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <h3>Expansión de Servicios</h3>
              <p>La excelencia en el servicio abrió paso a nuevas oportunidades. Incursionamos en la compra y venta de vehículos, ganándonos rápidamente un reconocimiento por la calidad de nuestro servicio.</p>
            </div>
          </div>

          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <h3>1998: Primera Sala de Ventas</h3>
              <p>Inauguramos nuestra primera sala de ventas Renault en la Calle 100, consolidando nuestra presencia en el mercado bogotano.</p>
            </div>
          </div>

          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <h3>1998: Concesionario Renault</h3>
              <p>Fuimos nombrados Concesionario Renault, un hito que reafirmó nuestro compromiso con la marca y con nuestros clientes.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 3: 2000-2007 -->
    <section class="section" data-section="3">
      <div class="timeline-section">
        <h2>Liderazgo y Reconocimiento</h2>
        <p class="timeline-years">2000 - 2007</p>
        
        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <h3>Consolidación como Líder</h3>
              <p>A lo largo de los años 2000, AUTO STOK se consolidó como uno de los concesionarios líderes de la red Renault.</p>
            </div>
          </div>

          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <h3>Reconocimientos Destacados</h3>
              <ul>
                <li>2004: Primer lugar suramericano en Carrocería Posventa alta tecnología</li>
                <li>2005: Apertura de la segunda sala de exhibición Sede Morato</li>
                <li>2007: Único concesionario autorizado para la distribución de taxis Renault en Bogotá</li>
              </ul>
            </div>
          </div>

          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <h3>Finales de 2007: Posiciones Destacadas</h3>
              <ul>
                <li>4.º lugar en ventas de vehículos</li>
                <li>3.º lugar en Bogotá</li>
                <li>2.º lugar en ventas de repuestos</li>
                <li>1.º lugar en taller, posventa y calidad de servicio</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 4: 2008-2011 -->
    <section class="section" data-section="4">
      <div class="timeline-section">
        <h2>Modernización y Expansión</h2>
        <p class="timeline-years">2008 - 2011</p>
        
        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <h3>2008: Innovación Tecnológica</h3>
              <p>Apertura del primer punto de servicio de carrocería de alta tecnología en Colombia, acompañado de Renault Minuto Mecánica y Renault Minuto Carrocería en los sectores de 7 de Agosto y Morato.</p>
            </div>
          </div>

          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <h3>Expansión Nacional</h3>
              <ul>
                <li>2009: Apertura de la sede en Madelena, primer concesionario Renault en el sur de Bogotá</li>
                <li>2009: Llegada a Cúcuta con una sede integral de servicios</li>
                <li>2010: Reconocimiento a la Excelencia en la Gestión 2009 y apertura de nuevo punto de carrocería en Cúcuta</li>
                <li>2011: Inauguración de la sede Restrepo en Bogotá, fortaleciendo presencia en el sur</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección 5: Hoy -->
    <section class="section" data-section="5">
      <div class="today-section">
        <h2>AUTO STOK Hoy</h2>
        
        <div class="today-content">
          <p>Hoy, después de más de cuatro décadas de experiencia, AUTO STOK es un taller multimarca y reconocido punto de venta de vehículos usados, que ofrece soluciones integrales para vehículos de diversas marcas, manteniendo los más altos estándares de calidad, tecnología y servicio.</p>
          
          <p>Nos hemos transformado para responder a las necesidades actuales del mercado automotor, brindando servicios especializados con la misma pasión y profesionalismo de siempre.</p>
        </div>

        <div class="services-grid">
          <div class="service-card">
            <h4>🔧 Mecánica General</h4>
            <p>Para múltiples marcas</p>
          </div>
          <div class="service-card">
            <h4>🎨 Carrocería y Pintura</h4>
            <p>Tecnología avanzada</p>
          </div>
          <div class="service-card">
            <h4>💻 Diagnóstico</h4>
            <p>Computarizado</p>
          </div>
          <div class="service-card">
            <h4>🛠️ Repuestos</h4>
            <p>Y accesorios</p>
          </div>
          <div class="service-card">
            <h4>👨‍🔧 Asesoría Técnica</h4>
            <p>Servicio personalizado</p>
          </div>
        </div>

        <div class="today-content" style="margin-top: 30px;">
          <p style="text-align: center; font-size: 1.1rem; color: #FFD700; font-weight: 600;">
            Nuestro propósito sigue siendo el mismo que en 1982: trabajar con pasión, profesionalismo y transparencia para ofrecer a nuestros clientes una experiencia confiable, eficiente y humana.
          </p>
        </div>
      </div>
    </section>

    <!-- Sección 6: Misión y Visión -->
    <section class="section" data-section="6">
      <div class="mission-vision-section">
        <h2>Nuestra Razón de Ser</h2>
        
        <div class="mv-grid">
          <div class="mv-card">
            <h3>🎯 MISIÓN</h3>
            <p>Somos un taller multimarca y un reconocido punto de venta de vehículos usados a nivel nacional. Ofrecemos servicios automotrices de excelencia y soluciones integrales en venta de vehículos usados, brindando servicios de mantenimiento y reparación de vehículos de diversas marcas con excelencia, profesionalismo, honestidad y compromiso.</p>
            <p>Nos dedicamos a garantizar la satisfacción y confianza de nuestros clientes, el bienestar y desarrollo de nuestro equipo, y el crecimiento sostenible de la empresa.</p>
          </div>

          <div class="mv-card">
            <h3>🚀 VISIÓN</h3>
            <p>Ser un taller multimarca y un reconocido punto de venta de vehículos usados en el sector automotriz, con presencia a nivel nacional y proyección internacional. Representar diversas marcas y trabajar siempre con honestidad, respeto y compromiso.</p>
            <p>Nuestro objetivo es promover el bienestar y la estabilidad de nuestros colaboradores, así como la fidelización de nuestros clientes, destacándonos por liderazgo en calidad, servicio al cliente y solidez financiera.</p>
          </div>
        </div>
      </div>
    </section>

  </div>

  <?php include 'footer.php'; ?>

<script>
  // Menú mobile
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

  // Navegación de secciones
  let seccionActual = 0;
  let isTransitioning = false;

  const sections = Array.from(document.querySelectorAll('.section'));
  const buttons = Array.from(document.querySelectorAll('.section-btn'));
  const totalSecciones = sections.length;

  const inicial = sections.findIndex(s => s.classList.contains('active'));
  if (inicial >= 0) seccionActual = inicial;

  function cambiarSeccion(index) {
    if (index < 0 || index >= totalSecciones) return;
    if (isTransitioning) return;

    const seccionAnterior = document.querySelector('.section.active');
    const nuevaSeccion = document.querySelector(`.section[data-section="${index}"]`) || sections[index];

    if (!nuevaSeccion || seccionAnterior === nuevaSeccion) {
      actualizarBotones(index);
      seccionActual = index;
      return;
    }

    isTransitioning = true;

    if (seccionAnterior) {
      seccionAnterior.classList.remove('active');
    }

    const TRANSITION_MS = 350;

    setTimeout(() => {
      nuevaSeccion.classList.add('active');
      seccionActual = index;
      actualizarBotones(index);

      setTimeout(() => {
        isTransitioning = false;
      }, 60);
    }, TRANSITION_MS);
  }

  function actualizarBotones(activeIndex) {
    buttons.forEach((btn, i) => {
      btn.classList.toggle('active', i === activeIndex);
      btn.setAttribute('aria-current', i === activeIndex ? 'true' : 'false');
    });
  }

  buttons.forEach((btn, i) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      cambiarSeccion(i);
    });
    btn.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        cambiarSeccion(i);
      }
    });
  });

  window.cambiarSeccion = cambiarSeccion;
</script>

</body>
</html>