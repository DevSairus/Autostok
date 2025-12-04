// Navegación entre secciones
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
    'sucursales': 'Gestión de Sucursales',
    'configuracion': 'Configuración'
  };
  document.getElementById('sectionTitle').textContent = titulos[seccionId];
  
  // Cargar logs si es esa sección
  if (seccionId === 'logs') {
    setTimeout(cargarLogs, 100);
  }
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
let imagenesVehiculoSubidas = [];

function abrirFormularioVehiculo() {
  document.getElementById('tituloModalVehiculo').textContent = 'Nuevo Vehículo';
  document.getElementById('formVehiculo').reset();
  document.getElementById('vehiculoId').value = '';
  imagenesVehiculoSubidas = [];
  document.getElementById('modalVehiculo').classList.add('active');
}

function cerrarModalVehiculo() {
  document.getElementById('modalVehiculo').classList.remove('active');
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
  document.getElementById('imagenes').value = (vehiculo.imagenes || []).join('\n');
  
  document.getElementById('modalVehiculo').classList.add('active');
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
      alert('Vehículo eliminado exitosamente');
      location.reload();
    } else {
      alert('Error: ' + (result.message || 'No se pudo eliminar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al eliminar el vehículo');
  }
}

document.getElementById('formVehiculo')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const imagenesTexto = formData.get('imagenes');
  const imagenes = imagenesTexto ? imagenesTexto.split('\n').filter(img => img.trim()) : [];
  
  const datos = {
    id: formData.get('id') || Date.now(),
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
    imagenes: imagenes
  };
  
  try {
    const method = formData.get('id') ? 'PUT' : 'POST';
    const response = await fetch('api/vehiculos.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos)
    });
    
    const result = await response.json();
    if (result.success) {
      alert(formData.get('id') ? 'Vehículo actualizado' : 'Vehículo creado');
      location.reload();
    } else {
      alert('Error: ' + (result.message || 'No se pudo guardar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al guardar el vehículo');
  }
});

// ===== SERVICIOS =====
function abrirFormularioServicio() {
  document.getElementById('tituloModalServicio').textContent = 'Nuevo Servicio';
  document.getElementById('formServicio').reset();
  document.getElementById('servicioId').value = '';
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
  document.getElementById('imagenServicio').value = servicio.imagen || '';
  
  document.getElementById('modalServicio').classList.add('active');
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
      alert('Servicio eliminado exitosamente');
      location.reload();
    } else {
      alert('Error: ' + (result.message || 'No se pudo eliminar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al eliminar el servicio');
  }
}

document.getElementById('formServicio')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const caracteristicasTexto = formData.get('caracteristicas');
  const caracteristicas = caracteristicasTexto ? caracteristicasTexto.split('\n').filter(c => c.trim()) : [];
  
  const datos = {
    id: formData.get('id') || Date.now(),
    nombre: formData.get('nombre'),
    categoria: formData.get('categoria'),
    precio: parseFloat(formData.get('precio')),
    duracion: formData.get('duracion'),
    descripcion_corta: formData.get('descripcion_corta'),
    descripcion: formData.get('descripcion'),
    caracteristicas: caracteristicas,
    imagen: formData.get('imagen') || document.getElementById('imagenServicio').value
  };
  
  try {
    const method = formData.get('id') ? 'PUT' : 'POST';
    const response = await fetch('api/servicios.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos)
    });
    
    const result = await response.json();
    if (result.success) {
      alert(formData.get('id') ? 'Servicio actualizado' : 'Servicio creado');
      location.reload();
    } else {
      alert('Error: ' + (result.message || 'No se pudo guardar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al guardar el servicio');
  }
});

// ===== PRODUCTOS =====
function abrirFormularioProducto() {
  document.getElementById('tituloModalProducto').textContent = 'Nuevo Producto';
  document.getElementById('formProducto').reset();
  document.getElementById('productoId').value = '';
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
  document.getElementById('imagenProducto').value = producto.imagen || '';
  
  document.getElementById('modalProducto').classList.add('active');
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
      alert('Producto eliminado exitosamente');
      location.reload();
    } else {
      alert('Error: ' + (result.message || 'No se pudo eliminar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al eliminar el producto');
  }
}

document.getElementById('formProducto')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  
  const datos = {
    id: formData.get('id') || Date.now(),
    nombre: formData.get('nombre'),
    categoria: formData.get('categoria'),
    precio: parseFloat(formData.get('precio')),
    stock: parseInt(formData.get('stock')) || 0,
    marca: formData.get('marca'),
    codigo: formData.get('codigo'),
    descripcion: formData.get('descripcion'),
    imagen: formData.get('imagen') || document.getElementById('imagenProducto').value
  };
  
  try {
    const method = formData.get('id') ? 'PUT' : 'POST';
    const response = await fetch('api/productos.php', {
      method: method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos)
    });
    
    const result = await response.json();
    if (result.success) {
      alert(formData.get('id') ? 'Producto actualizado' : 'Producto creado');
      location.reload();
    } else {
      alert('Error: ' + (result.message || 'No se pudo guardar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al guardar el producto');
  }
});

// ===== CITAS =====
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
      const fila = event.target.closest('tr');
      fila.dataset.estado = nuevoEstado;
      alert('Estado actualizado exitosamente');
    } else {
      alert('Error al actualizar el estado: ' + (result.message || ''));
      location.reload();
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al actualizar el estado');
  }
}

function verDetalleCita(cita) {
  const mensaje = `
DETALLE DE CITA #${cita.id}

Cliente: ${cita.nombre}
Teléfono: ${cita.telefono}
Correo: ${cita.correo}

Servicio: ${cita.servicio_nombre}
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
      alert('Cita eliminada exitosamente');
      location.reload();
    } else {
      alert('Error: ' + (result.message || 'No se pudo eliminar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al eliminar la cita');
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
      const fila = event.target.closest('tr');
      fila.dataset.estado = nuevoEstado;
      alert('Estado actualizado exitosamente');
    } else {
      alert('Error al actualizar el estado: ' + (result.message || ''));
      location.reload();
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al actualizar el estado');
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
      alert('Solicitud eliminada exitosamente');
      location.reload();
    } else {
      alert('Error: ' + (result.message || 'No se pudo eliminar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al eliminar la solicitud');
  }
}

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
      alert('Configuración guardada exitosamente');
    } else {
      alert('Error: ' + (result.message || 'No se pudo guardar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al guardar la configuración');
  }
});

document.getElementById('formConfigPagos')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const config = {
    urlPagos: document.getElementById('urlPagos').value
  };
  
  try {
    const response = await fetch('api/configuracion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ tipo: 'pagos', datos: config })
    });
    
    const result = await response.json();
    if (result.success) {
      alert('URL de pagos guardada exitosamente');
    } else {
      alert('Error: ' + (result.message || 'No se pudo guardar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al guardar la URL de pagos');
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
      alert('Información guardada exitosamente');
    } else {
      alert('Error: ' + (result.message || 'No se pudo guardar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al guardar');
  }
});

// ===== SUCURSALES =====
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
      alert('Sucursal Norte guardada exitosamente');
    } else {
      alert('Error: ' + (result.message || 'No se pudo guardar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al guardar la sucursal');
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
      alert('Sucursal Sur guardada exitosamente');
    } else {
      alert('Error: ' + (result.message || 'No se pudo guardar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al guardar la sucursal');
  }
});

// Cargar configuración al inicio
window.addEventListener('DOMContentLoaded', async () => {
  try {
    const response = await fetch('api/configuracion.php');
    const config = await response.json();
    
    // Configuración general
    if (config.general) {
      document.getElementById('nombreNegocio').value = config.general.nombreNegocio || '';
      document.getElementById('telefonoWhatsappVehiculos').value = config.general.telefonoWhatsappVehiculos || '';
      document.getElementById('telefonoWhatsappServicios').value = config.general.telefonoWhatsappServicios || '';
      document.getElementById('telefonoWhatsappAlmacen').value = config.general.telefonoWhatsappAlmacen || '';
      document.getElementById('correoNegocio').value = config.general.correoNegocio || '';
      document.getElementById('correoCallCenter').value = config.general.correoCallCenter || '';
    }
    
    // Pagos
    if (config.pagos) {
      document.getElementById('urlPagos').value = config.pagos.urlPagos || '';
    }
    
    // Nosotros
    if (config.nosotros) {
      document.getElementById('descripcionNosotros').value = config.nosotros.descripcionNosotros || '';
      document.getElementById('anosExperiencia').value = config.nosotros.anosExperiencia || '';
      document.getElementById('clientesSatisfechos').value = config.nosotros.clientesSatisfechos || '';
      document.getElementById('vehiculosVendidos').value = config.nosotros.vehiculosVendidos || '';
    }
    
    // Sucursales
    if (config.sucursales?.sucursal1) {
      const s1 = config.sucursales.sucursal1;
      document.getElementById('nombreSucursal1').value = s1.nombre || '';
      document.getElementById('direccionSucursal1').value = s1.direccion || '';
      document.getElementById('telefonoSucursal1').value = s1.telefono || '';
      document.getElementById('whatsappSucursal1').value = s1.whatsapp || '';
      document.getElementById('correoSucursal1').value = s1.correo || '';
      document.getElementById('horarioSemanaSucursal1').value = s1.horarioSemana || '';
      document.getElementById('horarioSabadoSucursal1').value = s1.horarioSabado || '';
      document.getElementById('mapaSucursal1').value = s1.mapa || '';
    }
    
    if (config.sucursales?.sucursal2) {
      const s2 = config.sucursales.sucursal2;
      document.getElementById('nombreSucursal2').value = s2.nombre || '';
      document.getElementById('direccionSucursal2').value = s2.direccion || '';
      document.getElementById('telefonoSucursal2').value = s2.telefono || '';
      document.getElementById('whatsappSucursal2').value = s2.whatsapp || '';
      document.getElementById('correoSucursal2').value = s2.correo || '';
      document.getElementById('horarioSemanaSucursal2').value = s2.horarioSemana || '';
      document.getElementById('horarioSabadoSucursal2').value = s2.horarioSabado || '';
      document.getElementById('mapaSucursal2').value = s2.mapa || '';
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