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
        <a href="#sucursales" class="nav-item" onclick="mostrarSeccion('sucursales')">
          <span class="icon">📍</span>
          <span>Sucursales</span>
        </a>
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

      <!-- Sucursales Section -->
      <section id="sucursales" class="section">
        <h2>Gestión de Sucursales</h2>
        
        <div class="config-cards">
          <!-- Sucursal 1 -->
          <div class="config-card">
            <h3>📍 Sucursal Norte</h3>
            <form id="formSucursal1">
              <div class="form-group">
                <label>Nombre de la Sucursal</label>
                <input type="text" id="nombreSucursal1" placeholder="Autostok Norte" class="form-control">
              </div>
              <div class="form-group">
                <label>Dirección</label>
                <input type="text" id="direccionSucursal1" placeholder="Calle 123 #45-67" class="form-control">
              </div>
              <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" id="telefonoSucursal1" placeholder="+57 300 123 4567" class="form-control">
              </div>
              <div class="form-group">
                <label>WhatsApp</label>
                <input type="tel" id="whatsappSucursal1" placeholder="+57 300 123 4567" class="form-control">
                <p class="helper-text">Número para recibir notificaciones de citas y servicios</p>
              </div>
              <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" id="correoSucursal1" placeholder="norte@autostok.com" class="form-control">
              </div>
              <div class="form-group">
                <label>Horario Lunes a Viernes</label>
                <input type="text" id="horarioSemanaSucursal1" placeholder="8:00 AM - 6:00 PM" class="form-control">
              </div>
              <div class="form-group">
                <label>Horario Sábados</label>
                <input type="text" id="horarioSabadoSucursal1" placeholder="9:00 AM - 2:00 PM" class="form-control">
              </div>
              <div class="form-group">
                <label>URL de Google Maps (opcional)</label>
                <input type="url" id="mapaSucursal1" placeholder="https://maps.google.com/..." class="form-control">
                <p class="helper-text">Link de ubicación en Google Maps</p>
              </div>
              <button type="submit" class="btn-primary">Guardar Sucursal Norte</button>
            </form>
          </div>

          <!-- Sucursal 2 -->
          <div class="config-card">
            <h3>📍 Sucursal Sur</h3>
            <form id="formSucursal2">
              <div class="form-group">
                <label>Nombre de la Sucursal</label>
                <input type="text" id="nombreSucursal2" placeholder="Autostok Sur" class="form-control">
              </div>
              <div class="form-group">
                <label>Dirección</label>
                <input type="text" id="direccionSucursal2" placeholder="Carrera 78 #90-12" class="form-control">
              </div>
              <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" id="telefonoSucursal2" placeholder="+57 300 765 4321" class="form-control">
              </div>
              <div class="form-group">
                <label>WhatsApp</label>
                <input type="tel" id="whatsappSucursal2" placeholder="+57 300 765 4321" class="form-control">
                <p class="helper-text">Número para recibir notificaciones de citas y servicios</p>
              </div>
              <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" id="correoSucursal2" placeholder="sur@autostok.com" class="form-control">
              </div>
              <div class="form-group">
                <label>Horario Lunes a Viernes</label>
                <input type="text" id="horarioSemanaSucursal2" placeholder="8:00 AM - 6:00 PM" class="form-control">
              </div>
              <div class="form-group">
                <label>Horario Sábados</label>
                <input type="text" id="horarioSabadoSucursal2" placeholder="9:00 AM - 2:00 PM" class="form-control">
              </div>
              <div class="form-group">
                <label>URL de Google Maps (opcional)</label>
                <input type="url" id="mapaSucursal2" placeholder="https://maps.google.com/..." class="form-control">
                <p class="helper-text">Link de ubicación en Google Maps</p>
              </div>
              <button type="submit" class="btn-primary">Guardar Sucursal Sur</button>
            </form>
          </div>
        </div>
      </section>

      <!-- Configuración Section -->
      <section id="configuracion" class="section">
        <h2>Configuración del Sistema</h2>
        
        <div class="config-cards">
          <div class="config-card">
            <h3>Información General</h3>
            <form id="formConfigGeneral">
              <div class="form-group">
                <label>Nombre del Negocio</label>
                <input type="text" id="nombreNegocio" value="Autostok" class="form-control">
              </div>
              <div class="form-group">
                <label>WhatsApp para Vehículos</label>
                <input type="tel" id="telefonoWhatsappVehiculos" placeholder="+57 300 123 4567" class="form-control">
                <p class="helper-text">Número para consultas sobre vehículos</p>
              </div>
              <div class="form-group">
                <label>WhatsApp para Servicios</label>
                <input type="tel" id="telefonoWhatsappServicios" placeholder="+57 300 765 4321" class="form-control">
                <p class="helper-text">Número para consultas sobre servicios</p>
              </div>
              <div class="form-group">
                <label>WhatsApp para Almacén</label>
                <input type="tel" id="telefonoWhatsappAlmacen" placeholder="+57 300 555 1234" class="form-control">
                <p class="helper-text">Número para solicitudes de productos/repuestos</p>
              </div>
              <div class="form-group">
                <label>Correo General</label>
                <input type="email" id="correoNegocio" placeholder="contacto@autostok.com" class="form-control">
              </div>
              <div class="form-group">
                <label>Correo Call Center</label>
                <input type="email" id="correoCallCenter" placeholder="callcenter@autostok.com" class="form-control">
                <p class="helper-text">Correo que recibirá notificaciones de citas</p>
              </div>
              <button type="submit" class="btn-primary">Guardar Configuración</button>
            </form>
          </div>

          <div class="config-card">
            <h3>Pagos PSE</h3>
            <form id="formConfigPagos">
              <div class="form-group">
                <label>URL de Pagos PSE</label>
                <input type="url" id="urlPagos" placeholder="https://tu-enlace-pse.com" class="form-control">
              </div>
              <button type="submit" class="btn-primary">Guardar URL</button>
            </form>
          </div>

          <div class="config-card">
            <h3>Sección Nosotros</h3>
            <form id="formConfigNosotros">
              <div class="form-group">
                <label>Descripción</label>
                <textarea id="descripcionNosotros" rows="4" class="form-control"></textarea>
              </div>
              <div class="form-group">
                <label>Años de Experiencia</label>
                <input type="number" id="anosExperiencia" class="form-control">
              </div>
              <div class="form-group">
                <label>Clientes Satisfechos</label>
                <input type="number" id="clientesSatisfechos" class="form-control">
              </div>
              <div class="form-group">
                <label>Vehículos Vendidos</label>
                <input type="number" id="vehiculosVendidos" class="form-control">
              </div>
              <button type="submit" class="btn-primary">Guardar Información</button>
            </form>
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
            <label>URLs de Imágenes (una por línea)</label>
            <textarea id="imagenes" name="imagenes" rows="3" placeholder="https://ejemplo.com/imagen1.jpg&#10;https://ejemplo.com/imagen2.jpg" class="form-control"></textarea>
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
            <label>URL de Imagen</label>
            <input type="url" id="imagenServicio" name="imagen" placeholder="https://ejemplo.com/imagen.jpg" class="form-control">
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
            <label>URL de Imagen</label>
            <input type="url" id="imagenProducto" name="imagen" placeholder="https://ejemplo.com/imagen.jpg" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="cerrarModalProducto()">Cancelar</button>
          <button type="submit" class="btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/admin.js"></script>
</body>
</html>