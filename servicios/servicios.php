<?php
// Cargar servicios desde JSON - Ruta corregida
$serviciosData = file_exists('../data/servicios.json') 
  ? json_decode(file_get_contents('../data/servicios.json'), true) 
  : ['servicios' => []];
$servicios = $serviciosData['servicios'] ?? [];

// Cargar productos desde JSON
$productosData = file_exists('../data/productos.json') 
  ? json_decode(file_get_contents('../data/productos.json'), true) 
  : ['productos' => []];
$productos = $productosData['productos'] ?? [];

// Agrupar por categorías
$categorias = [];
foreach ($servicios as $servicio) {
  $cat = $servicio['categoria'] ?? 'Otros';
  if (!isset($categorias[$cat])) {
    $categorias[$cat] = [];
  }
  $categorias[$cat][] = $servicio;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Servicios y Repuestos - Autostok</title>
  <link rel="stylesheet" href="../css/servicios.css">
</head>
<body>

  <header class="header">
    <div class="logo">🚗 Autostok</div>
    <nav>
      <a href="../index.php">Inicio</a>
      <a href="../vehiculos/catalogo.php">Vehículos</a>
      <a href="servicios.php">Servicios</a>
      <a href="../nosotros.php">Nosotros</a>
      <a href="../contacto.php">Contacto</a>
    </nav>
  </header>

  <main>
    <!-- Selector de Vista -->
    <div class="vista-selector">
      <button class="vista-btn active" onclick="cambiarVista('servicios')">
        🔧 Servicios de Taller
      </button>
      <button class="vista-btn" onclick="cambiarVista('repuestos')">
        🛒 Tienda de Repuestos
      </button>
    </div>
    <div class="hero-section">
      <h1 class="titulo-principal">Nuestros Servicios</h1>
      <p class="subtitulo-principal">Calidad y profesionalismo en cada detalle</p>
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
            <input type="number" id="cantidadProducto" placeholder="Cantidad" min="1" required>
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

  <script>
    const servicios = <?php echo json_encode($servicios); ?>;
    let servicioActual = null;

    // Cargar configuración
    let configSitio = {};
    
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
      console.log('Abriendo PSE:', urlPSE);
      window.open(urlPSE, '_blank');
    }

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
      
      const imgElement = document.getElementById('imagenServicio');
      imgElement.src = servicioActual.imagen || 'https://via.placeholder.com/800x400?text=Sin+Imagen';
      imgElement.onerror = function() {
        this.src = 'https://via.placeholder.com/800x400?text=Sin+Imagen';
      };
      
      document.getElementById('nombreServicio').textContent = servicioActual.nombre;
      document.getElementById('categoriaServicio').textContent = servicioActual.categoria || 'Servicio';
      document.getElementById('descripcionCompleta').textContent = servicioActual.descripcion || 'Sin descripción';
      document.getElementById('precioServicio').textContent = `${Number(servicioActual.precio).toLocaleString('es-CO')}`;
      
      const duracionContainer = document.getElementById('duracionContainer');
      if (servicioActual.duracion) {
        duracionContainer.style.display = 'flex';
        document.getElementById('duracionServicio').textContent = servicioActual.duracion;
      } else {
        duracionContainer.style.display = 'none';
      }
      
      // Características
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

    document.getElementById('formCita').onsubmit = async (e) => {
      e.preventDefault();
      
      const datos = {
        servicio_id: document.getElementById('servicioId').value,
        servicio_nombre: servicioActual.nombre,
        nombre: document.getElementById('nombreCita').value,
        telefono: document.getElementById('telefonoCita').value,
        correo: document.getElementById('correoCita').value,
        fecha: document.getElementById('fechaCita').value,
        hora: document.getElementById('horaCita').value,
        notas: document.getElementById('notasCita').value
      };
      
      console.log('Enviando cita:', datos);
      
      try {
        const response = await fetch('../admin/api/solicitar_cita.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(datos)
        });
        
        const result = await response.json();
        console.log('Respuesta:', result);
        
        if (result.success) {
          document.getElementById('formCita').reset();
          document.getElementById('mensajeCita').style.display = 'block';
          setTimeout(() => {
            document.getElementById('mensajeCita').style.display = 'none';
          }, 5000);
        } else {
          alert('Error al solicitar la cita: ' + (result.message || 'Intenta nuevamente'));
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Error al enviar la solicitud: ' + error.message);
      }
    };

    // Cerrar modal al hacer clic fuera
    window.onclick = (e) => {
      const modal = document.getElementById('modalServicio');
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    };
  </script>

  <?php include '../footer.php'; ?>

</body>
</html>