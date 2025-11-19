<?php
$configData = file_exists('data/configuracion.json') 
    ? json_decode(file_get_contents('data/configuracion.json'), true) 
    : [];
$config = $configData['general'] ?? [];
$nosotros = $configData['nosotros'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nosotros - <?php echo $config['nombreNegocio'] ?? 'Autostok'; ?></title>
  <link rel="stylesheet" href="style.css">
  <link rel=icon href="favicon.ico" type="image/x-icon">
  <style>
    .nosotros-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    .nosotros-hero {
      text-align: center;
      padding: 80px 20px;
      background: linear-gradient(135deg, rgba(255,215,0,0.1), transparent);
      border-radius: 20px;
      margin-bottom: 60px;
    }

    .nosotros-hero h1 {
      font-size: 3.5rem;
      color: #FFD700;
      margin-bottom: 20px;
      text-transform: uppercase;
      animation: fadeInDown 0.8s ease;
    }

    .nosotros-hero p {
      font-size: 1.3rem;
      color: rgba(255,255,255,0.8);
      max-width: 800px;
      margin: 0 auto;
      line-height: 1.8;
    }

    .valores-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      margin-bottom: 60px;
    }

    .valor-card {
      background: linear-gradient(135deg, rgba(255,215,0,0.1), rgba(0,0,0,0.8));
      border: 2px solid rgba(255,215,0,0.3);
      border-radius: 20px;
      padding: 40px 30px;
      text-align: center;
      transition: all 0.4s ease;
    }

    .valor-card:hover {
      transform: translateY(-10px);
      border-color: #FFD700;
      box-shadow: 0 20px 40px rgba(255,215,0,0.3);
    }

    .valor-icon {
      font-size: 4rem;
      margin-bottom: 20px;
    }

    .valor-card h3 {
      color: #FFD700;
      font-size: 1.8rem;
      margin-bottom: 15px;
      text-transform: uppercase;
    }

    .valor-card p {
      color: rgba(255,255,255,0.8);
      line-height: 1.6;
      font-size: 1.05rem;
    }

    .historia-section {
      background: rgba(255,215,0,0.05);
      border: 2px solid rgba(255,215,0,0.2);
      border-radius: 20px;
      padding: 60px 40px;
      margin-bottom: 60px;
    }

    .historia-section h2 {
      color: #FFD700;
      font-size: 2.5rem;
      margin-bottom: 30px;
      text-align: center;
      text-transform: uppercase;
    }

    .historia-section p {
      color: rgba(255,255,255,0.85);
      font-size: 1.15rem;
      line-height: 1.8;
      margin-bottom: 20px;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 30px;
      margin: 60px 0;
    }

    .stat-item {
      text-align: center;
      padding: 30px;
      background: rgba(0,0,0,0.6);
      border-radius: 15px;
      border: 2px solid rgba(255,215,0,0.2);
    }

    .stat-number {
      font-size: 3.5rem;
      color: #FFD700;
      font-weight: bold;
      display: block;
      margin-bottom: 10px;
    }

    .stat-label {
      color: rgba(255,255,255,0.8);
      font-size: 1.1rem;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
  </style>
</head>
<body>

  <header class="header">
    <div class="logo">🚗 <?php echo $config['nombreNegocio'] ?? 'AutoMarket'; ?></div>
    <nav>
      <a href="index.php">Inicio</a>
      <a href="vehiculos/catalogo.php">Vehículos</a>
      <a href="servicios/servicios.php">Servicios</a>
      <a href="nosotros.php">Nosotros</a>
      <a href="contacto.php">Contacto</a>
    </nav>
  </header>

  <main class="nosotros-container">
    <div class="nosotros-hero">
      <h1>Sobre Nosotros</h1>
      <p><?php echo htmlspecialchars($nosotros['descripcionNosotros'] ?? 'Somos una empresa dedicada a ofrecer los mejores vehículos y servicios automotrices, con más de 10 años de experiencia en el mercado.'); ?></p>
    </div>

    <div class="stats-grid">
      <div class="stat-item">
        <span class="stat-number"><?php echo $nosotros['anosExperiencia'] ?? '10'; ?>+</span>
        <span class="stat-label">Años de Experiencia</span>
      </div>
      <div class="stat-item">
        <span class="stat-number"><?php echo $nosotros['clientesSatisfechos'] ?? '500'; ?>+</span>
        <span class="stat-label">Clientes Satisfechos</span>
      </div>
      <div class="stat-item">
        <span class="stat-number"><?php echo $nosotros['vehiculosVendidos'] ?? '1000'; ?>+</span>
        <span class="stat-label">Vehículos Vendidos</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">100%</span>
        <span class="stat-label">Garantía</span>
      </div>
    </div>

    <div class="historia-section">
      <h2>Nuestra Historia</h2>
      <p>
        <?php echo $config['nombreNegocio'] ?? 'AutoMarket'; ?> nació en 2014 con la visión de transformar la experiencia de compra de vehículos en Colombia. 
        Comenzamos como un pequeño concesionario familiar y hemos crecido hasta convertirnos en uno de los referentes más confiables del mercado automotriz.
      </p>
      <p>
        Nuestra pasión por los automóviles y el compromiso con la satisfacción del cliente nos ha llevado a expandir nuestros servicios, 
        incluyendo un taller especializado y una amplia gama de soluciones automotrices integrales.
      </p>
      <p>
        Hoy en día, contamos con un equipo de profesionales altamente capacitados, instalaciones modernas y una selección cuidadosa 
        de vehículos que cumplen con los más altos estándares de calidad.
      </p>
    </div>

    <h2 style="text-align: center; color: #FFD700; font-size: 2.5rem; margin-bottom: 40px; text-transform: uppercase;">Nuestros Valores</h2>

    <div class="valores-grid">
      <div class="valor-card">
        <div class="valor-icon">🎯</div>
        <h3>Compromiso</h3>
        <p>Nos comprometemos con la satisfacción total de nuestros clientes, ofreciendo productos y servicios de la más alta calidad.</p>
      </div>

      <div class="valor-card">
        <div class="valor-icon">✨</div>
        <h3>Transparencia</h3>
        <p>Operamos con honestidad y claridad en cada transacción, construyendo relaciones basadas en la confianza.</p>
      </div>

      <div class="valor-card">
        <div class="valor-icon">🚀</div>
        <h3>Innovación</h3>
        <p>Constantemente mejoramos nuestros procesos y servicios para ofrecer la mejor experiencia a nuestros clientes.</p>
      </div>

      <div class="valor-card">
        <div class="valor-icon">👥</div>
        <h3>Servicio</h3>
        <p>Nuestro equipo está dedicado a brindar atención personalizada y profesional en cada punto de contacto.</p>
      </div>

      <div class="valor-card">
        <div class="valor-icon">🏆</div>
        <h3>Excelencia</h3>
        <p>Buscamos la excelencia en todo lo que hacemos, desde la selección de vehículos hasta el servicio postventa.</p>
      </div>

      <div class="valor-card">
        <div class="valor-icon">🤝</div>
        <h3>Responsabilidad</h3>
        <p>Asumimos nuestra responsabilidad social y ambiental, contribuyendo positivamente a nuestra comunidad.</p>
      </div>
    </div>
  </main>

  <?php include 'footer.php'; ?>

</body>
</html>