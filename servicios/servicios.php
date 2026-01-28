<?php
// Cargar servicios desde JSON
$serviciosData = file_exists('../data/servicios.json') 
  ? json_decode(file_get_contents('../data/servicios.json'), true) 
  : ['servicios' => []];
$servicios = $serviciosData['servicios'] ?? [];

// Cargar productos desde JSON
$productosData = file_exists('../data/productos.json') 
  ? json_decode(file_get_contents('../data/productos.json'), true) 
  : ['productos' => []];
$productos = $productosData['productos'] ?? [];

// Agrupar servicios por categorías
$categorias = [];
foreach ($servicios as $servicio) {
  $cat = $servicio['categoria'] ?? 'Otros';
  if (!isset($categorias[$cat])) {
    $categorias[$cat] = [];
  }
  $categorias[$cat][] = $servicio;
}

// Obtener categorías de productos
$categoriasProductos = array_unique(array_column($productos, 'categoria'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Servicios y Repuestos - Auto Stok</title>
  <link rel="stylesheet" href="css/servicios.css">
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

    .vista-selector {
      display: flex;
      gap: 20px;
      justify-content: center;
      margin: 20px 0 40px;
      padding: 0 20px;
    }
    
    .vista-btn {
      padding: 15px 40px;
      background: rgba(255,215,0,0.1);
      color: #fff;
      border: 2px solid rgba(255,215,0,0.3);
      border-radius: 30px;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .vista-btn:hover {
      background: rgba(255,215,0,0.2);
      border-color: #FFD700;
      transform: translateY(-2px);
    }
    
    .vista-btn.active {
      background: linear-gradient(135deg, #FFD700, #FFA500);
      color: #000;
      border-color: #FFD700;
      box-shadow: 0 5px 15px rgba(255,215,0,0.4);
    }
    
    .sucursal-selector {
      max-width: 600px;
      margin: 0 auto 40px;
      padding: 20px;
      background: rgba(255,215,0,0.05);
      border-radius: 15px;
      border: 1px solid rgba(255,215,0,0.2);
      text-align: center;
    }
    
    .sucursal-selector label {
      display: block;
      color: #FFD700;
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 10px;
    }
    
    .sucursal-selector select {
      width: 100%;
      max-width: 400px;
      padding: 12px;
      background: rgba(0,0,0,0.6);
      color: #fff;
      border: 2px solid rgba(255,215,0,0.3);
      border-radius: 10px;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .sucursal-selector select:focus {
      outline: none;
      border-color: #FFD700;
      box-shadow: 0 0 15px rgba(255,215,0,0.2);
    }
    
    /* ESTILOS PARA FILTROS DE PRODUCTOS */
    .filtros-container {
      max-width: 1200px;
      margin: 0 auto 40px;
      padding: 20px;
    }
    
    .filtros {
      display: grid;
      grid-template-columns: 2fr 1fr auto;
      gap: 15px;
      background: rgba(255,215,0,0.05);
      padding: 25px;
      border-radius: 15px;
      border: 1px solid rgba(255,215,0,0.2);
      align-items: center;
    }
    
    .filtros input {
      width: 100%;
      padding: 14px 20px;
      background: rgba(0,0,0,0.6);
      color: #fff;
      border: 2px solid rgba(255,215,0,0.3);
      border-radius: 10px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    
    .filtros input::placeholder {
      color: rgba(255,255,255,0.5);
    }
    
    .filtros input:focus {
      outline: none;
      border-color: #FFD700;
      box-shadow: 0 0 15px rgba(255,215,0,0.2);
      background: rgba(0,0,0,0.8);
    }
    
    .filtros select {
      width: 100%;
      padding: 14px 20px;
      background: rgba(0,0,0,0.6);
      color: #fff;
      border: 2px solid rgba(255,215,0,0.3);
      border-radius: 10px;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .filtros select:focus {
      outline: none;
      border-color: #FFD700;
      box-shadow: 0 0 15px rgba(255,215,0,0.2);
      background: rgba(0,0,0,0.8);
    }
    
    .btn-limpiar {
      padding: 14px 30px;
      background: linear-gradient(135deg, #FFD700, #FFA500);
      color: #000;
      border: none;
      border-radius: 10px;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      white-space: nowrap;
    }
    
    .btn-limpiar:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(255,215,0,0.4);
    }
    
    .productos-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 30px;
      margin-bottom: 60px;
    }
    
    .stock {
      font-size: 0.9rem;
      padding: 5px 12px;
      border-radius: 15px;
      font-weight: 600;
    }
    
    .stock-disponible {
      background: rgba(0,255,0,0.1);
      color: #0f0;
      border: 1px solid rgba(0,255,0,0.3);
    }
    
    .stock-bajo {
      background: rgba(255,165,0,0.1);
      color: #FFA500;
      border: 1px solid rgba(255,165,0,0.3);
    }
    
    .stock-agotado {
      background: rgba(255,0,0,0.1);
      color: #f00;
      border: 1px solid rgba(255,0,0,0.3);
    }
    
    .no-resultados {
      text-align: center;
      padding: 60px 20px;
      color: rgba(255,255,255,0.6);
      font-size: 1.2rem;
    }
    
    /* ESTILOS MEJORADOS PARA MODAL DE PRODUCTOS */
    .contacto-section {
      background: rgba(255,215,0,0.05);
      padding: 30px;
      border-radius: 15px;
      border: 1px solid rgba(255,215,0,0.2);
    }
    
    .contacto-section h3 {
      color: #FFD700;
      font-size: 1.6rem;
      margin-bottom: 25px;
      text-transform: uppercase;
    }
    
    .contacto-section form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    
    .contacto-section input,
    .contacto-section select,
    .contacto-section textarea {
      padding: 14px 18px;
      border: 2px solid rgba(255,215,0,0.3);
      background: rgba(0,0,0,0.6);
      color: #fff;
      border-radius: 10px;
      font-size: 1rem;
      transition: all 0.3s ease;
      font-family: inherit;
      width: 100%;
    }
    
    .contacto-section input:focus,
    .contacto-section select:focus,
    .contacto-section textarea:focus {
      outline: none;
      border-color: #FFD700;
      box-shadow: 0 0 15px rgba(255,215,0,0.2);
      background: rgba(0,0,0,0.8);
    }
    
    .contacto-section input::placeholder,
    .contacto-section textarea::placeholder {
      color: rgba(255,255,255,0.5);
    }
    
    .contacto-section input[type="number"] {
      -moz-appearance: textfield;
    }
    
    .contacto-section input[type="number"]::-webkit-outer-spin-button,
    .contacto-section input[type="number"]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    
    .btn-solicitar {
      padding: 16px;
      background: linear-gradient(135deg, #FFD700, #FFA500);
      color: #000;
      border: none;
      border-radius: 10px;
      font-size: 1.1rem;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }
    
    .btn-solicitar:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(255,215,0,0.4);
    }
    
    .mensaje-exito {
      margin-top: 20px;
      padding: 15px;
      background: rgba(0,255,0,0.1);
      border: 2px solid rgba(0,255,0,0.3);
      border-radius: 10px;
      color: #0f0;
      text-align: center;
      font-weight: bold;
      animation: slideDown 0.5s ease;
      line-height: 1.6;
    }
    
    .mensaje-exito small {
      display: block;
      font-size: 0.85rem;
      margin-top: 5px;
      opacity: 0.8;
      font-weight: normal;
    }
    
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

      .filtros {
        grid-template-columns: 1fr;
      }
      
      .productos-grid {
        grid-template-columns: 1fr;
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
  </style>
  <link rel=icon href="../favicon.ico" type="image/x-icon">
</head>
<body>

  <header class="header">
    <div class="logo">
      <img src="../assets/images/logo.png" alt="Auto Stok Logo" style="height: 70px; width: auto; vertical-align: middle; margin-right: 8px; filter: drop-shadow(1px 0 0 white) drop-shadow(-1px 0 0 white) drop-shadow(0 1px 0 white) drop-shadow(0 -1px 0 white);">
    </div>
    
    <div class="menu-toggle" id="menuToggle">
      <span></span>
      <span></span>
      <span></span>
    </div>
    
    <nav id="navMenu">
      <a href="../index.php">Inicio</a>
      <a href="../vehiculos/catalogo.php">Vehículos</a>
      <a href="servicios.php">Servicios</a>
      <a href="../nosotros.php">Nosotros</a>
      <a href="../autostok-team.php">Team</a>
      <a href="../contacto.php">Contacto</a>
    </nav>
  </header>

  <main>
    <!-- Selector de Vista -->
    <div class="vista-selector">
      <button class="vista-btn active" id="btnVistaServicios" onclick="cambiarVista('servicios')">
        🔧 Servicios de Taller
      </button>
      <button class="vista-btn" id="btnVistaRepuestos" onclick="cambiarVista('repuestos')">
        🛒 Tienda de Repuestos
      </button>
    </div>

    <!-- SECCIÓN DE SERVICIOS -->
    <section id="serviciosView">
      <div class="hero-section">
        <h1 class="titulo-principal">Nuestros Servicios</h1>
        <p class="subtitulo-principal">Calidad y profesionalismo en cada detalle</p>
      </div>

      <!-- Selector de Sucursal para Servicios -->
      <div class="sucursal-selector">
        <label for="sucursalServicio">📍 Selecciona tu sucursal:</label>
        <select id="sucursalServicio">
          <option value="">Seleccionar sucursal</option>
          <option value="sucursal1">Sede Morato</option>
          <option value="sucursal2">Sede Felicidad</option>
        </select>
      </div>

      <!-- Filtro de categorías -->
      <section class="categorias-tabs">
        <button class="tab-btn active" onclick="filtrarCategoria('todas')">Todas</button>
        <?php foreach (array_keys($categorias) as $cat): ?>
          <button class="tab-btn" onclick="filtrarCategoria('<?php echo htmlspecialchars($cat); ?>')">
            <?php echo htmlspecialchars($cat); ?>
          </button>
        <?php endforeach; ?>
      </section>

      <!-- Servicios Grid -->
      <section class="servicios-container">
        <?php foreach ($servicios as $servicio): ?>
          <div class="servicio-card" data-categoria="<?php echo htmlspecialchars($servicio['categoria'] ?? 'Otros'); ?>">
            <div class="card-imagen">
              <img src="<?php echo htmlspecialchars($servicio['imagen'] ?? 'https://via.placeholder.com/400x300?text=Sin+Imagen'); ?>" 
                   alt="<?php echo htmlspecialchars($servicio['nombre']); ?>"
                   onerror="this.src='https://via.placeholder.com/400x300?text=Sin+Imagen'">
              <div class="card-badge"><?php echo htmlspecialchars($servicio['categoria'] ?? 'Servicio'); ?></div>
            </div>
            <div class="card-content">
              <h3><?php echo htmlspecialchars($servicio['nombre']); ?></h3>
              <p class="descripcion"><?php echo htmlspecialchars($servicio['descripcion_corta'] ?? ''); ?></p>
              <div class="precio-duracion">
                <span class="precio">$<?php echo number_format($servicio['precio']); ?></span>
                <?php if (!empty($servicio['duracion'])): ?>
                  <span class="duracion">⏱️ <?php echo htmlspecialchars($servicio['duracion']); ?></span>
                <?php endif; ?>
              </div>
              <button class="btn-ver-mas" onclick="abrirModalServicio(<?php echo $servicio['id']; ?>); event.stopPropagation();">
                Ver detalles
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </section>
    </section>

    <!-- SECCIÓN DE REPUESTOS -->
    <section id="repuestosView" style="display:none;">
      <div class="hero-section">
        <h1 class="titulo-principal">Tienda de Repuestos</h1>
        <p class="subtitulo-principal">Encuentra los mejores repuestos para tu vehículo</p>
      </div>

      <section class="filtros-container">
        <div class="filtros">
          <input type="text" id="buscarProducto" placeholder="🔍 Buscar repuesto...">
          <select id="categoriaProducto">
            <option value="">Todas las categorías</option>
            <?php foreach ($categoriasProductos as $cat): ?>
              <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
          </select>
          <button id="limpiarFiltrosProductos" class="btn-limpiar">Limpiar</button>
        </div>
      </section>

      <section class="productos-grid" id="productosContainer"></section>
    </section>
  </main>

  <!-- Modal de detalles del servicio -->
  <div id="modalServicio" class="modal">
    <div class="modal-content">
      <button class="btn-cerrar-modal" id="cerrarModal">✕</button>
      
      <div class="modal-header">
        <img id="imagenServicio" src="" alt="Servicio">
        <div class="header-info">
          <h2 id="nombreServicio"></h2>
          <p class="categoria-badge" id="categoriaServicio"></p>
        </div>
      </div>

      <div class="modal-body">
        <div class="descripcion-completa" id="descripcionCompleta"></div>

        <div class="detalles-servicio">
          <div class="detalle-item">
            <span class="label">Precio</span>
            <span class="valor precio-valor" id="precioServicio"></span>
          </div>
          <div class="detalle-item" id="duracionContainer">
            <span class="label">Duración</span>
            <span class="valor" id="duracionServicio"></span>
          </div>
        </div>

        <div class="caracteristicas" id="caracteristicasServicio"></div>

        <button class="btn-pago" id="btnPagoServicio">💳 Realizar Pago</button>

        <div class="cita-section">
          <h3>Solicitar Cita</h3>
          <form id="formCita">
            <input type="hidden" id="servicioId">
            <input type="text" id="nombreCita" placeholder="Nombre completo" required>
            <input type="tel" id="telefonoCita" placeholder="Teléfono" required>
            <input type="email" id="correoCita" placeholder="Correo electrónico" required>
            <input type="date" id="fechaCita" required min="<?php echo date('Y-m-d'); ?>">
            <select id="horaCita" required>
              <option value="">Seleccionar hora</option>
              <option value="08:00">08:00 AM</option>
              <option value="09:00">09:00 AM</option>
              <option value="10:00">10:00 AM</option>
              <option value="11:00">11:00 AM</option>
              <option value="14:00">02:00 PM</option>
              <option value="15:00">03:00 PM</option>
              <option value="16:00">04:00 PM</option>
              <option value="17:00">05:00 PM</option>
            </select>
            <textarea id="notasCita" placeholder="Notas adicionales (opcional)" rows="3"></textarea>
            <button type="submit" class="btn-solicitar">Solicitar Cita</button>
          </form>
          <div id="mensajeCita" class="mensaje-exito" style="display:none;">
            ✓ Cita solicitada con éxito. Recibirás confirmación por correo.
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Producto -->
  <div id="modalProducto" class="modal">
    <div class="modal-content">
      <button class="btn-cerrar-modal" onclick="cerrarModalProducto()">✕</button>
      
      <div class="modal-header">
        <img id="imagenProducto" src="" alt="Producto">
        <div class="header-info">
          <h2 id="nombreProducto"></h2>
          <p class="categoria-badge" id="categoriaProductoBadge"></p>
        </div>
      </div>

      <div class="modal-body">
        <div class="descripcion-completa" id="descripcionProducto"></div>

        <div class="detalles-servicio">
          <div class="detalle-item">
            <span class="label">Precio</span>
            <span class="valor precio-valor" id="precioProducto"></span>
          </div>
          <div class="detalle-item">
            <span class="label">Stock</span>
            <span class="valor" id="stockProducto"></span>
          </div>
          <div class="detalle-item">
            <span class="label">Marca</span>
            <span class="valor" id="marcaProducto"></span>
          </div>
          <div class="detalle-item">
            <span class="label">Código</span>
            <span class="valor" id="codigoProducto"></span>
          </div>
        </div>

        <div class="contacto-section">
          <h3>Solicitar Producto</h3>
          <form id="formProducto">
            <input type="hidden" id="productoId">
            <input type="text" id="nombreProductoSol" placeholder="Nombre completo" required>
            <input type="tel" id="telefonoProductoSol" placeholder="Teléfono" required>
            <input type="email" id="correoProductoSol" placeholder="Correo electrónico" required>
            <input type="number" id="cantidadProducto" placeholder="Cantidad" min="1" required value="1">
            <textarea id="notasProducto" placeholder="Notas adicionales (opcional)" rows="3"></textarea>
            <button type="submit" class="btn-solicitar">Solicitar Cotización</button>
          </form>
          <div id="mensajeProducto" class="mensaje-exito" style="display:none;">
            ✓ Solicitud enviada. Te contactaremos pronto.
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Botón flotante PSE -->
  <button class="pse-flotante" onclick="abrirPagoPSE()">
    💳 Realizar Pago
  </button>

  <?php include '../footer.php'; ?>

  <script>
    const servicios = <?php echo json_encode($servicios); ?>;
    const productos = <?php echo json_encode($productos); ?>;
    let servicioActual = null;
    let productoActual = null;
    let configSitio = {};
    
    // Cargar configuración
    async function cargarConfiguracion() {
      try {
        const response = await fetch('../data/configuracion.json');
        configSitio = await response.json();
        console.log('Configuración cargada:', configSitio);
      } catch (err) {
        console.error('Error cargando configuración:', err);
      }
    }
    
    cargarConfiguracion();

    function abrirPagoPSE() {
      const urlPSE = configSitio.pagos?.urlPagos || 'https://www.psepagos.co/PSEHostingUI/ShowTicketOffice.aspx?ID=2979';
      window.open(urlPSE, '_blank');
    }

    // Cambiar entre vistas
    function cambiarVista(vista) {
      const serviciosView = document.getElementById('serviciosView');
      const repuestosView = document.getElementById('repuestosView');
      const btnServicios = document.getElementById('btnVistaServicios');
      const btnRepuestos = document.getElementById('btnVistaRepuestos');
      
      if (vista === 'servicios') {
        serviciosView.style.display = 'block';
        repuestosView.style.display = 'none';
        btnServicios.classList.add('active');
        btnRepuestos.classList.remove('active');
      } else {
        serviciosView.style.display = 'none';
        repuestosView.style.display = 'block';
        btnServicios.classList.remove('active');
        btnRepuestos.classList.add('active');
        mostrarProductos(productos);
      }
    }

    // Mostrar productos
    function mostrarProductos(productosArray) {
      const container = document.getElementById('productosContainer');
      
      if (!productosArray || productosArray.length === 0) {
        container.innerHTML = '<p class="no-resultados">No se encontraron productos</p>';
        return;
      }
      
      const cards = productosArray.map(producto => {
        const stockClass = producto.stock > 10 ? 'stock-disponible' : producto.stock > 0 ? 'stock-bajo' : 'stock-agotado';
        const stockText = producto.stock > 0 ? `${producto.stock} disponibles` : 'Agotado';
        
        return `
          <div class="servicio-card" onclick="abrirModalProducto(${producto.id})">
            <div class="card-imagen">
              <img src="${producto.imagen}" alt="${producto.nombre}" onerror="this.src='https://via.placeholder.com/400x300?text=Sin+Imagen'">
              <div class="card-badge">${producto.categoria}</div>
            </div>
            <div class="card-content">
              <h3>${producto.nombre}</h3>
              <p class="descripcion">${producto.descripcion || ''}</p>
              <div class="precio-duracion">
                <span class="precio">$${Number(producto.precio).toLocaleString('es-CO')}</span>
                <span class="stock ${stockClass}">${stockText}</span>
              </div>
              <button class="btn-ver-mas" onclick="event.stopPropagation(); abrirModalProducto(${producto.id})">
                Ver detalles
              </button>
            </div>
          </div>
        `;
      }).join('');
      
      container.innerHTML = cards;
    }

    // Filtrar productos
    function filtrarProductos() {
      const buscar = document.getElementById('buscarProducto').value.toLowerCase();
      const categoria = document.getElementById('categoriaProducto').value;
      
      const filtrados = productos.filter(p => {
        const coincideBusqueda = !buscar || 
          p.nombre.toLowerCase().includes(buscar) || 
          (p.descripcion && p.descripcion.toLowerCase().includes(buscar)) ||
          (p.marca && p.marca.toLowerCase().includes(buscar));
        const coincideCategoria = !categoria || p.categoria === categoria;
        
        return coincideBusqueda && coincideCategoria;
      });
      
      mostrarProductos(filtrados);
    }

    document.getElementById('buscarProducto')?.addEventListener('input', filtrarProductos);
    document.getElementById('categoriaProducto')?.addEventListener('change', filtrarProductos);
    document.getElementById('limpiarFiltrosProductos')?.addEventListener('click', () => {
      document.getElementById('buscarProducto').value = '';
      document.getElementById('categoriaProducto').value = '';
      mostrarProductos(productos);
    });

    // Abrir modal de producto
    function abrirModalProducto(id) {
      productoActual = productos.find(p => p.id == id);
      if (!productoActual) return;
      
      document.getElementById('imagenProducto').src = productoActual.imagen || 'https://via.placeholder.com/800x400?text=Sin+Imagen';
      document.getElementById('nombreProducto').textContent = productoActual.nombre;
      document.getElementById('categoriaProductoBadge').textContent = productoActual.categoria;
      document.getElementById('descripcionProducto').textContent = productoActual.descripcion || 'Sin descripción';
      document.getElementById('precioProducto').textContent = `$${Number(productoActual.precio).toLocaleString('es-CO')}`;
      document.getElementById('stockProducto').textContent = productoActual.stock > 0 ? `${productoActual.stock} unidades` : 'Agotado';
      document.getElementById('marcaProducto').textContent = productoActual.marca || 'N/A';
      document.getElementById('codigoProducto').textContent = productoActual.codigo || 'N/A';
      document.getElementById('productoId').value = productoActual.id;
      
      document.getElementById('modalProducto').style.display = 'flex';
      document.getElementById('mensajeProducto').style.display = 'none';
    }

    function cerrarModalProducto() {
      document.getElementById('modalProducto').style.display = 'none';
    }

    // Enviar solicitud de producto
    document.getElementById('formProducto')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const datos = {
        producto_id: document.getElementById('productoId').value,
        producto_nombre: productoActual.nombre,
        nombre: document.getElementById('nombreProductoSol').value,
        telefono: document.getElementById('telefonoProductoSol').value,
        correo: document.getElementById('correoProductoSol').value,
        cantidad: document.getElementById('cantidadProducto').value,
        notas: document.getElementById('notasProducto').value
      };
      
      try {
        const response = await fetch('../admin/api/solicitar_producto.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(datos)
        });
        
        const result = await response.json();
        
        if (result.success) {
          document.getElementById('formProducto').reset();
          document.getElementById('mensajeProducto').style.display = 'block';
          document.getElementById('mensajeProducto').innerHTML = '✓ Solicitud enviada. Te contactaremos pronto.<br><small>Tu solicitud ha sido recibida y enviada al almacén.</small>';
          
          setTimeout(() => {
            document.getElementById('mensajeProducto').style.display = 'none';
            document.getElementById('modalProducto').style.display = 'none';
          }, 5000);
        } else {
          alert('Error al enviar solicitud: ' + (result.message || 'Intenta nuevamente'));
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Error al enviar la solicitud');
      }
    });

    // Funciones de servicios (existentes)
    function filtrarCategoria(categoria) {
      const cards = document.querySelectorAll('.servicio-card');
      const buttons = document.querySelectorAll('.tab-btn');
      
      buttons.forEach(btn => btn.classList.remove('active'));
      event.target.classList.add('active');
      
      cards.forEach(card => {
        if (categoria === 'todas' || card.dataset.categoria === categoria) {
          card.style.display = 'block';
          setTimeout(() => card.style.opacity = '1', 10);
        } else {
          card.style.opacity = '0';
          setTimeout(() => card.style.display = 'none', 300);
        }
      });
    }

    function abrirModalServicio(id) {
      servicioActual = servicios.find(s => s.id == id);
      if (!servicioActual) return;
      
      document.getElementById('imagenServicio').src = servicioActual.imagen || 'https://via.placeholder.com/800x400?text=Sin+Imagen';
      document.getElementById('nombreServicio').textContent = servicioActual.nombre;
      document.getElementById('categoriaServicio').textContent = servicioActual.categoria || 'Servicio';
      document.getElementById('descripcionCompleta').textContent = servicioActual.descripcion || 'Sin descripción';
      document.getElementById('precioServicio').textContent = `$${Number(servicioActual.precio).toLocaleString('es-CO')}`;
      
      const duracionContainer = document.getElementById('duracionContainer');
      if (servicioActual.duracion) {
        duracionContainer.style.display = 'flex';
        document.getElementById('duracionServicio').textContent = servicioActual.duracion;
      } else {
        duracionContainer.style.display = 'none';
      }
      
      const caracteristicasDiv = document.getElementById('caracteristicasServicio');
      if (servicioActual.caracteristicas && servicioActual.caracteristicas.length > 0) {
        caracteristicasDiv.innerHTML = '<h4>Incluye:</h4><ul>' + 
          servicioActual.caracteristicas.map(c => `<li>✓ ${c}</li>`).join('') + 
          '</ul>';
        caracteristicasDiv.style.display = 'block';
      } else {
        caracteristicasDiv.style.display = 'none';
      }
      
      document.getElementById('servicioId').value = servicioActual.id;
      document.getElementById('modalServicio').style.display = 'flex';
      document.getElementById('mensajeCita').style.display = 'none';
    }

    document.getElementById('cerrarModal').onclick = () => {
      document.getElementById('modalServicio').style.display = 'none';
    };

    document.getElementById('btnPagoServicio').onclick = () => {
      abrirPagoPSE();
    };

    // Enviar cita con sucursal seleccionada
    document.getElementById('formCita').onsubmit = async (e) => {
      e.preventDefault();
      
      const sucursalSeleccionada = document.getElementById('sucursalServicio').value;
      
      if (!sucursalSeleccionada) {
        alert('Por favor selecciona una sucursal');
        return;
      }
      
      const datos = {
        servicio_id: document.getElementById('servicioId').value,
        servicio_nombre: servicioActual.nombre,
        nombre: document.getElementById('nombreCita').value,
        telefono: document.getElementById('telefonoCita').value,
        correo: document.getElementById('correoCita').value,
        fecha: document.getElementById('fechaCita').value,
        hora: document.getElementById('horaCita').value,
        notas: document.getElementById('notasCita').value,
        sucursal: sucursalSeleccionada
      };
      
      try {
        const response = await fetch('../admin/api/solicitar_cita.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(datos)
        });
        
        const result = await response.json();
        
        if (result.success) {
          document.getElementById('formCita').reset();
          document.getElementById('mensajeCita').style.display = 'block';
          document.getElementById('mensajeCita').innerHTML = '✓ Cita solicitada con éxito. Recibirás confirmación por correo.<br><small>La cita ha sido registrada y notificada a la sucursal.</small>';
          
          setTimeout(() => {
            document.getElementById('mensajeCita').style.display = 'none';
            document.getElementById('modalServicio').style.display = 'none';
          }, 5000);
        } else {
          alert('Error al solicitar la cita: ' + (result.message || 'Intenta nuevamente'));
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Error al enviar la solicitud');
      }
    };
    
    // ===== CARGAR HORARIOS DISPONIBLES =====
    async function cargarHorariosDisponibles() {
      const fecha = document.getElementById('fechaCita').value;
      const sucursal = document.getElementById('sucursalServicio').value;
      
      if (!fecha || !sucursal) {
        return;
      }
      
      try {
        // Llamar al API para obtener horarios ocupados
        const response = await fetch(`../admin/api/horarios_disponibles.php?fecha=${fecha}&sucursal=${sucursal}`);
        
        if (!response.ok) {
          throw new Error('Error al cargar horarios');
        }
        
        const result = await response.json();
        
        if (result.success) {
          const horasOcupadas = result.horarios_ocupados || [];
          
          console.log('Horarios ocupados:', horasOcupadas);
          
          // Actualizar el select de horas
          const selectHora = document.getElementById('horaCita');
          const opciones = selectHora.querySelectorAll('option');
          
          opciones.forEach(opcion => {
            if (opcion.value) {
              if (horasOcupadas.includes(opcion.value)) {
                opcion.disabled = true;
                opcion.style.color = '#999';
                opcion.style.background = '#f0f0f0';
                // Limpiar texto previo
                const textoBase = opcion.textContent.split(' -')[0].split(' (')[0].trim();
                opcion.textContent = textoBase + ' - No disponible';
              } else {
                opcion.disabled = false;
                opcion.style.color = '';
                opcion.style.background = '';
                // Limpiar texto previo
                const textoBase = opcion.textContent.split(' -')[0].split(' (')[0].trim();
                opcion.textContent = textoBase;
              }
            }
          });
          
          // Si la hora seleccionada actualmente está ocupada, limpiar selección
          if (selectHora.value && horasOcupadas.includes(selectHora.value)) {
            selectHora.value = '';
            mostrarNotificacion('La hora seleccionada ya no está disponible', 'warning');
          }
          
        } else {
          console.error('Error:', result.message);
        }
      } catch (error) {
        console.error('Error cargando horarios:', error);
      }
    }
    
    // Función auxiliar para mostrar notificaciones
    function mostrarNotificacion(mensaje, tipo = 'info') {
      // Crear elemento de notificación si no existe
      let notif = document.getElementById('notificacion-horario');
      if (!notif) {
        notif = document.createElement('div');
        notif.id = 'notificacion-horario';
        notif.style.cssText = `
          position: fixed;
          top: 80px;
          right: 20px;
          padding: 15px 20px;
          border-radius: 8px;
          color: white;
          font-weight: 600;
          z-index: 10000;
          box-shadow: 0 4px 12px rgba(0,0,0,0.3);
          animation: slideIn 0.3s ease;
        `;
        document.body.appendChild(notif);
      }
      
      // Colores según tipo
      const colores = {
        'success': '#28a745',
        'error': '#dc3545',
        'warning': '#ffc107',
        'info': '#17a2b8'
      };
      
      notif.style.background = colores[tipo] || colores.info;
      notif.textContent = mensaje;
      notif.style.display = 'block';
      
      // Ocultar después de 3 segundos
      setTimeout(() => {
        notif.style.display = 'none';
      }, 3000);
    }
    
    // Agregar listeners para actualizar horarios cuando cambia fecha o sucursal
    document.getElementById('fechaCita')?.addEventListener('change', cargarHorariosDisponibles);
    document.getElementById('sucursalServicio')?.addEventListener('change', cargarHorariosDisponibles);
    
    // También cargar cuando se abre el modal de cita
    const originalAbrirModal = window.abrirModalServicio;
    window.abrirModalServicio = function(id) {
      if (originalAbrirModal) originalAbrirModal(id);
      
      // Esperar un momento para que el modal se muestre
      setTimeout(() => {
        cargarHorariosDisponibles();
      }, 300);
    };
    
    // Agregar listeners para actualizar horarios
    document.getElementById('fechaCita')?.addEventListener('change', cargarHorariosDisponibles);
    document.getElementById('sucursalServicio')?.addEventListener('change', cargarHorariosDisponibles);

    // Cerrar modales al hacer clic fuera
    window.onclick = (e) => {
      if (e.target.id === 'modalServicio') {
        document.getElementById('modalServicio').style.display = 'none';
      }
      if (e.target.id === 'modalProducto') {
        document.getElementById('modalProducto').style.display = 'none';
      }
    };
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