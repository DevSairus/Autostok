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

$usuariosData = file_exists('../data/usuarios.json') 
    ? json_decode(file_get_contents('../data/usuarios.json'), true) 
    : ['usuarios' => []];
$usuarios = $usuariosData['usuarios'] ?? [];

$sucursalesData = file_exists('../data/sucursales.json') 
    ? json_decode(file_get_contents('../data/sucursales.json'), true) 
    : ['sucursales' => []];
$sucursales = $sucursalesData['sucursales'] ?? [];

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
        <?php if ($_SESSION['admin_rol'] === 'super_admin'): ?>
        <a href="#usuarios" class="nav-item" onclick="mostrarSeccion('usuarios')">
          <span class="icon">👥</span>
          <span>Usuarios</span>
        </a>
        <?php endif; ?>
        <!-- <a href="#sucursales" class="nav-item" onclick="mostrarSeccion('sucursales')">
          <span class="icon">📍</span>
          <span>Sucursales</span>
        </a>  -->
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

      <!-- Sucursales Section -->
      <section id="sucursales" class="section">
        <h2>Gestión de Sucursales</h2>
        
        <div class="config-cards">
          <!-- Sucursal 1 -->
          <div class="config-card">
            <h3>📍 Sucursal Norte</h3>
            <form id="form1">
              <div class="form-group">
                <label>Nombre de la Sucursal</label>
                <input type="text" id="nombre1" placeholder="Autostok Norte" class="form-control">
              </div>
              <div class="form-group">
                <label>Dirección</label>
                <input type="text" id="direccion1" placeholder="Calle 123 #45-67" class="form-control">
              </div>
              <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" id="telefono1" placeholder="+57 300 123 4567" class="form-control">
              </div>
              <div class="form-group">
                <label>WhatsApp</label>
                <input type="tel" id="whatsapp1" placeholder="+57 300 123 4567" class="form-control">
                <p class="helper-text">Número para recibir notificaciones de citas y servicios</p>
              </div>
              <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" id="correo1" placeholder="norte@autostok.com" class="form-control">
              </div>
              <div class="form-group">
                <label>Horario Lunes a Viernes</label>
                <input type="text" id="horarioSemana1" placeholder="8:00 AM - 6:00 PM" class="form-control">
              </div>
              <div class="form-group">
                <label>Horario Sábados</label>
                <input type="text" id="horarioSabado1" placeholder="9:00 AM - 2:00 PM" class="form-control">
              </div>
              <div class="form-group">
                <label>URL de Google Maps (opcional)</label>
                <input type="url" id="mapa1" placeholder="https://maps.google.com/..." class="form-control">
                <p class="helper-text">Link de ubicación en Google Maps</p>
              </div>
              <button type="submit" class="btn-primary">Guardar Sucursal Norte</button>
            </form>
          </div>

          <!-- Sucursal 2 -->
          <div class="config-card">
            <h3>📍 Sucursal Sur</h3>
            <form id="form2">
              <div class="form-group">
                <label>Nombre de la Sucursal</label>
                <input type="text" id="nombre2" placeholder="Autostok Sur" class="form-control">
              </div>
              <div class="form-group">
                <label>Dirección</label>
                <input type="text" id="direccion2" placeholder="Carrera 78 #90-12" class="form-control">
              </div>
              <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" id="telefono2" placeholder="+57 300 765 4321" class="form-control">
              </div>
              <div class="form-group">
                <label>WhatsApp</label>
                <input type="tel" id="whatsapp2" placeholder="+57 300 765 4321" class="form-control">
                <p class="helper-text">Número para recibir notificaciones de citas y servicios</p>
              </div>
              <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" id="correo2" placeholder="sur@autostok.com" class="form-control">
              </div>
              <div class="form-group">
                <label>Horario Lunes a Viernes</label>
                <input type="text" id="horarioSemana2" placeholder="8:00 AM - 6:00 PM" class="form-control">
              </div>
              <div class="form-group">
                <label>Horario Sábados</label>
                <input type="text" id="horarioSabado2" placeholder="9:00 AM - 2:00 PM" class="form-control">
              </div>
              <div class="form-group">
                <label>URL de Google Maps (opcional)</label>
                <input type="url" id="mapa2" placeholder="https://maps.google.com/..." class="form-control">
                <p class="helper-text">Link de ubicación en Google Maps</p>
              </div>
              <button type="submit" class="btn-primary">Guardar Sucursal Sur</button>
            </form>
          </div>
        </div>
      </section>

      
      <!-- Configuración y Sucursales Unificadas -->
      <section id="configuracion" class="section">
        <h2>Configuración del Sistema</h2>
        
        <div class="config-tabs">
          <button class="tab-btn active" onclick="cambiarTab('general')">General</button>
          <!-- <button class="tab-btn" onclick="cambiarTab('nosotros')">Nosotros</button> -->
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

        <!-- TAB: NOSOTROS 
        <div id="tab-nosotros" class="tab-content">
          <div class="config-card">
            <h3>ℹ️ Sección "Nosotros"</h3>
            <form id="formConfigNosotros">
              <div class="form-group">
                <label>Descripción de la Empresa</label>
                <textarea id="descripcionNosotros" rows="5" class="form-control" placeholder="Somos una empresa dedicada..."></textarea>
              </div>
              
              <div class="form-row">
                <div class="form-group">
                  <label>Años de Experiencia</label>
                  <input type="number" id="anosExperiencia" class="form-control" min="0">
                </div>
                <div class="form-group">
                  <label>Clientes Satisfechos</label>
                  <input type="number" id="clientesSatisfechos" class="form-control" min="0">
                </div>
                <div class="form-group">
                  <label>Vehículos Vendidos</label>
                  <input type="number" id="vehiculosVendidos" class="form-control" min="0">
                </div>
              </div>
              
              <button type="submit" class="btn-primary">💾 Guardar Información</button>
            </form>
          </div>
        </div> -->

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

<!-- Modal Formulario Vehículo - CON UPLOAD DE IMÁGENES -->
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
        
        <!-- NUEVO: Input de archivos para imágenes -->
        <div class="form-group">
          <label>Imágenes del Vehículo</label>
          <!-- Contenedor para imágenes actuales (edición) -->
          <div id="imagenesActualesVehiculo" style="margin-bottom: 15px;"></div>
          <!-- Input para nuevas imágenes -->
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

<!-- Modal Formulario Servicio - CON UPLOAD DE IMAGEN -->
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
        
        <!-- NUEVO: Input de archivo para imagen -->
        <div class="form-group">
          <label>Imagen del Servicio</label>
          <!-- Contenedor para imagen actual (edición) -->
          <div id="imagenActualServicio" style="margin-bottom: 15px;"></div>
          <!-- Input para nueva imagen -->
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

<!-- Modal Formulario Producto - CON UPLOAD DE IMAGEN -->
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
        
        <!-- NUEVO: Input de archivo para imagen -->
        <div class="form-group">
          <label>Imagen del Producto</label>
          <!-- Contenedor para imagen actual (edición) -->
          <div id="imagenActualProducto" style="margin-bottom: 15px;"></div>
          <!-- Input para nueva imagen -->
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
      .sucursales-grid {
        grid-template-columns: 1fr;
      }
    }
    </style>

  <script src="js/admin.js"></script>
</body>
</html>