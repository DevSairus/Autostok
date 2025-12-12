<!-- ============================================
     ACTUALIZACIONES NECESARIAS EN TODOS LOS ARCHIVOS
     ============================================ -->

<!-- 1. INDEX.PHP - Agregar sección Autostok Team
     ============================================ -->
<!-- Reemplazar esta sección en index.php: -->

<div class="main-container" style="margin-top: 10px;">
  <a href="vehiculos/catalogo.php" class="section-link">
    <img src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1200" alt="Catálogo" class="section-image">
    <div class="section-overlay">
      <h1 class="section-title">Vehículos</h1>
      <p class="section-description">Descubre nuestra exclusiva selección de vehículos premium. Calidad, estilo y potencia en cada modelo.</p>
    </div>
  </a>

  <a href="servicios/servicios.php" class="section-link">
    <img src="https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=1200" alt="Servicios" class="section-image">
    <div class="section-overlay">
      <h1 class="section-title">Servicios</h1>
      <p class="section-description">Taller especializado, mantenimiento, accesorios y todo lo que tu vehículo necesita.</p>
    </div>
  </a>

  <!-- NUEVA SECCIÓN AUTOSTOK TEAM -->
  <a href="autostok-team.php" class="section-link">
    <img src="https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=1200" alt="Autostok Team" class="section-image">
    <div class="section-overlay">
      <h1 class="section-title">Team</h1>
      <p class="section-description">Autostok Team: 30 años de trayectoria en competiciones nacionales e internacionales.</p>
    </div>
  </a>
</div>

<!-- ACTUALIZAR CSS DEL MAIN-CONTAINER EN INDEX.PHP -->
<style>
  .main-container {
    height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;  /* Cambiar de 1fr 1fr a 1fr 1fr 1fr */
    gap: 0;
  }

  @media (max-width: 1024px) {
    .main-container {
      grid-template-columns: 1fr;
      grid-template-rows: 1fr 1fr 1fr;  /* Cambiar para 3 filas */
    }
  }

  @media (max-width: 768px) {
    .main-container {
      height: auto;
      grid-template-columns: 1fr;
      grid-template-rows: auto;
    }

    .section-link {
      min-height: 300px;  /* Agregar altura mínima en móvil */
    }
  }
</style>


<!-- ============================================
     2. NOSOTROS.PHP - Agregar enlace Team en menú
     ============================================ -->
<!-- Reemplazar la sección NAV en nosotros.php: -->

<nav id="navMenu">
  <a href="index.php">Inicio</a>
  <a href="vehiculos/catalogo.php">Vehículos</a>
  <a href="servicios/servicios.php">Servicios</a>
  <a href="nosotros.php" class="active">Empresa</a>
  <a href="autostok-team.php">Team</a>  <!-- NUEVA LÍNEA -->
  <a href="contacto.php">Contacto</a>
</nav>


<!-- ============================================
     3. CONTACTO.PHP - Agregar enlace Team en menú
     ============================================ -->
<!-- Reemplazar el HEADER en contacto.php: -->

<header class="header">
  <div class="logo">🚗 <?php echo $config['nombreNegocio'] ?? 'AutoMarket'; ?></div>
  <nav>
    <a href="index.php">Inicio</a>
    <a href="vehiculos/catalogo.php">Vehículos</a>
    <a href="servicios/servicios.php">Servicios</a>
    <a href="nosotros.php">Empresa</a>
    <a href="autostok-team.php">Team</a>  <!-- NUEVA LÍNEA -->
    <a href="contacto.php">Contacto</a>
  </nav>
</header>


<!-- ============================================
     4. CATALOGO.PHP (vehiculos/catalogo.php)
        Agregar enlace Team en menú
     ============================================ -->
<!-- Reemplazar el HEADER en catalogo.php: -->

<header class="header">
  <div class="logo">🚗 Autostok</div>
  <nav>
    <a href="../index.php">Inicio</a>
    <a href="catalogo.php">Vehículos</a>
    <a href="../servicios/servicios.php">Servicios</a>
    <a href="../nosotros.php">Empresa</a>
    <a href="../autostok-team.php">Team</a>  <!-- NUEVA LÍNEA -->
    <a href="../contacto.php">Contacto</a>
  </nav>
</header>


<!-- ============================================
     5. SERVICIOS.PHP (servicios/servicios.php)
        Agregar enlace Team en menú
     ============================================ -->
<!-- Reemplazar el HEADER en servicios.php: -->

<header class="header">
  <div class="logo">🚗 Autostok</div>
  <nav>
    <a href="../index.php">Inicio</a>
    <a href="../vehiculos/catalogo.php">Vehículos</a>
    <a href="servicios.php">Servicios</a>
    <a href="../nosotros.php">Empresa</a>
    <a href="../autostok-team.php">Team</a>  <!-- NUEVA LÍNEA -->
    <a href="../contacto.php">Contacto</a>
  </nav>
</header>


<!-- ============================================
     RESUMEN DE CAMBIOS
     ============================================ -->

CAMBIOS A REALIZAR:

1. INDEX.PHP:
   ✓ Cambiar grid-template-columns: 1fr 1fr → 1fr 1fr 1fr
   ✓ Cambiar grid-template-rows en responsive: 1fr 1fr → 1fr 1fr 1fr
   ✓ Agregar nueva sección <a> con href="autostok-team.php"
   ✓ Actualizar altura de .main-container en responsive

2. NOSOTROS.PHP:
   ✓ Agregar <a href="autostok-team.php">Team</a> en el <nav>
   ✓ Cambiar etiqueta "Nosotros" por "Empresa"

3. CONTACTO.PHP:
   ✓ Agregar <a href="autostok-team.php">Team</a> en el <nav>

4. CATALOGO.PHP (vehiculos/):
   ✓ Agregar <a href="../autostok-team.php">Team</a> en el <nav>
   ✓ Agregar href="../nosotros.php" con texto "Empresa"

5. SERVICIOS.PHP (servicios/):
   ✓ Agregar <a href="../autostok-team.php">Team</a> en el <nav>
   ✓ Agregar href="../nosotros.php" con texto "Empresa"

6. FOOTER.PHP (si existe):
   ✓ Agregar enlace a autostok-team.php en el menú del footer

ESTRUCTURA DE CARPETAS FINAL:
├── index.php (ACTUALIZAR)
├── nosotros.php (ACTUALIZAR)
├── contacto.php (ACTUALIZAR)
├── autostok-team.php (NUEVO)
├── footer.php
├── vehiculos/
│   └── catalogo.php (ACTUALIZAR)
├── servicios/
│   └── servicios.php (ACTUALIZAR)
└── data/
    └── configuracion.json