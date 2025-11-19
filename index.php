<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Autostok - Concesionario Multimarca</title>
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

    .main-container {
      height: 100vh;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0;
    }

    .section-link {
      position: relative;
      overflow: hidden;
      cursor: pointer;
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .section-link:hover {
      transform: scale(1.02);
    }

    .section-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.8s ease;
      filter: brightness(0.6);
    }

    .section-link:hover .section-image {
      transform: scale(1.1);
      filter: brightness(0.8);
    }

    .section-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(0,0,0,0.7), rgba(255,215,0,0.3));
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      transition: all 0.5s ease;
    }

    .section-link:hover .section-overlay {
      background: linear-gradient(135deg, rgba(0,0,0,0.5), rgba(255,215,0,0.5));
    }

    .section-title {
      font-size: 4rem;
      font-weight: 900;
      color: #FFD700;
      text-transform: uppercase;
      letter-spacing: 5px;
      text-shadow: 0 0 20px rgba(255,215,0,0.5);
      margin-bottom: 20px;
      animation: pulse 2s ease-in-out infinite;
    }

    .section-description {
      font-size: 1.2rem;
      color: #fff;
      text-align: center;
      max-width: 80%;
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.5s ease;
    }

    .section-link:hover .section-description {
      opacity: 1;
      transform: translateY(0);
    }

    .header {
      position: fixed;
      top: 0;
      width: 100%;
      background: rgba(0,0,0,0.95);
      backdrop-filter: blur(10px);
      padding: 20px 50px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 1000;
      border-bottom: 2px solid #FFD700;
    }

    .logo {
      font-size: 2rem;
      font-weight: bold;
      color: #FFD700;
      text-shadow: 0 0 10px rgba(255,215,0,0.5);
    }

    .nav {
      display: flex;
      gap: 30px;
    }

    .nav a {
      color: #fff;
      text-decoration: none;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      position: relative;
    }

    .nav a::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 0;
      height: 2px;
      background: #FFD700;
      transition: width 0.3s ease;
    }

    .nav a:hover::after {
      width: 100%;
    }

    .pse-flotante {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: linear-gradient(135deg, #FFD700, #FFA500);
      color: #000;
      border: none;
      padding: 18px 28px;
      font-size: 1.1rem;
      font-weight: bold;
      border-radius: 50px;
      cursor: pointer;
      box-shadow: 0 8px 25px rgba(255,215,0,0.4);
      transition: all 0.3s ease;
      z-index: 999;
      animation: float 3s ease-in-out infinite;
    }

    .pse-flotante:hover {
      transform: translateY(-5px) scale(1.05);
      box-shadow: 0 12px 35px rgba(255,215,0,0.6);
    }

    @keyframes pulse {
      0%, 100% { text-shadow: 0 0 20px rgba(255,215,0,0.5); }
      50% { text-shadow: 0 0 40px rgba(255,215,0,0.8); }
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    @media (max-width: 768px) {
      .main-container {
        grid-template-columns: 1fr;
        grid-template-rows: 1fr 1fr;
      }

      .section-title {
        font-size: 2.5rem;
      }

      .header {
        padding: 15px 20px;
      }

      .nav {
        gap: 15px;
      }

      .nav a {
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body>

  <!-- <header class="header">
    <div class="logo">🚗 Autostok</div>
    <nav class="nav">
      <a href="index.php">Inicio</a>
      <a href="vehiculos/catalogo.php">Vehículos</a>
      <a href="servicios/servicios.php">Servicios</a>
      <a href="nosotros.php">Nosotros</a>
      <a href="contacto.php">Contacto</a>
    </nav>
  </header> -->

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
  </div>

  <!-- Modal de Pagos PSE -->
  <div id="modalPago" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); backdrop-filter: blur(10px); z-index: 9999; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: linear-gradient(135deg, rgba(20,20,20,0.98), rgba(0,0,0,0.98)); border: 2px solid rgba(255,215,0,0.3); border-radius: 25px; max-width: 900px; width: 95%; box-shadow: 0 25px 60px rgba(255,215,0,0.3);">
      <button onclick="cerrarModalPago()" style="position: absolute; top: 20px; right: 20px; width: 45px; height: 45px; background: rgba(255,215,0,0.9); color: #000; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; font-weight: bold;">✕</button>
      <div style="padding: 30px; text-align: center; background: linear-gradient(135deg, rgba(255,215,0,0.1), transparent); border-bottom: 2px solid rgba(255,215,0,0.3); border-radius: 25px 25px 0 0;">
        <h2 style="color: #FFD700; font-size: 2rem; margin-bottom: 10px;">💳 Pasarela de Pagos PSE</h2>
        <p style="color: rgba(255,255,255,0.7); font-size: 1.1rem;">Realiza tu pago de forma segura</p>
      </div>
      <div style="padding: 20px; min-height: 400px; display: flex; align-items: center; justify-content: center;">
        <iframe id="iframePSE" src="" style="display:none; width: 100%; height: 600px; border: none; border-radius: 10px;"></iframe>
        <div id="loadingPago" style="display: flex; flex-direction: column; align-items: center; gap: 20px; color: #FFD700;">
          <div style="width: 60px; height: 60px; border: 6px solid rgba(255,215,0,0.2); border-top: 6px solid #FFD700; border-radius: 50%; animation: spin 1s linear infinite;"></div>
          <p>Cargando pasarela de pagos...</p>
        </div>
      </div>
    </div>
  </div>

  <style>
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    .modal {
      display: none;
    }
    .modal.active {
      display: flex !important;
    }
  </style>

  <script>
    function abrirModalPago() {
      document.getElementById('modalPago').classList.add('active');
      document.getElementById('loadingPago').style.display = 'flex';
      const iframe = document.getElementById('iframePSE');
      iframe.src = 'https://www.psepagos.co/PSEHostingUI/ShowTicketOffice.aspx?ID=2979';
      iframe.onload = function() {
        document.getElementById('loadingPago').style.display = 'none';
        iframe.style.display = 'block';
      };
    }

    function cerrarModalPago() {
      document.getElementById('modalPago').classList.remove('active');
      document.getElementById('iframePSE').src = '';
    }
  </script>

</body>
</html>