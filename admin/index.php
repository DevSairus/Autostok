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
$citasPendientes = count(array_filter($citas, fn($c) => ($c['estado'] ?? 'pendiente') === 'pendiente'));
$solicitudesPendientes = count(array_filter($solicitudes, fn($s) => ($s['estado'] ?? 'pendiente') === 'pendiente'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Administración - AutoMarket</title>
  <link rel="stylesheet" href="css/admin.css">
  <link rel=favicon href="favicon.ico" type="image/x-icon">
  <link rel=icon href="../favicon.ico" type="image/x-icon">
</head>
<body>

  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>🚗 AutoMarket</h2>
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
        <a href="#configuracion" class="nav-item" onclick="mostrarSeccion('configuracion')">
          <span class="icon">⚙️</span>
          <span>Configuración</span>
        </a>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info">
          <span>👤 <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
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
            <div class="stat-icon">📅</div>
            <div class="stat-info">
              <h3><?php echo $citasPendientes; ?></h3>
              <p>Citas Pendientes</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">📧</div>
            <div class="stat-info">
              <h3><?php echo $solicitudesPendientes; ?></h3>
              <p>Solicitudes Pendientes</p>
            </div>
          </div>
        </div>

        <div class="recent-activity">
          <h2>Actividad Reciente</h2>
          <div class="activity-list">
            <?php 
            // Combinar citas y solicitudes
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
                    <p><strong>' . htmlspecialchars($solicitud['nombre']) . '</strong> envió una solicitud sobre <strong>' . htmlspecialchars($solicitud['vehiculo_nombre'] ?: 'Contacto general') . '</strong></p>
                    <span class="activity-time">' . date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'] ?? 'now')) . '</span>
                  </div>
                  <span class="status-badge ' . ($solicitud['estado'] ?? 'pendiente') . '">' . ucfirst($solicitud['estado'] ?? 'pendiente') . '</span>
                </div>'
              ];
            }
            
            // Ordenar por fecha
            usort($actividadReciente, function($a, $b) {
              return $b['fecha'] - $a['fecha'];
            });
            
            // Mostrar solo las 5 más recientes
            $actividadReciente = array_slice($actividadReciente, 0, 5);
            
            foreach ($actividadReciente as $actividad) {
              echo $actividad['html'];
            }
            ?>
            <?php if (empty($actividadReciente)): ?>
              <p class="no-data">No hay actividad reciente</p>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- Vehiculos Section -->
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
                    <img src="<?php echo htmlspecialchars($vehiculo['imagenes'][0] ?? 'placeholder.jpg'); ?>" 
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
                    <img src="<?php echo htmlspecialchars($servicio['imagen'] ?? 'placeholder.jpg'); ?>" 
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
                <th>Fecha</th>
                <th>Hora</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="citasTable">
              <?php foreach ($citas as $cita): ?>
                <tr data-estado="<?php echo $cita['estado'] ?? 'pendiente'; ?>">
                  <td><?php echo $cita['id']; ?></td>
                  <td><?php echo htmlspecialchars($cita['nombre']); ?></td>
                  <td><?php echo htmlspecialchars($cita['servicio_nombre']); ?></td>
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

      <!-- Configuracion Section -->
      <section id="configuracion" class="section">
        <h2>Configuración del Sistema</h2>
        
        <div class="config-cards">
          <div class="config-card">
            <h3>Información del Concesionario</h3>
            <form id="formConfigGeneral">
              <div class="form-group">
                <label>Nombre del Negocio</label>
                <input type="text" id="nombreNegocio" value="AutoMarket" class="form-control">
              </div>
              <div class="form-group">
                <label>WhatsApp para Vehículos</label>
                <input type="tel" id="telefonoWhatsappVehiculos" placeholder="+57 300 123 4567" class="form-control">
                <p class="helper-text">Número para consultas sobre vehículos</p>
              </div>
              <div class="form-group">
                <label>WhatsApp para Servicios</label>
                <input type="tel" id="telefonoWhatsappServicios" placeholder="+57 300 765 4321" class="form-control">
                <p class="helper-text">Número para consultas sobre servicios y taller</p>
              </div>
              <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" id="correoNegocio" placeholder="info@automarket.com" class="form-control">
              </div>
              <div class="form-group">
                <label>Dirección</label>
                <input type="text" id="direccion" placeholder="Dirección del concesionario" class="form-control">
              </div>
              <button type="submit" class="btn-primary">Guardar Cambios</button>
            </form>
          </div>

          <div class="config-card">
            <h3>Enlace de Pagos PSE</h3>
            <form id="formConfigPagos">
              <div class="form-group">
                <label>URL de Pagos</label>
                <input type="url" id="urlPagos" placeholder="https://tu-enlace-pse.com" class="form-control">
              </div>
              <p class="helper-text">Este enlace se usará en todos los botones de pago del sitio</p>
              <button type="submit" class="btn-primary">Guardar URL</button>
            </form>
          </div>

          <div class="config-card">
            <h3>Horarios de Atención</h3>
            <form id="formHorarios">
              <div class="form-group">
                <label>Lunes a Viernes</label>
                <input type="text" id="horarioSemana" placeholder="8:00 AM - 6:00 PM" class="form-control">
              </div>
              <div class="form-group">
                <label>Sábados</label>
                <input type="text" id="horarioSabado" placeholder="9:00 AM - 2:00 PM" class="form-control">
              </div>
              <button type="submit" class="btn-primary">Guardar Horarios</button>
            </form>
          </div>
          <div class="config-card">
            <h3>Contenido "Nosotros"</h3>
            <form id="formConfigNosotros">
              <div class="form-group">
                <label>Descripción Principal</label>
                <textarea id="descripcionNosotros" rows="4" class="form-control" placeholder="Descripción que aparece en la página Nosotros"></textarea>
              </div>
              <div class="form-group">
                <label>Años de Experiencia</label>
                <input type="number" id="anosExperiencia" value="10" class="form-control">
              </div>
              <div class="form-group">
                <label>Clientes Satisfechos</label>
                <input type="number" id="clientesSatisfechos" value="500" class="form-control">
              </div>
              <div class="form-group">
                <label>Vehículos Vendidos</label>
                <input type="number" id="vehiculosVendidos" value="1000" class="form-control">
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
      <form id="formVehiculo" enctype="multipart/form-data">
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
            <label>Imagen del Servicio</label>
            <input type="file" id="fileInputServicio" accept="image/*" class="form-control" style="padding: 10px;">
            <div id="previewServicio" class="preview-container"></div>
            <input type="hidden" id="imagenServicio" name="imagen">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="cerrarModalServicio()">Cancelar</button>
          <button type="submit" class="btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/admin.js"></script>
</body>
</html>