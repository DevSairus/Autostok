<?php
// Cargar vehículos desde JSON - Ruta corregida
$rutaJSON = __DIR__ . '/../data/vehiculos.json';
$vehiculosData = file_exists($rutaJSON) 
  ? json_decode(file_get_contents($rutaJSON), true) 
  : ['vehiculos' => []];
$vehiculos = $vehiculosData['vehiculos'] ?? [];

// Debug
error_log("Ruta JSON: " . $rutaJSON);
error_log("Archivo existe: " . (file_exists($rutaJSON) ? 'SI' : 'NO'));
error_log("Vehículos cargados: " . count($vehiculos));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Catálogo de Vehículos - Autostok</title>
  <link rel="stylesheet" href="css/catalogo.css">
  <link rel=icon href="../favicon.ico" type="image/x-icon">
</head>
<body>

  <header class="header">
    <div class="logo">🚗 Autostok</div>
    <nav>
      <a href="../index.php">Inicio</a>
      <a href="catalogo.php">Vehículos</a>
      <a href="../servicios/servicios.php">Servicios</a>
      <a href="../nosotros.php">Nosotros</a>
      <a href="../contacto.php">Contacto</a>
    </nav>
  </header>

  <main>
    <div class="hero-section">
      <h1 class="titulo-principal">Catálogo de Vehículos Premium</h1>
      <p class="subtitulo-principal">Encuentra el vehículo de tus sueños</p>
    </div>

    <section class="filtros-container">
      <div class="filtros">
        <input type="text" id="buscarInput" placeholder="🔍 Buscar vehículo...">
        <select id="marcaFiltro">
          <option value="">Todas las marcas</option>
        </select>
        <select id="modeloFiltro">
          <option value="">Todos los modelos</option>
        </select>
        <select id="tipoFiltro">
          <option value="">Todos los tipos</option>
          <option value="sedan">Sedán</option>
          <option value="suv">SUV</option>
          <option value="pickup">Pickup</option>
          <option value="deportivo">Deportivo</option>
          <option value="hatchback">Hatchback</option>
        </select>
        <input type="number" id="anioMin" placeholder="Año mínimo">
        <input type="number" id="anioMax" placeholder="Año máximo">
        <input type="number" id="precioMin" placeholder="Precio mínimo">
        <input type="number" id="precioMax" placeholder="Precio máximo">
        <button id="limpiarFiltros" class="btn-limpiar">Limpiar Filtros</button>
      </div>
    </section>

    <section id="vehiculosContainer" class="vehiculos-grid"></section>
  </main>

  <!-- Modal de detalles -->
  <div id="modalDetalles" class="modal">
    <div class="modal-content">
      <button class="btn-cerrar-modal" id="cerrarModal">✕</button>
      
      <div class="modal-gallery">
        <button class="flecha flecha-izq" id="prevFoto">❮</button>
        <img id="fotoVehiculo" src="" alt="Vehículo">
        <button class="flecha flecha-der" id="nextFoto">❯</button>
        <div class="gallery-dots" id="galleryDots"></div>
      </div>

      <div class="modal-info">
        <div class="info-header">
          <h2 id="tituloVehiculo"></h2>
          <p class="precio-destacado" id="precioVehiculo"></p>
        </div>

        <p class="descripcion" id="descripcionVehiculo"></p>

        <div class="detalles-grid">
          <div class="detalle-item">
            <span class="detalle-label">Marca</span>
            <span class="detalle-valor" id="marcaVehiculo"></span>
          </div>
          <div class="detalle-item">
            <span class="detalle-label">Modelo</span>
            <span class="detalle-valor" id="modeloVehiculo"></span>
          </div>
          <div class="detalle-item">
            <span class="detalle-label">Año</span>
            <span class="detalle-valor" id="anioVehiculo"></span>
          </div>
          <div class="detalle-item">
            <span class="detalle-label">Kilometraje</span>
            <span class="detalle-valor" id="kmVehiculo"></span>
          </div>
          <div class="detalle-item">
            <span class="detalle-label">Tipo</span>
            <span class="detalle-valor" id="tipoVehiculo"></span>
          </div>
          <div class="detalle-item">
            <span class="detalle-label">Transmisión</span>
            <span class="detalle-valor" id="transmisionVehiculo"></span>
          </div>
          <div class="detalle-item">
            <span class="detalle-label">Combustible</span>
            <span class="detalle-valor" id="combustibleVehiculo"></span>
          </div>
          <div class="detalle-item">
            <span class="detalle-label">Color</span>
            <span class="detalle-valor" id="colorVehiculo"></span>
          </div>
        </div>

        <button class="btn-pago" id="btnPagoVehiculo">💳 Realizar Pago/Abono</button>

        <div class="contacto-section">
          <h3>Solicitar Información</h3>
          <form id="formContacto">
            <input type="text" id="nombre" placeholder="Nombre completo" required>
            <input type="tel" id="telefono" placeholder="Teléfono" required>
            <input type="email" id="correo" placeholder="Correo electrónico" required>
            <textarea id="mensaje" placeholder="Mensaje (opcional)" rows="3"></textarea>
            <button type="submit" class="btn-enviar">Enviar Solicitud</button>
          </form>

          <div id="whatsappContainer" style="display:none;">
            <button class="btn-whatsapp" id="btnWhatsapp">
              <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
              </svg>
              Contactar por WhatsApp
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <button class="pse-flotante" onclick="abrirPagoPSE()">💳 Realizar Pago</button>

  <?php include '../footer.php'; ?>

  <script>
    const vehiculos = <?php echo json_encode($vehiculos); ?>;
    let vehiculoActual = null;
    let fotoActualIndex = 0;
    let configSitio = {};

    console.log('Vehículos cargados:', vehiculos.length);

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

    function abrirPagoPSE() {
      const urlPSE = configSitio.pagos?.urlPagos || 'https://www.psepagos.co/PSEHostingUI/ShowTicketOffice.aspx?ID=2979';
      console.log('Abriendo PSE:', urlPSE);
      window.open(urlPSE, '_blank');
    }

    document.addEventListener('DOMContentLoaded', async () => {
      await cargarConfiguracion();
      
      if (vehiculos && vehiculos.length > 0) {
        cargarFiltros();
        mostrarVehiculos(vehiculos);
        setupEventListeners();
      } else {
        document.getElementById('vehiculosContainer').innerHTML = '<p class="no-resultados">No hay vehículos disponibles</p>';
      }
    });

    function cargarFiltros() {
      const marcas = [...new Set(vehiculos.map(v => v.marca))];
      const modelos = [...new Set(vehiculos.map(v => v.modelo))];
      
      const marcaFiltro = document.getElementById('marcaFiltro');
      const modeloFiltro = document.getElementById('modeloFiltro');
      
      marcas.forEach(marca => {
        const option = document.createElement('option');
        option.value = marca;
        option.textContent = marca;
        marcaFiltro.appendChild(option);
      });
      
      modelos.forEach(modelo => {
        const option = document.createElement('option');
        option.value = modelo;
        option.textContent = modelo;
        modeloFiltro.appendChild(option);
      });
    }

    function mostrarVehiculos(vehiculosArray) {
      const container = document.getElementById('vehiculosContainer');
      
      if (!vehiculosArray || vehiculosArray.length === 0) {
        container.innerHTML = '<p class="no-resultados">No se encontraron vehículos</p>';
        return;
      }
      
      const cards = vehiculosArray.map(vehiculo => {
        const imagen = vehiculo.imagenes && vehiculo.imagenes[0] ? vehiculo.imagenes[0] : 'https://via.placeholder.com/400x300?text=Sin+Imagen';
        return '<div class="vehiculo-card" onclick="abrirModal(' + vehiculo.id + ')">' +
          '<div class="card-imagen">' +
            '<img src="' + imagen + '" alt="' + vehiculo.marca + ' ' + vehiculo.modelo + '" onerror="this.src=\'https://via.placeholder.com/400x300?text=Sin+Imagen\'">' +
            '<div class="card-badge">' + vehiculo.tipo + '</div>' +
          '</div>' +
          '<div class="card-content">' +
            '<h3>' + vehiculo.marca + ' ' + vehiculo.modelo + '</h3>' +
            '<p class="card-precio">$' + Number(vehiculo.precio).toLocaleString('es-CO') + '</p>' +
            '<div class="card-detalles">' +
              '<span>📅 ' + vehiculo.anio + '</span>' +
              '<span>🛣️ ' + Number(vehiculo.kilometraje).toLocaleString('es-CO') + ' km</span>' +
            '</div>' +
          '</div>' +
        '</div>';
      }).join('');
      
      container.innerHTML = cards;
    }

    function abrirModal(id) {
      vehiculoActual = vehiculos.find(v => v.id == id);
      if (!vehiculoActual) return;
      
      fotoActualIndex = 0;
      document.getElementById('tituloVehiculo').textContent = vehiculoActual.marca + ' ' + vehiculoActual.modelo;
      document.getElementById('precioVehiculo').textContent = '$' + Number(vehiculoActual.precio).toLocaleString('es-CO');
      document.getElementById('descripcionVehiculo').textContent = vehiculoActual.descripcion || 'Sin descripción';
      document.getElementById('marcaVehiculo').textContent = vehiculoActual.marca;
      document.getElementById('modeloVehiculo').textContent = vehiculoActual.modelo;
      document.getElementById('anioVehiculo').textContent = vehiculoActual.anio;
      document.getElementById('kmVehiculo').textContent = Number(vehiculoActual.kilometraje).toLocaleString('es-CO') + ' km';
      document.getElementById('tipoVehiculo').textContent = vehiculoActual.tipo;
      document.getElementById('transmisionVehiculo').textContent = vehiculoActual.transmision || 'N/A';
      document.getElementById('combustibleVehiculo').textContent = vehiculoActual.combustible || 'N/A';
      document.getElementById('colorVehiculo').textContent = vehiculoActual.color || 'N/A';
      
      actualizarFoto();
      document.getElementById('modalDetalles').style.display = 'flex';
      document.getElementById('whatsappContainer').style.display = 'none';
    }

    function actualizarFoto() {
      const fotos = vehiculoActual.imagenes || ['https://via.placeholder.com/800x600?text=Sin+Imagen'];
      const imgElement = document.getElementById('fotoVehiculo');
      imgElement.src = fotos[fotoActualIndex];
      imgElement.onerror = function() {
        this.src = 'https://via.placeholder.com/800x600?text=Sin+Imagen';
      };
      
      const dotsContainer = document.getElementById('galleryDots');
      const dots = fotos.map(function(_, i) {
        return '<span class="dot ' + (i === fotoActualIndex ? 'active' : '') + '" onclick="fotoActualIndex=' + i + '; actualizarFoto()"></span>';
      }).join('');
      dotsContainer.innerHTML = dots;
    }

    function setupEventListeners() {
      document.getElementById('cerrarModal').onclick = function() {
        document.getElementById('modalDetalles').style.display = 'none';
      };
      
      document.getElementById('prevFoto').onclick = function() {
        if (vehiculoActual.imagenes && vehiculoActual.imagenes.length > 0) {
          fotoActualIndex = (fotoActualIndex - 1 + vehiculoActual.imagenes.length) % vehiculoActual.imagenes.length;
          actualizarFoto();
        }
      };
      
      document.getElementById('nextFoto').onclick = function() {
        if (vehiculoActual.imagenes && vehiculoActual.imagenes.length > 0) {
          fotoActualIndex = (fotoActualIndex + 1) % vehiculoActual.imagenes.length;
          actualizarFoto();
        }
      };
      
      document.getElementById('formContacto').onsubmit = async function(e) {
        e.preventDefault();
        const nombre = document.getElementById('nombre').value;
        const telefono = document.getElementById('telefono').value;
        const correo = document.getElementById('correo').value;
        
        const solicitud = {
          tipo: 'vehiculo',
          vehiculo_id: vehiculoActual.id,
          vehiculo_nombre: vehiculoActual.marca + ' ' + vehiculoActual.modelo + ' ' + vehiculoActual.anio,
          nombre: nombre,
          telefono: telefono,
          correo: correo
        };
        
        try {
          await fetch('../admin/api/guardar_solicitud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(solicitud)
          });
        } catch(err) {
          console.error('Error guardando solicitud:', err);
        }
        
        document.getElementById('whatsappContainer').style.display = 'block';
        document.getElementById('btnWhatsapp').onclick = function() {
          const mensaje = 'Hola, estoy interesado en el ' + vehiculoActual.marca + ' ' + vehiculoActual.modelo + ' ' + vehiculoActual.anio + '. Mi nombre es ' + nombre + ', teléfono: ' + telefono + ', correo: ' + correo;
          const numeroWhatsapp = configSitio.general?.telefonoWhatsappVehiculos || '573001234567';
          const numeroLimpio = numeroWhatsapp.replace(/[^0-9]/g, '');
          window.open('https://wa.me/' + numeroLimpio + '?text=' + encodeURIComponent(mensaje), '_blank');
        };
      };
      
      document.getElementById('btnPagoVehiculo').onclick = function() {
        abrirPagoPSE();
      };
      
      ['buscarInput', 'marcaFiltro', 'modeloFiltro', 'tipoFiltro', 'anioMin', 'anioMax', 'precioMin', 'precioMax'].forEach(function(id) {
        const elem = document.getElementById(id);
        if (elem) elem.addEventListener('input', filtrarVehiculos);
      });
      
      document.getElementById('limpiarFiltros').onclick = function() {
        document.getElementById('buscarInput').value = '';
        document.getElementById('marcaFiltro').value = '';
        document.getElementById('modeloFiltro').value = '';
        document.getElementById('tipoFiltro').value = '';
        document.getElementById('anioMin').value = '';
        document.getElementById('anioMax').value = '';
        document.getElementById('precioMin').value = '';
        document.getElementById('precioMax').value = '';
        filtrarVehiculos();
      };
    }

    function filtrarVehiculos() {
      const buscar = document.getElementById('buscarInput').value.toLowerCase();
      const marca = document.getElementById('marcaFiltro').value;
      const modelo = document.getElementById('modeloFiltro').value;
      const tipo = document.getElementById('tipoFiltro').value;
      const anioMin = document.getElementById('anioMin').value;
      const anioMax = document.getElementById('anioMax').value;
      const precioMin = document.getElementById('precioMin').value;
      const precioMax = document.getElementById('precioMax').value;
      
      const filtrados = vehiculos.filter(function(v) {
        const coincideBusqueda = !buscar || 
          v.marca.toLowerCase().includes(buscar) || 
          v.modelo.toLowerCase().includes(buscar) ||
          (v.descripcion && v.descripcion.toLowerCase().includes(buscar));
        const coincideMarca = !marca || v.marca === marca;
        const coincideModelo = !modelo || v.modelo === modelo;
        const coincideTipo = !tipo || v.tipo === tipo;
        const coincideAnioMin = !anioMin || v.anio >= parseInt(anioMin);
        const coincideAnioMax = !anioMax || v.anio <= parseInt(anioMax);
        const coincidePrecioMin = !precioMin || v.precio >= parseFloat(precioMin);
        const coincidePrecioMax = !precioMax || v.precio <= parseFloat(precioMax);
        
        return coincideBusqueda && coincideMarca && coincideModelo && coincideTipo && 
               coincideAnioMin && coincideAnioMax && coincidePrecioMin && coincidePrecioMax;
      });
      
      mostrarVehiculos(filtrados);
    }

    window.onclick = function(e) {
      if (e.target.id === 'modalDetalles') {
        document.getElementById('modalDetalles').style.display = 'none';
      }
    };
  </script>
</body>
</html>