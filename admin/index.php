<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Cargar datos
$vehiculosData = file_exists('../data/vehiculos.json') 
    ? json_decode(file_get_contents('../data/vehiculos.json'), true) 
    : ['vehiculos' => []];
$vehiculos = $vehiculosData['vehiculos'] ?? [];

$serviciosData = file_exists('../data/servicios.json') 
    ? json_decode(file_get_contents('../data/servicios.json'), true) 
    : ['servicios' => []];
$servicios = $serviciosData['servicios'] ?? [];

$productosData = file_exists('../data/productos.json') 
    ? json_decode(file_get_contents('../data/productos.json'), true) 
    : ['productos' => []];
$productos = $productosData['productos'] ?? [];

$citasData = file_exists('../data/citas.json') 
    ? json_decode(file_get_contents('../data/citas.json'), true) 
    : ['citas' => []];
$citas = $citasData['citas'] ?? [];

$solicitudesData = file_exists('../data/solicitudes.json') 
    ? json_decode(file_get_contents('../data/solicitudes.json'), true) 
    : ['solicitudes' => []];
$solicitudes = $solicitudesData['solicitudes'] ?? [];

// Estadísticas
$totalVehiculos = count($vehiculos);
$totalServicios = count($servicios);
$totalProductos = count($productos);
$citasPendientes = count(array_filter($citas, fn($c) => ($c['estado'] ?? 'pendiente') === 'pendiente'));
$solicitudesPendientes = count(array_filter($solicitudes, fn($s) => ($s['estado'] ?? 'pendiente') === 'pendiente'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Administración - Autostok</title>
  <link rel="stylesheet" href="css/admin.css">
  <link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>

  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>🚗 Autostok</h2>
        <p>Panel Admin</p>
      </div>

      <nav class="sidebar-nav">
        <a href="#dashboard" class="nav-item active" onclick="mostrarSeccion('dashboard')">
          <span class="icon">📊</span>
          <span>Dashboard</span>
        </a>
        <a href="#imagenes" class="nav-item" onclick="mostrarSeccion('imagenes')">
          <span class="icon">🖼️</span>
          <span>Imágenes Home</span>
        </a>
        <a href="#vehiculos" class="nav-item" onclick="mostrarSeccion('vehiculos')">
          <span class="icon">🚗</span>
          <span>Vehículos</span>
        </a>
        <a href="#servicios" class="nav-item" onclick="mostrarSeccion('servicios')">
          <span class="icon">🔧</span>
          <span>Servicios</span>
        </a>
        <a href="#productos" class="nav-item" onclick="mostrarSeccion('productos')">
          <span class="icon">🛒</span>
          <span>Productos</span>
        </a>
        <a href="#citas" class="nav-item" onclick="mostrarSeccion('citas')">
          <span class="icon">📅</span>
          <span>Citas</span>
          <?php if ($citasPendientes > 0): ?>
            <span class="badge"><?php echo $citasPendientes; ?></span>
          <?php endif; ?>
        </a>
        <a href="#solicitudes" class="nav-item" onclick="mostrarSeccion('solicitudes')">
          <span class="icon">📧</span>
          <span>Solicitudes</span>
          <?php if ($solicitudesPendientes > 0): ?>
            <span class="badge"><?php echo $solicitudesPendientes; ?></span>
          <?php endif; ?>
        </a>
        <a href="#logs" class="nav-item" onclick="mostrarSeccion('logs')">
          <span class="icon">📋</span>
          <span>Logs</span>
        </a>
        <?php if ($_SESSION['admin_rol'] === 'super_admin'): ?>
        <a href="#usuarios" class="nav-item" onclick="mostrarSeccion('usuarios')">
          <span class="icon">👥</span>
          <span>Usuarios</span>
        </a>
        <?php endif; ?>
        <a href="#configuracion" class="nav-item" onclick="mostrarSeccion('configuracion')">
          <span class="icon">⚙️</span>
          <span>Configuración</span>
        </a>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info">
          <span>👤 <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
        </div>
        <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="content-header">
        <h1 id="sectionTitle">Dashboard</h1>
        <div class="header-actions">
          <button class="btn-view-site" onclick="window.open('../index.php', '_blank')">Ver Sitio</button>
        </div>
      </header>
      <!-- Dashboard Section -->
      <section id="dashboard" class="section active">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">🚗</div>
            <div class="stat-info">
              <h3><?php echo $totalVehiculos; ?></h3>
              <p>Vehículos</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">🔧</div>
            <div class="stat-info">
              <h3><?php echo $totalServicios; ?></h3>
              <p>Servicios</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">🛒</div>
            <div class="stat-info">
              <h3><?php echo $totalProductos; ?></h3>
              <p>Productos</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-info">
              <h3><?php echo $citasPendientes; ?></h3>
              <p>Citas Pendientes</p>
            </div>
          </div>
        </div>

        <div class="recent-activity">
          <h2>Actividad Reciente</h2>
          <div class="activity-list">
            <?php 
            $actividadReciente = [];
            
            foreach (array_slice(array_reverse($citas), 0, 3) as $cita) {
              $actividadReciente[] = [
                'tipo' => 'cita',
                'fecha' => strtotime($cita['fecha_solicitud'] ?? 'now'),
                'html' => '<div class="activity-item">
                  <div class="activity-icon">📅</div>
                  <div class="activity-content">
                    <p><strong>' . htmlspecialchars($cita['nombre']) . '</strong> solicitó cita para <strong>' . htmlspecialchars($cita['servicio_nombre']) . '</strong></p>
                    <span class="activity-time">' . date('d/m/Y H:i', strtotime($cita['fecha_solicitud'] ?? 'now')) . '</span>
                  </div>
                  <span class="status-badge ' . ($cita['estado'] ?? 'pendiente') . '">' . ucfirst($cita['estado'] ?? 'pendiente') . '</span>
                </div>'
              ];
            }
            
            foreach (array_slice(array_reverse($solicitudes), 0, 3) as $solicitud) {
              $actividadReciente[] = [
                'tipo' => 'solicitud',
                'fecha' => strtotime($solicitud['fecha_solicitud'] ?? 'now'),
                'html' => '<div class="activity-item">
                  <div class="activity-icon">📧</div>
                  <div class="activity-content">
                    <p><strong>' . htmlspecialchars($solicitud['nombre']) . '</strong> envió una solicitud</p>
                    <span class="activity-time">' . date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'] ?? 'now')) . '</span>
                  </div>
                  <span class="status-badge ' . ($solicitud['estado'] ?? 'pendiente') . '">' . ucfirst($solicitud['estado'] ?? 'pendiente') . '</span>
                </div>'
              ];
            }
            
            usort($actividadReciente, function($a, $b) {
              return $b['fecha'] - $a['fecha'];
            });
            
            $actividadReciente = array_slice($actividadReciente, 0, 5);
            
            foreach ($actividadReciente as $actividad) {
              echo $actividad['html'];
            }
            
            if (empty($actividadReciente)) {
              echo '<p class="no-data">No hay actividad reciente</p>';
            }
            ?>
          </div>
        </div>
      </section>

      <!-- SECCIÓN: IMÁGENES HOME -->
      <section id="imagenes" class="section">
        <div class="section-header">
          <h2>Editar Imágenes de Inicio</h2>
          <p style="color: rgba(255,255,255,0.6); margin-top: 10px;">Cambia las imágenes principales de la página de inicio</p>
        </div>

        <div class="imagenes-grid">
          <!-- Sección 1: Vehículos -->
          <div class="imagen-card">
            <h3>🚗 Sección 1: Vehículos</h3>
            
            <form id="formImagen1" class="imagen-form" onsubmit="event.preventDefault();">
              <div class="form-group">
                <label>Título</label>
                <input type="text" id="titulo1" value="Vehículos" class="form-control">
              </div>

              <div class="form-group">
                <label>Descripción</label>
                <textarea id="descripcion1" rows="2" class="form-control">Descubre nuestra exclusiva selección de vehículos premium. Calidad, estilo y potencia en cada modelo.</textarea>
              </div>

              <div class="form-group">
                <label>Imagen</label>
                <input type="file" id="imagen1" accept="image/*" onchange="previewImagen('imagen1', 'preview1')" class="form-control">
                <p class="helper-text">Formatos: JPG, PNG, WebP, GIF. Máximo 5MB</p>
              </div>

              <div class="form-group">
                <label>Vista Previa Actual</label>
                <div class="preview-container">
                  <img id="preview1" alt="Preview" style="display: none;">
                </div>
              </div>

              <div class="form-group">
                <label>Enlace de Destino</label>
                <input type="text" id="enlace1" value="vehiculos/catalogo.php" class="form-control">
              </div>

              <button type="button" class="btn-primary" onclick="guardarImagen(1)">💾 Guardar Cambios</button>
            </form>
          </div>

          <!-- Sección 2: Servicios -->
          <div class="imagen-card">
            <h3>🔧 Sección 2: Servicios</h3>
            
            <form id="formImagen2" class="imagen-form" onsubmit="event.preventDefault();">
              <div class="form-group">
                <label>Título</label>
                <input type="text" id="titulo2" value="Servicios" class="form-control">
              </div>

              <div class="form-group">
                <label>Descripción</label>
                <textarea id="descripcion2" rows="2" class="form-control">Taller especializado, mantenimiento, accesorios y todo lo que tu vehículo necesita.</textarea>
              </div>

              <div class="form-group">
                <label>Imagen</label>
                <input type="file" id="imagen2" accept="image/*" onchange="previewImagen('imagen2', 'preview2')" class="form-control">
                <p class="helper-text">Formatos: JPG, PNG, WebP, GIF. Máximo 5MB</p>
              </div>

              <div class="form-group">
                <label>Vista Previa Actual</label>
                <div class="preview-container">
                  <img id="preview2" alt="Preview" style="display: none;">
                </div>
              </div>

              <div class="form-group">
                <label>Enlace de Destino</label>
                <input type="text" id="enlace2" value="servicios/servicios.php" class="form-control">
              </div>

              <button type="button" class="btn-primary" onclick="guardarImagen(2)">💾 Guardar Cambios</button>
            </form>
          </div>
        </div>

        <div id="mensajeImagenes" class="mensaje" style="display: none;"></div>
      </section>

      <!-- Vehículos Section -->
      <section id="vehiculos" class="section">
        <div class="section-header">
          <h2>Gestión de Vehículos</h2>
          <button class="btn-primary" onclick="abrirFormularioVehiculo()">+ Nuevo Vehículo</button>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Año</th>
                <th>Precio</th>
                <th>Tipo</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="vehiculosTable">
              <?php foreach ($vehiculos as $vehiculo): ?>
                <tr>
                  <td><?php echo $vehiculo['id']; ?></td>
                  <td>
                    <img src="<?php echo htmlspecialchars($vehiculo['imagenes'][0] ?? 'https://via.placeholder.com/100'); ?>" 
                         alt="Vehículo" class="table-img">
                  </td>
                  <td><?php echo htmlspecialchars($vehiculo['marca']); ?></td>
                  <td><?php echo htmlspecialchars($vehiculo['modelo']); ?></td>
                  <td><?php echo $vehiculo['anio']; ?></td>
                  <td>$<?php echo number_format($vehiculo['precio']); ?></td>
                  <td><span class="tipo-badge"><?php echo htmlspecialchars($vehiculo['tipo']); ?></span></td>
                  <td>
                    <button class="btn-edit" onclick='editarVehiculo(<?php echo json_encode($vehiculo); ?>)'>✏️</button>
                    <button class="btn-delete" onclick="eliminarVehiculo(<?php echo $vehiculo['id']; ?>)">🗑️</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Servicios Section -->
      <section id="servicios" class="section">
        <div class="section-header">
          <h2>Gestión de Servicios</h2>
          <button class="btn-primary" onclick="abrirFormularioServicio()">+ Nuevo Servicio</button>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Duración</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="serviciosTable">
              <?php foreach ($servicios as $servicio): ?>
                <tr>
                  <td><?php echo $servicio['id']; ?></td>
                  <td>
                    <img src="<?php echo htmlspecialchars($servicio['imagen'] ?? 'https://via.placeholder.com/100'); ?>" 
                         alt="Servicio" class="table-img">
                  </td>
                  <td><?php echo htmlspecialchars($servicio['nombre']); ?></td>
                  <td><span class="categoria-badge"><?php echo htmlspecialchars($servicio['categoria'] ?? 'General'); ?></span></td>
                  <td>$<?php echo number_format($servicio['precio']); ?></td>
                  <td><?php echo htmlspecialchars($servicio['duracion'] ?? 'N/A'); ?></td>
                  <td>
                    <button class="btn-edit" onclick='editarServicio(<?php echo json_encode($servicio); ?>)'>✏️</button>
                    <button class="btn-delete" onclick="eliminarServicio(<?php echo $servicio['id']; ?>)">🗑️</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Productos Section -->
      <section id="productos" class="section">
        <div class="section-header">
          <h2>Gestión de Productos (Repuestos)</h2>
          <button class="btn-primary" onclick="abrirFormularioProducto()">+ Nuevo Producto</button>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Marca</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="productosTable">
              <?php foreach ($productos as $producto): ?>
                <tr>
                  <td><?php echo $producto['id']; ?></td>
                  <td>
                    <img src="<?php echo htmlspecialchars($producto['imagen'] ?? 'https://via.placeholder.com/100'); ?>" 
                         alt="Producto" class="table-img">
                  </td>
                  <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                  <td><span class="categoria-badge"><?php echo htmlspecialchars($producto['categoria'] ?? 'General'); ?></span></td>
                  <td>$<?php echo number_format($producto['precio']); ?></td>
                  <td><?php echo $producto['stock'] ?? 0; ?></td>
                  <td><?php echo htmlspecialchars($producto['marca'] ?? 'N/A'); ?></td>
                  <td>
                    <button class="btn-edit" onclick='editarProducto(<?php echo json_encode($producto); ?>)'>✏️</button>
                    <button class="btn-delete" onclick="eliminarProducto(<?php echo $producto['id']; ?>)">🗑️</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
      <!-- Citas Section -->
      <section id="citas" class="section">
        <div class="section-header">
          <h2>Gestión de Citas</h2>
          <select id="filtroCitas" onchange="filtrarCitas()" class="filtro-select">
            <option value="todas">Todas</option>
            <option value="pendiente">Pendientes</option>
            <option value="confirmada">Confirmadas</option>
            <option value="completada">Completadas</option>
            <option value="cancelada">Canceladas</option>
          </select>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Servicio</th>
                <th>Sucursal</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="citasTable">
              <?php foreach ($citas as $cita): ?>
                <?php 
                  // Obtener nombre de sucursal
                  $configData = file_exists('../data/configuracion.json') 
                      ? json_decode(file_get_contents('../data/configuracion.json'), true) 
                      : [];
                  $sucursalNombre = 'N/A';
                  if (!empty($cita['sucursal']) && isset($configData['sucursales'][$cita['sucursal']])) {
                      $sucursalNombre = $configData['sucursales'][$cita['sucursal']]['nombre'] ?? 'N/A';
                  }
                ?>
                <tr data-estado="<?php echo $cita['estado'] ?? 'pendiente'; ?>">
                  <td><?php echo $cita['id']; ?></td>
                  <td><?php echo htmlspecialchars($cita['nombre']); ?></td>
                  <td><?php echo htmlspecialchars($cita['servicio_nombre']); ?></td>
                  <td><?php echo htmlspecialchars($sucursalNombre); ?></td>
                  <td><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></td>
                  <td><?php echo htmlspecialchars($cita['hora']); ?></td>
                  <td><?php echo htmlspecialchars($cita['telefono']); ?></td>
                  <td>
                    <select class="estado-select" onchange="cambiarEstadoCita(<?php echo $cita['id']; ?>, this.value)">
                      <option value="pendiente" <?php echo ($cita['estado'] ?? 'pendiente') === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                      <option value="confirmada" <?php echo ($cita['estado'] ?? '') === 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                      <option value="completada" <?php echo ($cita['estado'] ?? '') === 'completada' ? 'selected' : ''; ?>>Completada</option>
                      <option value="cancelada" <?php echo ($cita['estado'] ?? '') === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                    </select>
                  </td>
                  <td>
                    <button class="btn-view" onclick='verDetalleCita(<?php echo json_encode($cita); ?>)'>👁️</button>
                    <button class="btn-delete" onclick="eliminarCita(<?php echo $cita['id']; ?>)">🗑️</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Solicitudes Section -->
      <section id="solicitudes" class="section">
        <div class="section-header">
          <h2>Gestión de Solicitudes</h2>
          <select id="filtroSolicitudes" onchange="filtrarSolicitudes()" class="filtro-select">
            <option value="todas">Todas</option>
            <option value="pendiente">Pendientes</option>
            <option value="contactada">Contactadas</option>
            <option value="completada">Completadas</option>
          </select>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Referencia</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="solicitudesTable">
              <?php foreach ($solicitudes as $solicitud): ?>
                <tr data-estado="<?php echo $solicitud['estado'] ?? 'pendiente'; ?>">
                  <td><?php echo $solicitud['id']; ?></td>
                  <td><span class="tipo-badge"><?php echo ucfirst($solicitud['tipo'] ?? 'General'); ?></span></td>
                  <td><?php echo htmlspecialchars($solicitud['nombre']); ?></td>
                  <td><?php echo htmlspecialchars($solicitud['telefono']); ?></td>
                  <td><?php echo htmlspecialchars($solicitud['correo']); ?></td>
                  <td><?php echo htmlspecialchars($solicitud['vehiculo_nombre'] ?? 'N/A'); ?></td>
                  <td><?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?></td>
                  <td>
                    <select class="estado-select" onchange="cambiarEstadoSolicitud(<?php echo $solicitud['id']; ?>, this.value)">
                      <option value="pendiente" <?php echo ($solicitud['estado'] ?? 'pendiente') === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                      <option value="contactada" <?php echo ($solicitud['estado'] ?? '') === 'contactada' ? 'selected' : ''; ?>>Contactada</option>
                      <option value="completada" <?php echo ($solicitud['estado'] ?? '') === 'completada' ? 'selected' : ''; ?>>Completada</option>
                    </select>
                  </td>
                  <td>
                    <button class="btn-view" onclick='verDetalleSolicitud(<?php echo json_encode($solicitud); ?>)'>👁️</button>
                    <button class="btn-delete" onclick="eliminarSolicitud(<?php echo $solicitud['id']; ?>)">🗑️</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Logs Section -->
      <section id="logs" class="section">
        <div class="section-header">
          <h2>Registro de Actividad (Logs)</h2>
          <div>
            <select id="filtroTipoLog" onchange="cargarLogs()" class="filtro-select">
              <option value="">Todos los tipos</option>
              <option value="cita">Citas</option>
              <option value="solicitud">Solicitudes</option>
              <option value="vehiculo">Vehículos</option>
              <option value="servicio">Servicios</option>
              <option value="producto">Productos</option>
              <option value="usuario">Usuarios</option>
              <option value="sistema">Sistema</option>
            </select>
            <button class="btn-secondary" onclick="cargarLogs()">🔄 Actualizar</button>
          </div>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Fecha/Hora</th>
                <th>Tipo</th>
                <th>Acción</th>
                <th>Usuario</th>
                <th>Detalles</th>
                <th>IP</th>
              </tr>
            </thead>
            <tbody id="logsTable">
              <tr>
                <td colspan="6" class="loading">Cargando logs...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Usuarios Section (Solo Super Admin) -->
      <?php if ($_SESSION['admin_rol'] === 'super_admin'): ?>
      <section id="usuarios" class="section">
        <div class="section-header">
          <h2>Gestión de Usuarios</h2>
          <button class="btn-primary" onclick="abrirFormularioUsuario()">+ Nuevo Usuario</button>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Último Acceso</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="usuariosTable">
              <tr>
                <td colspan="8" class="loading">Cargando usuarios...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
      <?php endif; ?>

      <!-- Configuración Section -->
      <section id="configuracion" class="section">
        <h2>Configuración del Sistema</h2>
        
        <div class="config-tabs">
          <button class="tab-btn active" onclick="cambiarTab('general')">General</button>
          <button class="tab-btn" onclick="cambiarTab('oauth')">OAuth2</button>
          <button class="tab-btn" onclick="cambiarTab('correo')">SMTP</button>
          <button class="tab-btn" onclick="cambiarTab('sucursales')">Sucursales</button>
        </div>

        <!-- TAB: GENERAL -->
        <div id="tab-general" class="tab-content active">
          <div class="config-card">
            <h3>📞 Información de Contacto</h3>
            <form id="formConfigGeneral">
              <div class="form-group">
                <label>Nombre del Negocio</label>
                <input type="text" id="nombreNegocio" value="Autostok" class="form-control" required>
              </div>
              
              <div class="form-row">
                <div class="form-group">
                  <label>WhatsApp Vehículos</label>
                  <input type="tel" id="telefonoWhatsappVehiculos" placeholder="+57 300 123 4567" class="form-control">
                  <p class="helper-text">Número para consultas sobre vehículos</p>
                </div>
                <div class="form-group">
                  <label>WhatsApp Servicios/Citas</label>
                  <input type="tel" id="telefonoWhatsappServicios" placeholder="+57 300 765 4321" class="form-control">
                  <p class="helper-text">Número para notificaciones de citas y servicios</p>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>WhatsApp Almacén/Repuestos</label>
                  <input type="tel" id="telefonoWhatsappAlmacen" placeholder="+57 300 555 1234" class="form-control">
                  <p class="helper-text">Número para solicitudes de productos</p>
                </div>
                <div class="form-group">
                  <label>Correo Call Center</label>
                  <input type="email" id="correoCallCenter" placeholder="callcenter@autostok.com" class="form-control">
                  <p class="helper-text">Recibirá notificaciones de citas y solicitudes</p>
                </div>
              </div>

              <div class="form-group">
                <label>Correo General</label>
                <input type="email" id="correoNegocio" placeholder="contacto@autostok.com" class="form-control">
              </div>

              <button type="submit" class="btn-primary">💾 Guardar Configuración General</button>
            </form>
          </div>
        </div>

      <!-- TAB: OAUTH2 (MICROSOFT GRAPH API) -->
      <div id="tab-oauth" class="tab-content">
        <div class="config-card">
          <h3>🔐 Configuración OAuth2 - Microsoft Graph API</h3>
          <p style="color: rgba(255,255,255,0.6); margin-bottom: 20px;">
            Configuración moderna y segura para envío de correos con Office365/Microsoft 365 usando OAuth2.
          </p>
          
          <div style="background: rgba(0,255,0,0.1); border: 2px solid rgba(0,255,0,0.3); padding: 15px; border-radius: 8px; margin-bottom: 25px;">
            <p style="margin: 0; color: #0f0; font-weight: 600;">
              ✅ Este método es el recomendado por Microsoft y no requiere habilitar SMTP básico.
            </p>
          </div>
          
          <form id="formConfigOAuth" onsubmit="event.preventDefault();">
            <div class="form-group">
              <label>Tenant ID * <span style="color: rgba(255,255,255,0.5); font-weight: normal;">(ID del inquilino de Azure AD)</span></label>
              <input type="text" id="oauthTenantId" placeholder="cbf2f2a0-3897-45a1-aac7-4830d5303736" class="form-control" required>
              <p class="helper-text">ID del tenant de tu organización en Azure AD / Microsoft 365</p>
            </div>
            
            <div class="form-group">
              <label>Client ID * <span style="color: rgba(255,255,255,0.5); font-weight: normal;">(Application ID)</span></label>
              <input type="text" id="oauthClientId" placeholder="fb6af37f-be7c-49e8-a3f0-90573eb00773" class="form-control" required>
              <p class="helper-text">ID de la aplicación registrada en Azure AD</p>
            </div>
            
            <div class="form-group">
              <label>Client Secret * <span style="color: rgba(255,255,255,0.5); font-weight: normal;">(Secreto de cliente)</span></label>
              <input type="password" id="oauthClientSecret" placeholder="Ingrese el client secret" class="form-control">
              <p class="helper-text">Secreto de la aplicación (se genera en Azure AD)</p>
            </div>
            
            <div class="form-group">
              <label>Email del Remitente *</label>
              <input type="email" id="oauthFromEmail" placeholder="noresponder@autostok.com.co" class="form-control" required>
              <p class="helper-text">Dirección de correo que enviará las notificaciones</p>
            </div>
            
            <div class="form-group">
              <label>Nombre del Remitente</label>
              <input type="text" id="oauthFromName" placeholder="Auto Stok" class="form-control">
              <p class="helper-text">Nombre que aparecerá como remitente</p>
            </div>
            
            <div class="form-group">
              <label style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" id="oauthEnabled" style="width: auto;">
                Activar envío de correos con OAuth2
              </label>
              <p class="helper-text">Desactiva si no quieres usar este método</p>
            </div>
            
            <div style="display: flex; gap: 15px;">
              <button type="submit" class="btn-primary" style="flex: 1;">💾 Guardar Configuración OAuth2</button>
              <button type="button" class="btn-secondary" onclick="probarCorreoOAuth()" style="flex: 1;">
                📨 Enviar Correo de Prueba
              </button>
            </div>
          </form>
          
          <div id="mensajeOAuth" style="display: none; margin-top: 20px; padding: 15px; border-radius: 8px; font-weight: 600; text-align: center;"></div>
          
          <!-- INSTRUCCIONES -->
          <div style="margin-top: 40px; padding: 25px; background: rgba(255,215,0,0.05); border-radius: 10px; border: 1px solid rgba(255,215,0,0.2);">
            <h4 style="color: #FFD700; margin-bottom: 20px;">📚 ¿Cómo obtener las credenciales OAuth2?</h4>
            
            <ol style="color: rgba(255,255,255,0.8); line-height: 2;">
              <li><strong>Ve al Portal de Azure:</strong> <a href="https://portal.azure.com" target="_blank" style="color: #FFD700;">portal.azure.com</a></li>
              <li><strong>Azure Active Directory</strong> → <strong>App registrations</strong> → <strong>New registration</strong></li>
              <li>Nombre: <code style="background: #333; padding: 3px 8px; border-radius: 3px;">Auto Stok Mail Sender</code></li>
              <li>Supported account types: <strong>Accounts in this organizational directory only</strong></li>
              <li>Haz clic en <strong>Register</strong></li>
              <li>Copia el <strong>Application (client) ID</strong> → Este es tu <strong>Client ID</strong></li>
              <li>Copia el <strong>Directory (tenant) ID</strong> → Este es tu <strong>Tenant ID</strong></li>
              <li>Ve a <strong>Certificates & secrets</strong> → <strong>New client secret</strong></li>
              <li>Description: <code style="background: #333; padding: 3px 8px; border-radius: 3px;">Auto Stok Secret</code></li>
              <li>Expires: <strong>24 months</strong> (recomendado)</li>
              <li>Copia el <strong>Value</strong> → Este es tu <strong>Client Secret</strong> (⚠️ solo se muestra una vez)</li>
              <li>Ve a <strong>API permissions</strong> → <strong>Add a permission</strong></li>
              <li>Selecciona <strong>Microsoft Graph</strong> → <strong>Application permissions</strong></li>
              <li>Busca y agrega: <strong>Mail.Send</strong></li>
              <li>Haz clic en <strong>Grant admin consent</strong> ✅</li>
            </ol>
            
            <div style="margin-top: 20px; padding: 15px; background: rgba(0,255,0,0.1); border-left: 4px solid #0f0; border-radius: 5px;">
              <p style="margin: 0; color: #0f0; font-weight: 600;">
                ✅ Una vez configurado, funcionará inmediatamente sin necesidad de habilitar SMTP básico.
              </p>
            </div>
          </div>
        </div>
      </div>

        <!-- TAB: CORREO ELECTRÓNICO -->
        <div id="tab-correo" class="tab-content">
          <div class="config-card">
            <h3>📧 Configuración de Correo Electrónico (SMTP)</h3>
            <p style="color: rgba(255,255,255,0.6); margin-bottom: 20px;">
              Configura las credenciales SMTP para enviar notificaciones automáticas por correo electrónico.
            </p>
            
            <form id="formConfigCorreo" onsubmit="event.preventDefault();">
              <div class="form-group">
                <label>Servidor SMTP *</label>
                <input type="text" id="smtpHost" placeholder="smtp.gmail.com" class="form-control" required>
                <p class="helper-text">Ejemplo: smtp.gmail.com, smtp.office365.com, smtp.hostinger.com</p>
              </div>
              
              <div class="form-row">
                <div class="form-group">
                  <label>Puerto SMTP *</label>
                  <input type="number" id="smtpPort" placeholder="587" class="form-control" required>
                  <p class="helper-text">587 (TLS) o 465 (SSL)</p>
                </div>
                <div class="form-group">
                  <label>Cifrado *</label>
                  <select id="smtpEncryption" class="form-control" required>
                    <option value="tls">TLS (Puerto 587)</option>
                    <option value="ssl">SSL (Puerto 465)</option>
                  </select>
                </div>
              </div>
              
              <div class="form-group">
                <label>Usuario SMTP (Correo) *</label>
                <input type="email" id="smtpUsername" placeholder="tu-correo@gmail.com" class="form-control" required>
                <p class="helper-text">Dirección de correo electrónico que enviará las notificaciones</p>
              </div>
              
              <div class="form-group">
                <label>Contraseña SMTP *</label>
                <input type="password" id="smtpPassword" placeholder="Ingrese contraseña" class="form-control">
                <p class="helper-text">
                  <strong>⚠️ Gmail:</strong> Usa una "Contraseña de aplicación" (no tu contraseña normal)<br>
                  <a href="https://support.google.com/accounts/answer/185833" target="_blank" style="color: #FFD700; text-decoration: underline;">
                    ¿Cómo generar una contraseña de aplicación en Gmail?
                  </a>
                </p>
              </div>
              
              <div class="form-group">
                <label>Nombre del remitente</label>
                <input type="text" id="smtpFromName" placeholder="Auto Stok" class="form-control">
                <p class="helper-text">Nombre que aparecerá como remitente en los correos</p>
              </div>
              
              <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px;">
                  <input type="checkbox" id="smtpEnabled" style="width: auto;">
                  Activar envío de correos electrónicos
                </label>
                <p class="helper-text">Desactiva si no quieres que se envíen notificaciones por correo</p>
              </div>
              
              <div style="display: flex; gap: 15px;">
                <button type="submit" class="btn-primary" style="flex: 1;">💾 Guardar Configuración</button>
                <button type="button" class="btn-secondary" onclick="probarCorreo()" style="flex: 1;">
                  📨 Enviar Correo de Prueba
                </button>
              </div>
            </form>
            
            <div id="mensajeCorreo" style="display: none; margin-top: 20px; padding: 15px; border-radius: 8px; font-weight: 600; text-align: center;"></div>
            
            <div style="margin-top: 30px; padding: 20px; background: rgba(255,215,0,0.05); border-radius: 10px; border: 1px solid rgba(255,215,0,0.2);">
              <h4 style="color: #FFD700; margin-bottom: 15px;">💡 Instrucciones para Gmail</h4>
              <ol style="color: rgba(255,255,255,0.8); line-height: 1.8; padding-left: 20px;">
                <li>Inicia sesión en tu cuenta de Gmail</li>
                <li>Ve a: <a href="https://myaccount.google.com/security" target="_blank" style="color: #FFD700;">Cuenta de Google → Seguridad</a></li>
                <li>Activa la "Verificación en dos pasos" si no la tienes</li>
                <li>Busca "Contraseñas de aplicaciones"</li>
                <li>Selecciona "Correo" y "Otro dispositivo personalizado"</li>
                <li>Escribe "Auto Stok Panel" y genera la contraseña</li>
                <li>Copia esa contraseña de 16 caracteres y pégala aquí</li>
              </ol>
              <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-top: 15px;">
                <strong>Nota:</strong> Para otros proveedores (Outlook, Hostinger, etc.), consulta su documentación sobre SMTP.
              </p>
            </div>
          </div>
        </div>

        <!-- TAB: SUCURSALES -->
        <div id="tab-sucursales" class="tab-content">
          <div class="sucursales-grid">
            <!-- Sucursal 1 (Norte) -->
            <div class="config-card">
              <h3>📍 Sucursal Norte</h3>
              <form id="formSucursal1">
                <div class="form-group">
                  <label>Nombre de la Sucursal *</label>
                  <input type="text" id="nombreSucursal1" placeholder="Autostok Norte" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Dirección *</label>
                  <input type="text" id="direccionSucursal1" placeholder="Calle 123 #45-67" class="form-control" required>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Teléfono *</label>
                    <input type="tel" id="telefonoSucursal1" placeholder="+57 300 123 4567" class="form-control" required>
                  </div>
                  <div class="form-group">
                    <label>WhatsApp *</label>
                    <input type="tel" id="whatsappSucursal1" placeholder="+57 300 123 4567" class="form-control" required>
                    <p class="helper-text">Recibirá notificaciones de citas</p>
                  </div>
                </div>
                <div class="form-group">
                  <label>Correo Electrónico *</label>
                  <input type="email" id="correoSucursal1" placeholder="norte@autostok.com" class="form-control" required>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Horario Lunes a Viernes</label>
                    <input type="text" id="horarioSemanaSucursal1" placeholder="8:00 AM - 6:00 PM" class="form-control">
                  </div>
                  <div class="form-group">
                    <label>Horario Sábados</label>
                    <input type="text" id="horarioSabadoSucursal1" placeholder="9:00 AM - 2:00 PM" class="form-control">
                  </div>
                </div>
                <div class="form-group">
                  <label>URL de Google Maps (iframe embed)</label>
                  <textarea id="mapaSucursal1" rows="3" placeholder="<iframe src=&quot;https://www.google.com/maps/embed?...&quot;></iframe>" class="form-control"></textarea>
                  <p class="helper-text">Pega el código de inserción completo de Google Maps. <a href="https://www.google.com/maps" target="_blank">¿Cómo obtenerlo?</a></p>
                </div>
                <button type="submit" class="btn-primary">💾 Guardar Sucursal Norte</button>
              </form>
            </div>

            <!-- Sucursal 2 (Sur) -->
            <div class="config-card">
              <h3>📍 Sucursal Sur</h3>
              <form id="formSucursal2">
                <div class="form-group">
                  <label>Nombre de la Sucursal *</label>
                  <input type="text" id="nombreSucursal2" placeholder="Autostok Sur" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Dirección *</label>
                  <input type="text" id="direccionSucursal2" placeholder="Carrera 78 #90-12" class="form-control" required>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Teléfono *</label>
                    <input type="tel" id="telefonoSucursal2" placeholder="+57 300 765 4321" class="form-control" required>
                  </div>
                  <div class="form-group">
                    <label>WhatsApp *</label>
                    <input type="tel" id="whatsappSucursal2" placeholder="+57 300 765 4321" class="form-control" required>
                    <p class="helper-text">Recibirá notificaciones de citas</p>
                  </div>
                </div>
                <div class="form-group">
                  <label>Correo Electrónico *</label>
                  <input type="email" id="correoSucursal2" placeholder="sur@autostok.com" class="form-control" required>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Horario Lunes a Viernes</label>
                    <input type="text" id="horarioSemanaSucursal2" placeholder="8:00 AM - 6:00 PM" class="form-control">
                  </div>
                  <div class="form-group">
                    <label>Horario Sábados</label>
                    <input type="text" id="horarioSabadoSucursal2" placeholder="9:00 AM - 2:00 PM" class="form-control">
                  </div>
                </div>
                <div class="form-group">
                  <label>URL de Google Maps (iframe embed)</label>
                  <textarea id="mapaSucursal2" rows="3" placeholder="<iframe src=&quot;https://www.google.com/maps/embed?...&quot;></iframe>" class="form-control"></textarea>
                  <p class="helper-text">Pega el código de inserción completo de Google Maps. <a href="https://www.google.com/maps" target="_blank">¿Cómo obtenerlo?</a></p>
                </div>
                <button type="submit" class="btn-primary">💾 Guardar Sucursal Sur</button>
              </form>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
  <!-- Modal Formulario Vehículo -->
<div id="modalVehiculo" class="modal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 id="tituloModalVehiculo">Nuevo Vehículo</h3>
      <button class="btn-close" onclick="cerrarModalVehiculo()">✕</button>
    </div>
    <form id="formVehiculo">
      <input type="hidden" id="vehiculoId" name="id">
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label>Marca *</label>
            <input type="text" id="marca" name="marca" required class="form-control">
          </div>
          <div class="form-group">
            <label>Modelo *</label>
            <input type="text" id="modelo" name="modelo" required class="form-control">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Año *</label>
            <input type="number" id="anio" name="anio" required min="1900" max="2030" class="form-control">
          </div>
          <div class="form-group">
            <label>Precio *</label>
            <input type="number" id="precio" name="precio" required class="form-control">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Kilometraje</label>
            <input type="number" id="kilometraje" name="kilometraje" class="form-control">
          </div>
          <div class="form-group">
            <label>Tipo *</label>
            <select id="tipo" name="tipo" required class="form-control">
              <option value="">Seleccionar</option>
              <option value="sedan">Sedán</option>
              <option value="suv">SUV</option>
              <option value="pickup">Pickup</option>
              <option value="deportivo">Deportivo</option>
              <option value="hatchback">Hatchback</option>
              <option value="coupe">Coupé</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Transmisión</label>
            <select id="transmision" name="transmision" class="form-control">
              <option value="Manual">Manual</option>
              <option value="Automática">Automática</option>
            </select>
          </div>
          <div class="form-group">
            <label>Combustible</label>
            <select id="combustible" name="combustible" class="form-control">
              <option value="Gasolina">Gasolina</option>
              <option value="Diesel">Diesel</option>
              <option value="Eléctrico">Eléctrico</option>
              <option value="Híbrido">Híbrido</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Color</label>
          <input type="text" id="color" name="color" class="form-control">
        </div>
        <div class="form-group">
          <label>Descripción</label>
          <textarea id="descripcion" name="descripcion" rows="4" class="form-control"></textarea>
        </div>
        
        <div class="form-group">
          <label>Imágenes del Vehículo</label>
          <div id="imagenesActualesVehiculo" style="margin-bottom: 15px;"></div>
          <input type="file" id="imagenesVehiculo" accept="image/*" multiple class="form-control">
          <p class="helper-text">Puedes seleccionar múltiples imágenes nuevas (máx 5MB cada una)</p>
          <div id="previewImagenesVehiculo" style="margin-top: 10px;"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="cerrarModalVehiculo()">Cancelar</button>
        <button type="submit" class="btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Formulario Servicio -->
<div id="modalServicio" class="modal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 id="tituloModalServicio">Nuevo Servicio</h3>
      <button class="btn-close" onclick="cerrarModalServicio()">✕</button>
    </div>
    <form id="formServicio">
      <input type="hidden" id="servicioId" name="id">
      <div class="modal-body">
        <div class="form-group">
          <label>Nombre del Servicio *</label>
          <input type="text" id="nombreServicio" name="nombre" required class="form-control">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Categoría *</label>
            <select id="categoriaServicio" name="categoria" required class="form-control">
              <option value="">Seleccionar</option>
              <option value="Mantenimiento">Mantenimiento</option>
              <option value="Reparación">Reparación</option>
              <option value="Lavado">Lavado</option>
              <option value="Accesorios">Accesorios</option>
              <option value="Diagnóstico">Diagnóstico</option>
              <option value="Pintura">Pintura</option>
            </select>
          </div>
          <div class="form-group">
            <label>Precio *</label>
            <input type="number" id="precioServicio" name="precio" required class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label>Duración</label>
          <input type="text" id="duracionServicio" name="duracion" placeholder="Ej: 2 horas" class="form-control">
        </div>
        <div class="form-group">
          <label>Descripción Corta</label>
          <textarea id="descripcionCorta" name="descripcion_corta" rows="2" class="form-control"></textarea>
        </div>
        <div class="form-group">
          <label>Descripción Completa</label>
          <textarea id="descripcionServicio" name="descripcion" rows="4" class="form-control"></textarea>
        </div>
        <div class="form-group">
          <label>Características (una por línea)</label>
          <textarea id="caracteristicas" name="caracteristicas" rows="3" placeholder="Característica 1&#10;Característica 2" class="form-control"></textarea>
        </div>
        
        <div class="form-group">
          <label>Imagen del Servicio</label>
          <div id="imagenActualServicio" style="margin-bottom: 15px;"></div>
          <input type="file" id="imagenServicioFile" accept="image/*" class="form-control">
          <p class="helper-text">Formato: JPG, PNG, WEBP (máx 5MB)</p>
          <div style="margin-top: 10px;">
            <img id="previewImagenServicio" style="max-width: 200px; display: none; border-radius: 5px; border: 2px solid #FFD700;">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="cerrarModalServicio()">Cancelar</button>
        <button type="submit" class="btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Formulario Producto -->
<div id="modalProducto" class="modal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 id="tituloModalProducto">Nuevo Producto</h3>
      <button class="btn-close" onclick="cerrarModalProducto()">✕</button>
    </div>
    <form id="formProducto">
      <input type="hidden" id="productoId" name="id">
      <div class="modal-body">
        <div class="form-group">
          <label>Nombre del Producto *</label>
          <input type="text" id="nombreProducto" name="nombre" required class="form-control">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Categoría *</label>
            <select id="categoriaProducto" name="categoria" required class="form-control">
              <option value="">Seleccionar</option>
              <option value="Lubricantes">Lubricantes</option>
              <option value="Filtros">Filtros</option>
              <option value="Baterías">Baterías</option>
              <option value="Frenos">Frenos</option>
              <option value="Suspensión">Suspensión</option>
              <option value="Eléctricos">Eléctricos</option>
              <option value="Accesorios">Accesorios</option>
              <option value="Otros">Otros</option>
            </select>
          </div>
          <div class="form-group">
            <label>Precio *</label>
            <input type="number" id="precioProducto" name="precio" required class="form-control">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Stock *</label>
            <input type="number" id="stockProducto" name="stock" required min="0" class="form-control">
          </div>
          <div class="form-group">
            <label>Marca</label>
            <input type="text" id="marcaProducto" name="marca" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <label>Código del Producto</label>
          <input type="text" id="codigoProducto" name="codigo" class="form-control">
        </div>
        <div class="form-group">
          <label>Descripción</label>
          <textarea id="descripcionProducto" name="descripcion" rows="4" class="form-control"></textarea>
        </div>
        
        <div class="form-group">
          <label>Imagen del Producto</label>
          <div id="imagenActualProducto" style="margin-bottom: 15px;"></div>
          <input type="file" id="imagenProductoFile" accept="image/*" class="form-control">
          <p class="helper-text">Formato: JPG, PNG, WEBP (máx 5MB)</p>
          <div style="margin-top: 10px;">
            <img id="previewImagenProducto" style="max-width: 200px; display: none; border-radius: 5px; border: 2px solid #FFD700;">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="cerrarModalProducto()">Cancelar</button>
        <button type="submit" class="btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Formulario Usuario -->
<?php if ($_SESSION['admin_rol'] === 'super_admin'): ?>
<div id="modalUsuario" class="modal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 id="tituloModalUsuario">Nuevo Usuario</h3>
      <button class="btn-close" onclick="cerrarModalUsuario()">✕</button>
    </div>
    <form id="formUsuario">
      <input type="hidden" id="usuarioId" name="id">
      <div class="modal-body">
        <div class="form-group">
          <label>Nombre de Usuario *</label>
          <input type="text" id="username" name="username" required class="form-control">
        </div>
        <div class="form-group">
          <label>Contraseña *</label>
          <input type="password" id="passwordUsuario" name="password" class="form-control">
          <p class="helper-text" id="passwordHelper">Mínimo 6 caracteres</p>
        </div>
        <div class="form-group">
          <label>Nombre Completo *</label>
          <input type="text" id="nombreUsuario" name="nombre" required class="form-control">
        </div>
        <div class="form-group">
          <label>Email *</label>
          <input type="email" id="emailUsuario" name="email" required class="form-control">
        </div>
        <div class="form-group">
          <label>Rol *</label>
          <select id="rolUsuario" name="rol" required class="form-control">
            <option value="">Seleccionar rol</option>
            <option value="super_admin">Super Administrador</option>
            <option value="administrador">Administrador</option>
            <option value="ventas">Vendedor</option>
            <option value="taller">Taller</option>
            <option value="visualizador">Visualizador</option>
          </select>
          <p class="helper-text" id="rolDescripcion"></p>
        </div>
        <div class="form-group">
          <label style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" id="activoUsuario" name="activo" checked style="width: auto;">
            Usuario Activo
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="cerrarModalUsuario()">Cancelar</button>
        <button type="submit" class="btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
<style>
    /* Estilos para la nueva sección de imágenes */
    .imagenes-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 30px;
      margin-top: 30px;
    }

    .imagen-card {
      background: rgba(255,215,0,0.05);
      border: 2px solid rgba(255,215,0,0.3);
      border-radius: 15px;
      padding: 25px;
      transition: all 0.3s ease;
    }

    .imagen-card:hover {
      border-color: #FFD700;
      box-shadow: 0 10px 30px rgba(255,215,0,0.2);
    }

    .imagen-card h3 {
      color: #FFD700;
      margin-bottom: 20px;
      font-size: 1.3rem;
    }

    .imagen-form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .preview-container {
      min-height: 200px;
      background: rgba(0,0,0,0.3);
      border: 2px dashed rgba(255,215,0,0.3);
      border-radius: 8px;
      padding: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .preview-container img {
      max-width: 100%;
      max-height: 200px;
      border-radius: 5px;
    }

    .mensaje {
      padding: 15px;
      border-radius: 8px;
      margin-top: 20px;
      text-align: center;
      font-weight: 600;
      animation: slideIn 0.5s ease;
    }

    .mensaje.exito {
      background: rgba(0,255,0,0.1);
      border: 2px solid rgba(0,255,0,0.5);
      color: #0f0;
      display: block;
    }

    .mensaje.error {
      background: rgba(255,0,0,0.1);
      border: 2px solid rgba(255,0,0,0.5);
      color: #f00;
      display: block;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Estilos originales de configuración */
    .config-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 30px;
      border-bottom: 2px solid rgba(255, 215, 0, 0.3);
      padding-bottom: 0;
    }

    .tab-btn {
      padding: 12px 24px;
      background: transparent;
      border: none;
      color: rgba(255, 255, 255, 0.6);
      cursor: pointer;
      font-size: 1rem;
      font-weight: 600;
      transition: all 0.3s ease;
      border-bottom: 3px solid transparent;
      margin-bottom: -2px;
    }

    .tab-btn:hover {
      color: #FFD700;
    }

    .tab-btn.active {
      color: #FFD700;
      border-bottom-color: #FFD700;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .sucursales-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
      gap: 30px;
    }

    @media (max-width: 768px) {
      .sucursales-grid, .imagenes-grid {
        grid-template-columns: 1fr;
      }
    }

    /* Estilos para select de filtros */
    .filtro-select {
      padding: 10px 15px;
      background: rgba(0, 0, 0, 0.5);
      border: 2px solid rgba(255, 215, 0, 0.3);
      border-radius: 8px;
      color: #fff;
      font-size: 0.95rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      outline: none;
      min-width: 180px;
    }

    .filtro-select:hover {
      border-color: #FFD700;
      background: rgba(0, 0, 0, 0.7);
    }

    .filtro-select:focus {
      border-color: #FFD700;
      box-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
    }

    .filtro-select option {
      background: #1a1a1a;
      color: #fff;
      padding: 10px;
    }

    .filtro-select option:hover {
      background: rgba(255, 215, 0, 0.2);
    }

    /* Section header spacing */
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 15px;
    }

    .section-header > div {
      display: flex;
      gap: 10px;
      align-items: center;
    }
  </style>

  <!-- Incluir archivos JavaScript -->
  <script src="js/admin.js"></script>
  <script src="js/imagenes-home.js"></script>

<script>
// ==================== CONFIGURACIÓN SMTP ====================

async function cargarConfigSMTP() {
  try {
    const response = await fetch('api/config_smtp.php');
    const result = await response.json();
    
    console.log('Respuesta config SMTP:', result); // Debug
    
    if (result.success && result.smtp) {
      const smtp = result.smtp;
      document.getElementById('smtpHost').value = smtp.host || '';
      document.getElementById('smtpPort').value = smtp.port || 587;
      document.getElementById('smtpEncryption').value = smtp.encryption || 'tls';
      document.getElementById('smtpUsername').value = smtp.username || '';
      document.getElementById('smtpFromName').value = smtp.from_name || 'Auto Stok';
      document.getElementById('smtpEnabled').checked = smtp.enabled || false;
      
      if (smtp.has_password) {
        document.getElementById('smtpPassword').placeholder = '••••••••••••';
      }
    }
  } catch (error) {
    console.error('Error cargando configuración SMTP:', error);
  }
}

document.getElementById('formConfigCorreo')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const password = document.getElementById('smtpPassword').value;
  const placeholder = document.getElementById('smtpPassword').placeholder;
  
  const datos = {
    host: document.getElementById('smtpHost').value.trim(),
    port: document.getElementById('smtpPort').value,
    encryption: document.getElementById('smtpEncryption').value,
    username: document.getElementById('smtpUsername').value.trim(),
    from_name: document.getElementById('smtpFromName').value.trim(),
    enabled: document.getElementById('smtpEnabled').checked
  };
  
  if (password) {
    datos.password = password;
  } else if (placeholder === 'Ingrese contraseña') {
    mostrarMensajeCorreo('✗ Por favor ingrese la contraseña SMTP', 'error');
    return;
  }
  
  console.log('Enviando datos:', datos); // Debug
  
  try {
    const response = await fetch('api/config_smtp.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos)
    });
    
    const result = await response.json();
    console.log('Respuesta guardado:', result); // Debug
    
    if (result.success) {
      mostrarMensajeCorreo('✓ Configuración guardada correctamente', 'exito');
      document.getElementById('smtpPassword').value = '';
      document.getElementById('smtpPassword').placeholder = '••••••••••••';
    } else {
      mostrarMensajeCorreo('✗ Error: ' + result.message, 'error');
    }
  } catch (error) {
    console.error('Error completo:', error);
    mostrarMensajeCorreo('✗ Error al guardar: ' + error.message, 'error');
  }
});

async function probarCorreo() {
  const mensajeDiv = document.getElementById('mensajeCorreo');
  mensajeDiv.style.display = 'block';
  mensajeDiv.textContent = '📤 Enviando correo de prueba...';
  mensajeDiv.style.background = 'rgba(255,215,0,0.1)';
  mensajeDiv.style.border = '2px solid rgba(255,215,0,0.5)';
  mensajeDiv.style.color = '#FFD700';
  
  try {
    // CAMBIAR de diagnostico_correo.php a probar_correo.php
    const response = await fetch('api/probar_correo.php', {
      method: 'POST'
    });
    
    const result = await response.json();
    
    if (result.success) {
      mostrarMensajeCorreo('✓ ' + result.message, 'exito');
    } else {
      mostrarMensajeCorreo('✗ Error: ' + result.message, 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarMensajeCorreo('✗ Error al enviar correo de prueba', 'error');
  }
}

function mostrarMensajeCorreo(mensaje, tipo) {
  const mensajeDiv = document.getElementById('mensajeCorreo');
  mensajeDiv.style.display = 'block';
  mensajeDiv.textContent = mensaje;
  
  if (tipo === 'exito') {
    mensajeDiv.style.background = 'rgba(0,255,0,0.1)';
    mensajeDiv.style.border = '2px solid rgba(0,255,0,0.5)';
    mensajeDiv.style.color = '#0f0';
  } else {
    mensajeDiv.style.background = 'rgba(255,0,0,0.1)';
    mensajeDiv.style.border = '2px solid rgba(255,0,0,0.5)';
    mensajeDiv.style.color = '#f00';
  }
  
  setTimeout(() => {
    mensajeDiv.style.display = 'none';
  }, 8000);
}

const cambiarTabOriginal = window.cambiarTab;
window.cambiarTab = function(tab) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  
  const tabContent = document.getElementById(`tab-${tab}`);
  if (tabContent) {
    tabContent.classList.add('active');
  }
  
  if (event && event.target) {
    event.target.classList.add('active');
  }
  
  if (tab === 'correo') {
    setTimeout(() => cargarConfigSMTP(), 100);
  }
  
  if (cambiarTabOriginal && typeof cambiarTabOriginal === 'function') {
    cambiarTabOriginal(tab);
  }
};

window.cambiarEstadoSolicitud = async function(id, nuevoEstado) {
  if (!confirm('¿Confirmar cambio de estado?')) return;
  
  try {
    const response = await fetch('api/solicitudes.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, estado: nuevoEstado })
    });
    
    const result = await response.json();
    
    if (result.success) {
      if (nuevoEstado === 'completada') {
        alert('✓ Solicitud completada exitosamente.\n\nSi era una solicitud de producto, el stock se ha actualizado automáticamente.');
      } else {
        alert('✓ Estado actualizado correctamente');
      }
      
      setTimeout(() => location.reload(), 500);
    } else {
      alert('✗ Error: ' + result.message);
    }
  } catch (error) {
    console.error('Error:', error);
    alert('✗ Error al actualizar el estado');
  }
};

document.addEventListener('DOMContentLoaded', () => {
  const tabCorreo = document.getElementById('tab-correo');
  if (tabCorreo && tabCorreo.classList.contains('active')) {
    cargarConfigSMTP();
  }
});
</script>

<script>
// ==================== CONFIGURACIÓN OAUTH2 ====================

async function cargarConfigOAuth() {
  try {
    const response = await fetch('api/config_oauth.php');
    const result = await response.json();
    
    console.log('Config OAuth2 cargada:', result);
    
    if (result.success && result.oauth) {
      const oauth = result.oauth;
      document.getElementById('oauthTenantId').value = oauth.tenant_id || '';
      document.getElementById('oauthClientId').value = oauth.client_id || '';
      document.getElementById('oauthFromEmail').value = oauth.from_email || '';
      document.getElementById('oauthFromName').value = oauth.from_name || 'Auto Stok';
      document.getElementById('oauthEnabled').checked = oauth.enabled || false;
      
      if (oauth.has_client_secret) {
        document.getElementById('oauthClientSecret').placeholder = '••••••••••••••••••••';
      }
    }
  } catch (error) {
    console.error('Error cargando configuración OAuth2:', error);
  }
}

document.getElementById('formConfigOAuth')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const clientSecret = document.getElementById('oauthClientSecret').value;
  const placeholder = document.getElementById('oauthClientSecret').placeholder;
  
  const datos = {
    tenant_id: document.getElementById('oauthTenantId').value.trim(),
    client_id: document.getElementById('oauthClientId').value.trim(),
    from_email: document.getElementById('oauthFromEmail').value.trim(),
    from_name: document.getElementById('oauthFromName').value.trim(),
    enabled: document.getElementById('oauthEnabled').checked
  };
  
  if (clientSecret) {
    datos.client_secret = clientSecret;
  } else if (placeholder === 'Ingrese el client secret') {
    mostrarMensajeOAuth('✗ Por favor ingrese el Client Secret', 'error');
    return;
  }
  
  console.log('Guardando OAuth2:', datos);
  
  try {
    const response = await fetch('api/config_oauth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos)
    });
    
    const result = await response.json();
    console.log('Resultado:', result);
    
    if (result.success) {
      mostrarMensajeOAuth('✓ Configuración OAuth2 guardada correctamente', 'exito');
      document.getElementById('oauthClientSecret').value = '';
      document.getElementById('oauthClientSecret').placeholder = '••••••••••••••••••••';
    } else {
      mostrarMensajeOAuth('✗ Error: ' + result.message, 'error');
    }
  } catch (error) {
    console.error('Error completo:', error);
    mostrarMensajeOAuth('✗ Error al guardar: ' + error.message, 'error');
  }
});

async function probarCorreoOAuth() {
  const mensajeDiv = document.getElementById('mensajeOAuth');
  mensajeDiv.style.display = 'block';
  mensajeDiv.textContent = '📤 Enviando correo de prueba con OAuth2...';
  mensajeDiv.style.background = 'rgba(255,215,0,0.1)';
  mensajeDiv.style.border = '2px solid rgba(255,215,0,0.5)';
  mensajeDiv.style.color = '#FFD700';
  
  try {
    const response = await fetch('api/probar_correo_oauth.php', {
      method: 'POST'
    });
    
    const result = await response.json();
    
    if (result.success) {
      mostrarMensajeOAuth('✓ ' + result.message, 'exito');
    } else {
      mostrarMensajeOAuth('✗ Error: ' + result.message, 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarMensajeOAuth('✗ Error al enviar correo de prueba', 'error');
  }
}

function mostrarMensajeOAuth(mensaje, tipo) {
  const mensajeDiv = document.getElementById('mensajeOAuth');
  mensajeDiv.style.display = 'block';
  mensajeDiv.textContent = mensaje;
  
  if (tipo === 'exito') {
    mensajeDiv.style.background = 'rgba(0,255,0,0.1)';
    mensajeDiv.style.border = '2px solid rgba(0,255,0,0.5)';
    mensajeDiv.style.color = '#0f0';
  } else {
    mensajeDiv.style.background = 'rgba(255,0,0,0.1)';
    mensajeDiv.style.border = '2px solid rgba(255,0,0,0.5)';
    mensajeDiv.style.color = '#f00';
  }
  
  setTimeout(() => {
    mensajeDiv.style.display = 'none';
  }, 8000);
}
</script>
</body>
</html>