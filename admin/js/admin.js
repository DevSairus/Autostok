// =============================================
// ADMIN.JS - PANEL DE ADMINISTRACIÓN AUTOSTOK
// =============================================

// ===== NAVEGACIÓN Y TABS =====
function mostrarSeccion(seccionId) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  
  document.getElementById(seccionId).classList.add('active');
  event.target.closest('.nav-item').classList.add('active');
  
  const titulos = {
    'dashboard': 'Dashboard',
    'vehiculos': 'Gestión de Vehículos',
    'servicios': 'Gestión de Servicios',
    'productos': 'Gestión de Productos',
    'citas': 'Gestión de Citas',
    'solicitudes': 'Gestión de Solicitudes',
    'logs': 'Registro de Actividad',
    'usuarios': 'Gestión de Usuarios',
    'configuracion': 'Configuración del Sistema'
  };
  document.getElementById('sectionTitle').textContent = titulos[seccionId];
  
  if (seccionId === 'logs') {
    setTimeout(cargarLogs, 100);
  }
  if (seccionId === 'usuarios') {
    setTimeout(cargarUsuarios, 100);
  }
}

function cambiarTab(tabName) {
  document.querySelectorAll('.tab-content').forEach(tab => {
    tab.classList.remove('active');
  });
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  
  document.getElementById('tab-' + tabName).classList.add('active');
  event.target.classList.add('active');
}

// ===== NOTIFICACIONES =====
function mostrarNotificacion(mensaje, tipo = 'success') {
  const notif = document.createElement('div');
  notif.className = `notificacion ${tipo}`;
  notif.textContent = mensaje;
  notif.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    background: ${tipo === 'success' ? '#4CAF50' : '#f44336'};
    color: white;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    z-index: 10000;
    animation: slideIn 0.3s ease;
  `;
  
  document.body.appendChild(notif);
  
  setTimeout(() => {
    notif.style.animation = 'slideOut 0.3s ease';
    setTimeout(() => notif.remove(), 300);
  }, 3000);
}

if (!document.getElementById('notif-styles')) {
  const style = document.createElement('style');
  style.id = 'notif-styles';
  style.textContent = `
    @keyframes slideIn {
      from { transform: translateX(400px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(400px); opacity: 0; }
    }
  `;
  document.head.appendChild(style);
}

// ===== LOGS =====
async function cargarLogs() {
  const tipo = document.getElementById('filtroTipoLog').value;
  const tbody = document.getElementById('logsTable');
  
  try {
    const url = tipo ? `api/logs.php?tipo=${tipo}&limite=100` : 'api/logs.php?limite=100';
    const response = await fetch(url);
    const result = await response.json();
    
    if (result.success && result.logs.length > 0) {
      tbody.innerHTML = result.logs.map(log => {
        const detalles = JSON.stringify(log.datos).substring(0, 80);
        return `
          <tr>
            <td>${log.fecha}</td>
            <td><span class="tipo-badge">${log.tipo}</span></td>
            <td><span class="accion-badge">${log.accion}</span></td>
            <td>${log.usuario}</td>
            <td title="${JSON.stringify(log.datos, null, 2)}">${detalles}...</td>
            <td>${log.ip}</td>
          </tr>
        `;
      }).join('');
    } else {
      tbody.innerHTML = '<tr><td colspan="6" class="no-data">No hay logs disponibles</td></tr>';
    }
  } catch (error) {
    console.error('Error cargando logs:', error);
    tbody.innerHTML = '<tr><td colspan="6" class="error">Error al cargar logs</td></tr>';
  }
}

// ===== VEHÍCULOS =====
let imagenesVehiculoActuales = [];
let imagenesVehiculoEliminadas = [];

function abrirFormularioVehiculo() {
  document.getElementById('tituloModalVehiculo').textContent = 'Nuevo Vehículo';
  document.getElementById('formVehiculo').reset();
  document.getElementById('vehiculoId').value = '';
  imagenesVehiculoActuales = [];
  imagenesVehiculoEliminadas = [];
  
  // Limpiar previews
  document.getElementById('imagenesVehiculo').value = '';
  document.getElementById('previewImagenesVehiculo').innerHTML = '';
  
  // Ocultar sección de imágenes actuales
  const contenedorActuales = document.getElementById('imagenesActualesVehiculo');
  if (contenedorActuales) contenedorActuales.innerHTML = '';
  
  document.getElementById('modalVehiculo').classList.add('active');
}

function cerrarModalVehiculo() {
  document.getElementById('modalVehiculo').classList.remove('active');
  imagenesVehiculoEliminadas = [];
}

function editarVehiculo(vehiculo) {
  document.getElementById('tituloModalVehiculo').textContent = 'Editar Vehículo';
  document.getElementById('vehiculoId').value = vehiculo.id;
  document.getElementById('marca').value = vehiculo.marca;
  document.getElementById('modelo').value = vehiculo.modelo;
  document.getElementById('anio').value = vehiculo.anio;
  document.getElementById('precio').value = vehiculo.precio;
  document.getElementById('kilometraje').value = vehiculo.kilometraje || '';
  document.getElementById('tipo').value = vehiculo.tipo;
  document.getElementById('transmision').value = vehiculo.transmision || 'Manual';
  document.getElementById('combustible').value = vehiculo.combustible || 'Gasolina';
  document.getElementById('color').value = vehiculo.color || '';
  document.getElementById('descripcion').value = vehiculo.descripcion || '';
  
  // Guardar imágenes actuales
  imagenesVehiculoActuales = vehiculo.imagenes || [];
  imagenesVehiculoEliminadas = [];
  
  // Limpiar el input de archivos nuevos
  document.getElementById('imagenesVehiculo').value = '';
  document.getElementById('previewImagenesVehiculo').innerHTML = '';
  
  // Mostrar imágenes actuales con opción de eliminar
  mostrarImagenesActualesVehiculo();
  
  document.getElementById('modalVehiculo').classList.add('active');
}

function mostrarImagenesActualesVehiculo() {
  const contenedor = document.getElementById('imagenesActualesVehiculo');
  if (!contenedor) return;
  
  if (imagenesVehiculoActuales.length === 0) {
    contenedor.innerHTML = '<p style="color: #999; font-style: italic;">Sin imágenes</p>';
    return;
  }
  
  contenedor.innerHTML = '<label style="display: block; margin-bottom: 10px; font-weight: bold;">Imágenes Actuales:</label>';
  
  imagenesVehiculoActuales.forEach((imagen, index) => {
    if (!imagenesVehiculoEliminadas.includes(imagen)) {
      const div = document.createElement('div');
      div.style.cssText = 'display: inline-block; margin: 5px; position: relative;';
      div.innerHTML = `
        <img src="${imagen}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px; border: 2px solid #FFD700;">
        <button type="button" onclick="eliminarImagenVehiculo('${imagen.replace(/'/g, "\\'")}', ${index})" 
                style="position: absolute; top: -5px; right: -5px; background: #f44336; color: white; border: none; 
                       border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-weight: bold; 
                       display: flex; align-items: center; justify-content: center; font-size: 16px; line-height: 1;">×</button>
      `;
      contenedor.appendChild(div);
    }
  });
}

function eliminarImagenVehiculo(urlImagen, index) {
  if (!imagenesVehiculoEliminadas.includes(urlImagen)) {
    imagenesVehiculoEliminadas.push(urlImagen);
  }
  mostrarImagenesActualesVehiculo();
}

async function eliminarVehiculo(id) {
  if (!confirm('¿Estás seguro de eliminar este vehículo?')) return;
  
  try {
    const response = await fetch('api/vehiculos.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion('Vehículo eliminado exitosamente', 'success');
      location.reload();
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo eliminar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al eliminar el vehículo', 'error');
  }
}


// ===== SERVICIOS =====
let imagenServicioActual = '';

function abrirFormularioServicio() {
  document.getElementById('tituloModalServicio').textContent = 'Nuevo Servicio';
  document.getElementById('formServicio').reset();
  document.getElementById('servicioId').value = '';
  imagenServicioActual = '';
  document.getElementById('previewImagenServicio').style.display = 'none';
  document.getElementById('imagenServicioFile').value = '';
  
  // Ocultar sección de imagen actual
  const contenedorActual = document.getElementById('imagenActualServicio');
  if (contenedorActual) contenedorActual.innerHTML = '';
  
  document.getElementById('modalServicio').classList.add('active');
}

function cerrarModalServicio() {
  document.getElementById('modalServicio').classList.remove('active');
}

function editarServicio(servicio) {
  document.getElementById('tituloModalServicio').textContent = 'Editar Servicio';
  document.getElementById('servicioId').value = servicio.id;
  document.getElementById('nombreServicio').value = servicio.nombre;
  document.getElementById('categoriaServicio').value = servicio.categoria || '';
  document.getElementById('precioServicio').value = servicio.precio;
  document.getElementById('duracionServicio').value = servicio.duracion || '';
  document.getElementById('descripcionCorta').value = servicio.descripcion_corta || '';
  document.getElementById('descripcionServicio').value = servicio.descripcion || '';
  document.getElementById('caracteristicas').value = (servicio.caracteristicas || []).join('\n');
  
  // Guardar imagen actual
  imagenServicioActual = servicio.imagen || '';
  
  // Limpiar input de archivo
  document.getElementById('imagenServicioFile').value = '';
  document.getElementById('previewImagenServicio').style.display = 'none';
  
  // Mostrar imagen actual con opción de eliminar
  mostrarImagenActualServicio();
  
  document.getElementById('modalServicio').classList.add('active');
}

function mostrarImagenActualServicio() {
  const contenedor = document.getElementById('imagenActualServicio');
  if (!contenedor) return;
  
  if (!imagenServicioActual) {
    contenedor.innerHTML = '<p style="color: #999; font-style: italic;">Sin imagen</p>';
    return;
  }
  
  contenedor.innerHTML = `
    <label style="display: block; margin-bottom: 10px; font-weight: bold;">Imagen Actual:</label>
    <div style="display: inline-block; position: relative; margin: 5px;">
      <img src="${imagenServicioActual}" style="width: 150px; height: 150px; object-fit: cover; border-radius: 5px; border: 2px solid #FFD700;">
      <button type="button" onclick="eliminarImagenServicio()" 
              style="position: absolute; top: -5px; right: -5px; background: #f44336; color: white; border: none; 
                     border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-weight: bold; 
                     display: flex; align-items: center; justify-content: center; font-size: 16px; line-height: 1;">×</button>
    </div>
  `;
}

function eliminarImagenServicio() {
  imagenServicioActual = '';
  mostrarImagenActualServicio();
}

async function eliminarServicio(id) {
  if (!confirm('¿Estás seguro de eliminar este servicio?')) return;
  
  try {
    const response = await fetch('api/servicios.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion('Servicio eliminado exitosamente', 'success');
      location.reload();
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo eliminar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al eliminar el servicio', 'error');
  }
}


// ===== PRODUCTOS =====
let imagenProductoActual = '';

function abrirFormularioProducto() {
  document.getElementById('tituloModalProducto').textContent = 'Nuevo Producto';
  document.getElementById('formProducto').reset();
  document.getElementById('productoId').value = '';
  imagenProductoActual = '';
  document.getElementById('previewImagenProducto').style.display = 'none';
  document.getElementById('imagenProductoFile').value = '';
  
  // Ocultar sección de imagen actual
  const contenedorActual = document.getElementById('imagenActualProducto');
  if (contenedorActual) contenedorActual.innerHTML = '';
  
  document.getElementById('modalProducto').classList.add('active');
}

function cerrarModalProducto() {
  document.getElementById('modalProducto').classList.remove('active');
}

function editarProducto(producto) {
  document.getElementById('tituloModalProducto').textContent = 'Editar Producto';
  document.getElementById('productoId').value = producto.id;
  document.getElementById('nombreProducto').value = producto.nombre;
  document.getElementById('categoriaProducto').value = producto.categoria || '';
  document.getElementById('precioProducto').value = producto.precio;
  document.getElementById('stockProducto').value = producto.stock || 0;
  document.getElementById('marcaProducto').value = producto.marca || '';
  document.getElementById('codigoProducto').value = producto.codigo || '';
  document.getElementById('descripcionProducto').value = producto.descripcion || '';
  
  // Guardar imagen actual
  imagenProductoActual = producto.imagen || '';
  
  // Limpiar input de archivo
  document.getElementById('imagenProductoFile').value = '';
  document.getElementById('previewImagenProducto').style.display = 'none';
  
  // Mostrar imagen actual con opción de eliminar
  mostrarImagenActualProducto();
  
  document.getElementById('modalProducto').classList.add('active');
}

function mostrarImagenActualProducto() {
  const contenedor = document.getElementById('imagenActualProducto');
  if (!contenedor) return;
  
  if (!imagenProductoActual) {
    contenedor.innerHTML = '<p style="color: #999; font-style: italic;">Sin imagen</p>';
    return;
  }
  
  contenedor.innerHTML = `
    <label style="display: block; margin-bottom: 10px; font-weight: bold;">Imagen Actual:</label>
    <div style="display: inline-block; position: relative; margin: 5px;">
      <img src="${imagenProductoActual}" style="width: 150px; height: 150px; object-fit: cover; border-radius: 5px; border: 2px solid #FFD700;">
      <button type="button" onclick="eliminarImagenProducto()" 
              style="position: absolute; top: -5px; right: -5px; background: #f44336; color: white; border: none; 
                     border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-weight: bold; 
                     display: flex; align-items: center; justify-content: center; font-size: 16px; line-height: 1;">×</button>
    </div>
  `;
}

function eliminarImagenProducto() {
  imagenProductoActual = '';
  mostrarImagenActualProducto();
}

async function eliminarProducto(id) {
  if (!confirm('¿Estás seguro de eliminar este producto?')) return;
  
  try {
    const response = await fetch('api/productos.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion('Producto eliminado exitosamente', 'success');
      location.reload();
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo eliminar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al eliminar el producto', 'error');
  }
}

function filtrarCitas() {
  const filtro = document.getElementById('filtroCitas').value;
  const filas = document.querySelectorAll('#citasTable tr');
  
  filas.forEach(fila => {
    const estado = fila.dataset.estado;
    if (filtro === 'todas' || estado === filtro) {
      fila.style.display = '';
    } else {
      fila.style.display = 'none';
    }
  });
}

async function cambiarEstadoCita(id, nuevoEstado) {
  try {
    const response = await fetch('api/citas.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, estado: nuevoEstado })
    });
    
    const result = await response.json();
    if (result.success) {
      const selectElement = document.querySelector(`select[onchange*="cambiarEstadoCita(${id}"]`);
      if (selectElement) {
        const fila = selectElement.closest('tr');
        if (fila) {
          fila.dataset.estado = nuevoEstado;
        }
      }
      mostrarNotificacion('Estado actualizado exitosamente', 'success');
    } else {
      mostrarNotificacion('Error al actualizar el estado: ' + (result.message || ''), 'error');
      location.reload();
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al actualizar el estado', 'error');
    location.reload();
  }
}

function verDetalleCita(cita) {
  const mensaje = `
DETALLE DE CITA #${cita.id}

Cliente: ${cita.nombre}
Teléfono: ${cita.telefono}
Correo: ${cita.correo}

Servicio: ${cita.servicio_nombre}
Sucursal: ${cita.sucursal ? (cita.sucursal === 'sucursal1' ? 'Norte' : 'Sur') : 'N/A'}
Fecha: ${new Date(cita.fecha).toLocaleDateString()}
Hora: ${cita.hora}

Estado: ${cita.estado || 'Pendiente'}

Notas: ${cita.notas || 'Sin notas'}
  `;
  alert(mensaje);
}

async function eliminarCita(id) {
  if (!confirm('¿Estás seguro de eliminar esta cita?')) return;
  
  try {
    const response = await fetch('api/citas.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion('Cita eliminada exitosamente', 'success');
      location.reload();
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo eliminar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al eliminar la cita', 'error');
  }
}

// ===== SOLICITUDES =====
function filtrarSolicitudes() {
  const filtro = document.getElementById('filtroSolicitudes').value;
  const filas = document.querySelectorAll('#solicitudesTable tr');
  
  filas.forEach(fila => {
    const estado = fila.dataset.estado;
    if (filtro === 'todas' || estado === filtro) {
      fila.style.display = '';
    } else {
      fila.style.display = 'none';
    }
  });
}

async function cambiarEstadoSolicitud(id, nuevoEstado) {
  try {
    const response = await fetch('api/solicitudes.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, estado: nuevoEstado })
    });
    
    const result = await response.json();
    if (result.success) {
      const selectElement = document.querySelector(`select[onchange*="cambiarEstadoSolicitud(${id}"]`);
      if (selectElement) {
        const fila = selectElement.closest('tr');
        if (fila) {
          fila.dataset.estado = nuevoEstado;
        }
      }
      mostrarNotificacion('Estado actualizado exitosamente', 'success');
    } else {
      mostrarNotificacion('Error al actualizar el estado: ' + (result.message || ''), 'error');
      location.reload();
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al actualizar el estado', 'error');
    location.reload();
  }
}

function verDetalleSolicitud(solicitud) {
  const mensaje = `
DETALLE DE SOLICITUD #${solicitud.id}

Tipo: ${solicitud.tipo || 'General'}
Cliente: ${solicitud.nombre}
Teléfono: ${solicitud.telefono}
Correo: ${solicitud.correo}

${solicitud.vehiculo_nombre ? 'Vehículo: ' + solicitud.vehiculo_nombre : ''}
${solicitud.mensaje ? 'Mensaje: ' + solicitud.mensaje : ''}

Estado: ${solicitud.estado || 'Pendiente'}
Fecha: ${new Date(solicitud.fecha_solicitud).toLocaleString()}
  `;
  alert(mensaje);
}

async function eliminarSolicitud(id) {
  if (!confirm('¿Estás seguro de eliminar esta solicitud?')) return;
  
  try {
    const response = await fetch('api/solicitudes.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion('Solicitud eliminada exitosamente', 'success');
      location.reload();
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo eliminar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al eliminar la solicitud', 'error');
  }
}

// ===== USUARIOS =====
let usuariosData = [];
let rolesData = {};

async function cargarUsuarios() {
  const tbody = document.getElementById('usuariosTable');
  
  try {
    const response = await fetch('api/usuarios.php');
    const result = await response.json();
    
    if (result.success && result.usuarios) {
      usuariosData = result.usuarios;
      rolesData = result.roles || {};
      
      if (result.usuarios.length > 0) {
        tbody.innerHTML = result.usuarios.map(usuario => {
          const rolNombre = rolesData[usuario.rol]?.nombre || usuario.rol;
          const estadoBadge = usuario.activo 
            ? '<span class="status-badge confirmada">Activo</span>' 
            : '<span class="status-badge cancelada">Inactivo</span>';
          const ultimoAcceso = usuario.ultimo_acceso 
            ? new Date(usuario.ultimo_acceso).toLocaleString('es-CO')
            : 'Nunca';
          
          return `
            <tr>
              <td>${usuario.id}</td>
              <td><strong>${usuario.username}</strong></td>
              <td>${usuario.nombre}</td>
              <td>${usuario.email}</td>
              <td><span class="tipo-badge">${rolNombre}</span></td>
              <td>${estadoBadge}</td>
              <td>${ultimoAcceso}</td>
              <td>
                <button class="btn-edit" onclick='editarUsuario(${JSON.stringify(usuario)})'>✏️</button>
                ${usuario.rol !== 'super_admin' 
                  ? `<button class="btn-delete" onclick="eliminarUsuario(${usuario.id})">🗑️</button>`
                  : '<span style="opacity: 0.5">🔒</span>'}
              </td>
            </tr>
          `;
        }).join('');
      } else {
        tbody.innerHTML = '<tr><td colspan="8" class="no-data">No hay usuarios disponibles</td></tr>';
      }
    } else {
      tbody.innerHTML = '<tr><td colspan="8" class="error">Error al cargar usuarios</td></tr>';
    }
  } catch (error) {
    console.error('Error cargando usuarios:', error);
    tbody.innerHTML = '<tr><td colspan="8" class="error">Error al cargar usuarios</td></tr>';
  }
}

function abrirFormularioUsuario() {
  document.getElementById('tituloModalUsuario').textContent = 'Nuevo Usuario';
  document.getElementById('formUsuario').reset();
  document.getElementById('usuarioId').value = '';
  document.getElementById('passwordUsuario').required = true;
  document.getElementById('passwordHelper').textContent = 'Mínimo 6 caracteres';
  document.getElementById('activoUsuario').checked = true;
  document.getElementById('username').disabled = false;
  document.getElementById('rolUsuario').disabled = false;
  document.getElementById('rolDescripcion').textContent = '';
  document.getElementById('modalUsuario').classList.add('active');
}

function cerrarModalUsuario() {
  document.getElementById('modalUsuario').classList.remove('active');
  document.getElementById('username').disabled = false;
  document.getElementById('rolUsuario').disabled = false;
}

function editarUsuario(usuario) {
  document.getElementById('tituloModalUsuario').textContent = 'Editar Usuario';
  document.getElementById('usuarioId').value = usuario.id;
  document.getElementById('username').value = usuario.username;
  document.getElementById('username').disabled = true;
  document.getElementById('passwordUsuario').value = '';
  document.getElementById('passwordUsuario').required = false;
  document.getElementById('passwordHelper').textContent = 'Dejar en blanco para mantener la contraseña actual';
  document.getElementById('nombreUsuario').value = usuario.nombre;
  document.getElementById('emailUsuario').value = usuario.email;
  document.getElementById('rolUsuario').value = usuario.rol;
  document.getElementById('activoUsuario').checked = usuario.activo;
  
  if (usuario.rol === 'super_admin') {
    document.getElementById('rolUsuario').disabled = true;
  } else {
    document.getElementById('rolUsuario').disabled = false;
  }
  
  const descripciones = {
    'super_admin': 'Acceso total al sistema, incluyendo gestión de usuarios',
    'administrador': 'Acceso completo excepto gestión de usuarios',
    'ventas': 'Acceso a vehículos, solicitudes y citas',
    'taller': 'Acceso a servicios, productos y citas',
    'visualizador': 'Solo lectura en todas las secciones'
  };
  document.getElementById('rolDescripcion').textContent = descripciones[usuario.rol] || '';
  
  document.getElementById('modalUsuario').classList.add('active');
}

async function eliminarUsuario(id) {
  if (!confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')) return;
  
  try {
    const response = await fetch('api/usuarios.php', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion('Usuario eliminado exitosamente', 'success');
      cargarUsuarios();
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo eliminar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al eliminar el usuario', 'error');
  }
}

document.getElementById('formUsuario')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const id = formData.get('id');
  const password = formData.get('password');
  
  if (!id && (!password || password.length < 6)) {
    mostrarNotificacion('La contraseña debe tener al menos 6 caracteres', 'error');
    return;
  }
  
  if (id && password && password.length < 6) {
    mostrarNotificacion('La contraseña debe tener al menos 6 caracteres', 'error');
    return;
  }
  
  const datos = {
    id: id || undefined,
    username: formData.get('username'),
    nombre: formData.get('nombre'),
    email: formData.get('email'),
    rol: formData.get('rol'),
    activo: document.getElementById('activoUsuario').checked
  };
  
  if (password) {
    datos.password = password;
  }
  
  Object.keys(datos).forEach(key => datos[key] === undefined && delete datos[key]);
  
  try {
    const method = id ? 'PUT' : 'POST';
    const response = await fetch('api/usuarios.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos)
    });
    
    const result = await response.json();
    if (result.success) {
      alert(id ? 'Usuario actualizado' : 'Usuario creado');
      cerrarModalUsuario();
      cargarUsuarios();
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo guardar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al guardar el usuario', 'error');
  }
});

document.getElementById('rolUsuario')?.addEventListener('change', (e) => {
  const descripciones = {
    'super_admin': 'Acceso total al sistema, incluyendo gestión de usuarios',
    'administrador': 'Acceso completo excepto gestión de usuarios',
    'ventas': 'Acceso a vehículos, solicitudes y citas',
    'taller': 'Acceso a servicios, productos y citas',
    'visualizador': 'Solo lectura en todas las secciones'
  };
  
  const descripcion = descripciones[e.target.value] || '';
  document.getElementById('rolDescripcion').textContent = descripcion;
});

// ===== CONFIGURACIÓN =====
document.getElementById('formConfigGeneral')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const config = {
    nombreNegocio: document.getElementById('nombreNegocio').value,
    telefonoWhatsappVehiculos: document.getElementById('telefonoWhatsappVehiculos').value,
    telefonoWhatsappServicios: document.getElementById('telefonoWhatsappServicios').value,
    telefonoWhatsappAlmacen: document.getElementById('telefonoWhatsappAlmacen').value,
    correoNegocio: document.getElementById('correoNegocio').value,
    correoCallCenter: document.getElementById('correoCallCenter').value
  };
  
  try {
    const response = await fetch('api/configuracion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ tipo: 'general', datos: config })
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion('Configuración guardada exitosamente', 'success');
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo guardar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al guardar la configuración', 'error');
  }
});

document.getElementById('formConfigNosotros')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const config = {
    descripcionNosotros: document.getElementById('descripcionNosotros').value,
    anosExperiencia: document.getElementById('anosExperiencia').value,
    clientesSatisfechos: document.getElementById('clientesSatisfechos').value,
    vehiculosVendidos: document.getElementById('vehiculosVendidos').value
  };
  
  try {
    const response = await fetch('api/configuracion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ tipo: 'nosotros', datos: config })
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion('Información guardada exitosamente', 'success');
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo guardar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al guardar', 'error');
  }
});

document.getElementById('formSucursal1')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const sucursal1Data = {
    nombre: document.getElementById('nombreSucursal1').value,
    direccion: document.getElementById('direccionSucursal1').value,
    telefono: document.getElementById('telefonoSucursal1').value,
    whatsapp: document.getElementById('whatsappSucursal1').value,
    correo: document.getElementById('correoSucursal1').value,
    horarioSemana: document.getElementById('horarioSemanaSucursal1').value,
    horarioSabado: document.getElementById('horarioSabadoSucursal1').value,
    mapa: document.getElementById('mapaSucursal1').value
  };
  
  try {
    const response = await fetch('api/configuracion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ tipo: 'sucursal1', datos: sucursal1Data })
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion('Sucursal Norte guardada exitosamente', 'success');
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo guardar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al guardar la sucursal', 'error');
  }
});

document.getElementById('formSucursal2')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const sucursal2Data = {
    nombre: document.getElementById('nombreSucursal2').value,
    direccion: document.getElementById('direccionSucursal2').value,
    telefono: document.getElementById('telefonoSucursal2').value,
    whatsapp: document.getElementById('whatsappSucursal2').value,
    correo: document.getElementById('correoSucursal2').value,
    horarioSemana: document.getElementById('horarioSemanaSucursal2').value,
    horarioSabado: document.getElementById('horarioSabadoSucursal2').value,
    mapa: document.getElementById('mapaSucursal2').value
  };
  
  try {
    const response = await fetch('api/configuracion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ tipo: 'sucursal2', datos: sucursal2Data })
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion('Sucursal Sur guardada exitosamente', 'success');
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo guardar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al guardar la sucursal', 'error');
  }
});

// ===== CARGAR CONFIGURACIÓN AL INICIO =====
window.addEventListener('DOMContentLoaded', async () => {
  try {
    const response = await fetch('api/configuracion.php');
    const config = await response.json();
    
    console.log('Configuración cargada:', config);
    
    const setValueSafe = (id, value) => {
      const el = document.getElementById(id);
      if (el) el.value = value || '';
    };
    
    // Configuración general
    if (config.general) {
      setValueSafe('nombreNegocio', config.general.nombreNegocio);
      setValueSafe('telefonoWhatsappVehiculos', config.general.telefonoWhatsappVehiculos);
      setValueSafe('telefonoWhatsappServicios', config.general.telefonoWhatsappServicios);
      setValueSafe('telefonoWhatsappAlmacen', config.general.telefonoWhatsappAlmacen);
      setValueSafe('correoNegocio', config.general.correoNegocio);
      setValueSafe('correoCallCenter', config.general.correoCallCenter);
    }
    
    // Nosotros
    if (config.nosotros) {
      setValueSafe('descripcionNosotros', config.nosotros.descripcionNosotros);
      setValueSafe('anosExperiencia', config.nosotros.anosExperiencia);
      setValueSafe('clientesSatisfechos', config.nosotros.clientesSatisfechos);
      setValueSafe('vehiculosVendidos', config.nosotros.vehiculosVendidos);
    }
    
    // Sucursales
    if (config.sucursales) {
      if (config.sucursales.sucursal1) {
        const s1 = config.sucursales.sucursal1;
        setValueSafe('nombreSucursal1', s1.nombre);
        setValueSafe('direccionSucursal1', s1.direccion);
        setValueSafe('telefonoSucursal1', s1.telefono);
        setValueSafe('whatsappSucursal1', s1.whatsapp);
        setValueSafe('correoSucursal1', s1.correo);
        setValueSafe('horarioSemanaSucursal1', s1.horarioSemana);
        setValueSafe('horarioSabadoSucursal1', s1.horarioSabado);
        setValueSafe('mapaSucursal1', s1.mapa);
      }
      
      if (config.sucursales.sucursal2) {
        const s2 = config.sucursales.sucursal2;
        setValueSafe('nombreSucursal2', s2.nombre);
        setValueSafe('direccionSucursal2', s2.direccion);
        setValueSafe('telefonoSucursal2', s2.telefono);
        setValueSafe('whatsappSucursal2', s2.whatsapp);
        setValueSafe('correoSucursal2', s2.correo);
        setValueSafe('horarioSemanaSucursal2', s2.horarioSemana);
        setValueSafe('horarioSabadoSucursal2', s2.horarioSabado);
        setValueSafe('mapaSucursal2', s2.mapa);
      }
    }
  } catch (error) {
    console.error('Error al cargar configuración:', error);
  }
});

// Cerrar modales al hacer clic fuera
window.onclick = (e) => {
  if (e.target.classList.contains('modal')) {
    e.target.classList.remove('active');
  }
};

/**
 * Subir una imagen al servidor
 * @param {File} file - Archivo de imagen
 * @returns {Promise<string>} - URL de la imagen subida
 */
async function subirImagen(file) {
  const formData = new FormData();
  formData.append('imagen', file);
  
  try {
    const response = await fetch('api/upload_imagen.php', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      return result.url;
    } else {
      throw new Error(result.message || 'Error al subir imagen');
    }
  } catch (error) {
    console.error('Error subiendo imagen:', error);
    throw error;
  }
}

/**
 * Vista previa de imagen antes de subir
 * @param {File} file - Archivo de imagen
 * @param {string} previewId - ID del elemento img para preview
 */
function previsualizarImagen(file, previewId) {
  const reader = new FileReader();
  reader.onload = function(e) {
    const img = document.getElementById(previewId);
    if (img) {
      img.src = e.target.result;
      img.style.display = 'block';
    }
  };
  reader.readAsDataURL(file);
}

// =============================================
// MODIFICAR FUNCIONES DE VEHÍCULOS
// =============================================

// Reemplazar la función abrirFormularioVehiculo existente
function abrirFormularioVehiculo() {
  document.getElementById('tituloModalVehiculo').textContent = 'Nuevo Vehículo';
  document.getElementById('formVehiculo').reset();
  document.getElementById('vehiculoId').value = '';
  
  // Limpiar preview de imágenes
  document.getElementById('previewImagenesVehiculo').innerHTML = '';
  imagenesVehiculoSubidas = [];
  
  document.getElementById('modalVehiculo').classList.add('active');
}

// Reemplazar el evento submit del formulario de vehículos
document.getElementById('formVehiculo')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const btnSubmit = e.target.querySelector('button[type="submit"]');
  
  // Deshabilitar botón mientras sube
  btnSubmit.disabled = true;
  btnSubmit.textContent = 'Subiendo imágenes...';
  
  try {
    // Subir nuevas imágenes si hay archivos
    const inputImagenes = document.getElementById('imagenesVehiculo');
    const imagenesNuevas = [];
    
    if (inputImagenes.files.length > 0) {
      for (let i = 0; i < inputImagenes.files.length; i++) {
        btnSubmit.textContent = `Subiendo imagen ${i + 1} de ${inputImagenes.files.length}...`;
        const url = await subirImagen(inputImagenes.files[i]);
        imagenesNuevas.push('../' + url);
      }
    }
    
    // Determinar imágenes finales
    const vehiculoId = formData.get('id');
    let imagenesFinal = [];
    
    if (vehiculoId) {
      // Modo edición: mantener actuales (menos eliminadas) + agregar nuevas
      imagenesFinal = imagenesVehiculoActuales.filter(img => !imagenesVehiculoEliminadas.includes(img));
      imagenesFinal.push(...imagenesNuevas);
    } else {
      // Modo creación: solo nuevas
      imagenesFinal = imagenesNuevas;
    }
    
    btnSubmit.textContent = 'Guardando vehículo...';
    
    const datos = {
      id: vehiculoId || null,
      marca: formData.get('marca'),
      modelo: formData.get('modelo'),
      anio: parseInt(formData.get('anio')),
      precio: parseFloat(formData.get('precio')),
      kilometraje: parseInt(formData.get('kilometraje')) || 0,
      tipo: formData.get('tipo'),
      transmision: formData.get('transmision'),
      combustible: formData.get('combustible'),
      color: formData.get('color'),
      descripcion: formData.get('descripcion'),
      imagenes: imagenesFinal
    };
    
    const method = vehiculoId ? 'PUT' : 'POST';
    const response = await fetch('api/vehiculos.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos)
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion(vehiculoId ? 'Vehículo actualizado' : 'Vehículo creado', 'success');
      location.reload();
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo guardar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al guardar el vehículo: ' + error.message, 'error');
  } finally {
    btnSubmit.disabled = false;
    btnSubmit.textContent = 'Guardar';
  }
});

// =============================================

// Reemplazar el evento submit del formulario de servicios
document.getElementById('formServicio')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const caracteristicasTexto = formData.get('caracteristicas');
  const caracteristicas = caracteristicasTexto ? caracteristicasTexto.split('\n').filter(c => c.trim()) : [];
  
  const btnSubmit = e.target.querySelector('button[type="submit"]');
  btnSubmit.disabled = true;
  btnSubmit.textContent = 'Subiendo imagen...';
  
  try {
    // Determinar imagen final
    let imagenFinal = imagenServicioActual; // Mantener actual por defecto
    const inputImagen = document.getElementById('imagenServicioFile');
    
    // Si hay nueva imagen, subirla
    if (inputImagen && inputImagen.files.length > 0) {
      imagenFinal = '../' + await subirImagen(inputImagen.files[0]);
    }
    
    btnSubmit.textContent = 'Guardando servicio...';
    
    const datos = {
      id: formData.get('id') || null,
      nombre: formData.get('nombre'),
      categoria: formData.get('categoria'),
      precio: parseFloat(formData.get('precio')),
      duracion: formData.get('duracion'),
      descripcion_corta: formData.get('descripcion_corta'),
      descripcion: formData.get('descripcion'),
      caracteristicas: caracteristicas,
      imagen: imagenFinal
    };
    
    const method = formData.get('id') ? 'PUT' : 'POST';
    const response = await fetch('api/servicios.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos)
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion(formData.get('id') ? 'Servicio actualizado' : 'Servicio creado', 'success');
      location.reload();
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo guardar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al guardar el servicio: ' + error.message, 'error');
  } finally {
    btnSubmit.disabled = false;
    btnSubmit.textContent = 'Guardar';
  }
});

// =============================================
// MODIFICAR FUNCIONES DE PRODUCTOS
// =============================================
// MODIFICAR FUNCIONES DE PRODUCTOS
// =============================================

// Reemplazar el evento submit del formulario de productos
document.getElementById('formProducto')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const btnSubmit = e.target.querySelector('button[type="submit"]');
  btnSubmit.disabled = true;
  btnSubmit.textContent = 'Subiendo imagen...';
  
  try {
    // Determinar imagen final
    let imagenFinal = imagenProductoActual; // Mantener actual por defecto
    const inputImagen = document.getElementById('imagenProductoFile');
    
    // Si hay nueva imagen, subirla
    if (inputImagen && inputImagen.files.length > 0) {
      imagenFinal = '../' + await subirImagen(inputImagen.files[0]);
    }
    
    btnSubmit.textContent = 'Guardando producto...';
    
    const datos = {
      id: formData.get('id') || null,
      nombre: formData.get('nombre'),
      categoria: formData.get('categoria'),
      precio: parseFloat(formData.get('precio')),
      stock: parseInt(formData.get('stock')) || 0,
      marca: formData.get('marca'),
      codigo: formData.get('codigo'),
      descripcion: formData.get('descripcion'),
      imagen: imagenFinal
    };
    
    const method = formData.get('id') ? 'PUT' : 'POST';
    const response = await fetch('api/productos.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos)
    });
    
    const result = await response.json();
    if (result.success) {
      mostrarNotificacion(formData.get('id') ? 'Producto actualizado' : 'Producto creado', 'success');
      location.reload();
    } else {
      mostrarNotificacion('Error: ' + (result.message || 'No se pudo guardar'), 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error al guardar el producto: ' + error.message, 'error');
  } finally {
    btnSubmit.disabled = false;
    btnSubmit.textContent = 'Guardar';
  }
});

// =============================================
// LISTENERS PARA PREVIEW DE IMÁGENES
// =============================================

// Preview de imágenes de vehículos (múltiples)
document.getElementById('imagenesVehiculo')?.addEventListener('change', function(e) {
  const preview = document.getElementById('previewImagenesVehiculo');
  preview.innerHTML = '';
  
  Array.from(e.target.files).forEach((file, index) => {
    const reader = new FileReader();
    reader.onload = function(event) {
      const div = document.createElement('div');
      div.style.cssText = 'display: inline-block; margin: 5px; position: relative;';
      div.innerHTML = `
        <img src="${event.target.result}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px; border: 2px solid #FFD700;">
        <span style="position: absolute; top: -5px; right: -5px; background: #FFD700; color: #000; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">${index + 1}</span>
      `;
      preview.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
});

// Preview de imagen de servicio (única)
document.getElementById('imagenServicioFile')?.addEventListener('change', function(e) {
  if (e.target.files.length > 0) {
    previsualizarImagen(e.target.files[0], 'previewImagenServicio');
  }
});

// Preview de imagen de producto (única)
document.getElementById('imagenProductoFile')?.addEventListener('change', function(e) {
  if (e.target.files.length > 0) {
    previsualizarImagen(e.target.files[0], 'previewImagenProducto');
  }
});