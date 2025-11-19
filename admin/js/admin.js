// Navegación entre secciones
function mostrarSeccion(seccionId) {
  // Ocultar todas las secciones
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  
  // Mostrar la sección seleccionada
  document.getElementById(seccionId).classList.add('active');
  event.target.closest('.nav-item').classList.add('active');
  
  // Actualizar título
  const titulos = {
    'dashboard': 'Dashboard',
    'vehiculos': 'Gestión de Vehículos',
    'servicios': 'Gestión de Servicios',
    'citas': 'Gestión de Citas',
    'solicitudes': 'Gestión de Solicitudes',
    'configuracion': 'Configuración'
  };
  document.getElementById('sectionTitle').textContent = titulos[seccionId];
}

// ===== VEHÍCULOS =====
let imagenesVehiculoSubidas = [];

function abrirFormularioVehiculo() {
  document.getElementById('tituloModalVehiculo').textContent = 'Nuevo Vehículo';
  document.getElementById('formVehiculo').reset();
  document.getElementById('vehiculoId').value = '';
  imagenesVehiculoSubidas = [];
  document.getElementById('previewVehiculo').innerHTML = '';
  document.getElementById('modalVehiculo').classList.add('active');
}

// Manejar carga de imágenes para vehículos
document.getElementById('fileInputVehiculo')?.addEventListener('change', async (e) => {
  const files = e.target.files;
  const previewDiv = document.getElementById('previewVehiculo');
  
  for (let file of files) {
    const formData = new FormData();
    formData.append('imagen', file);
    
    try {
      const response = await fetch('api/upload_imagen.php', {
        method: 'POST',
        body: formData
      });
      
      const result = await response.json();
      
      if (result.success) {
        imagenesVehiculoSubidas.push(result.url);
        
        // Mostrar preview
        const imgPreview = document.createElement('div');
        imgPreview.className = 'preview-item';
        imgPreview.innerHTML = `
          <img src="../${result.url}" alt="Preview">
          <button type="button" class="btn-remove-img" onclick="eliminarImagenVehiculo('${result.url}')">✕</button>
        `;
        previewDiv.appendChild(imgPreview);
      } else {
        alert('Error al subir imagen: ' + result.message);
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Error al subir la imagen');
    }
  }
  
  e.target.value = '';
});

function eliminarImagenVehiculo(url) {
  imagenesVehiculoSubidas = imagenesVehiculoSubidas.filter(img => img !== url);
  document.getElementById('previewVehiculo').querySelector(`img[src="../${url}"]`)?.parentElement.remove();
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
  
  // Cargar imágenes existentes
  imagenesVehiculoSubidas = vehiculo.imagenes || [];
  const previewDiv = document.getElementById('previewVehiculo');
  previewDiv.innerHTML = '';
  
  imagenesVehiculoSubidas.forEach(url => {
    const imgPreview = document.createElement('div');
    imgPreview.className = 'preview-item';
    imgPreview.innerHTML = `
      <img src="../${url}" alt="Preview">
      <button type="button" class="btn-remove-img" onclick="eliminarImagenVehiculo('${url}')">✕</button>
    `;
    previewDiv.appendChild(imgPreview);
  });
  
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

// Enviar formulario de vehículo
document.getElementById('formVehiculo')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  
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
    imagenes: imagenesVehiculoSubidas
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
let imagenServicioSubida = '';

function abrirFormularioServicio() {
  document.getElementById('tituloModalServicio').textContent = 'Nuevo Servicio';
  document.getElementById('formServicio').reset();
  document.getElementById('servicioId').value = '';
  imagenServicioSubida = '';
  document.getElementById('previewServicio').innerHTML = '';
  document.getElementById('modalServicio').classList.add('active');
}

// Manejar carga de imagen para servicios
document.getElementById('fileInputServicio')?.addEventListener('change', async (e) => {
  const file = e.target.files[0];
  if (!file) return;
  
  const formData = new FormData();
  formData.append('imagen', file);
  
  try {
    const response = await fetch('api/upload_imagen.php', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      imagenServicioSubida = result.url;
      
      // Mostrar preview
      const previewDiv = document.getElementById('previewServicio');
      previewDiv.innerHTML = `
        <div class="preview-item">
          <img src="../${result.url}" alt="Preview">
          <button type="button" class="btn-remove-img" onclick="eliminarImagenServicio()">✕</button>
        </div>
      `;
    } else {
      alert('Error al subir imagen: ' + result.message);
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al subir la imagen');
  }
  
  e.target.value = '';
});

function eliminarImagenServicio() {
  imagenServicioSubida = '';
  document.getElementById('previewServicio').innerHTML = '';
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
  
  // Cargar imagen existente
  imagenServicioSubida = servicio.imagen || '';
  const previewDiv = document.getElementById('previewServicio');
  if (imagenServicioSubida) {
    previewDiv.innerHTML = `
      <div class="preview-item">
        <img src="../${imagenServicioSubida}" alt="Preview">
        <button type="button" class="btn-remove-img" onclick="eliminarImagenServicio()">✕</button>
      </div>
    `;
  }
  
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

// Enviar formulario de servicio
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
    imagen: imagenServicioSubida
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
      alert('Error: ' + (result.message || 'No se pudo actualizar'));
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
      // Actualizar el estado en la fila
      const fila = event.target.closest('tr');
      fila.dataset.estado = nuevoEstado;
      alert('Estado actualizado exitosamente');
    } else {
      alert('Error: ' + (result.message || 'No se pudo actualizar'));
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

// ===== CONFIGURACIÓN =====
document.getElementById('formConfigGeneral')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const config = {
    nombreNegocio: document.getElementById('nombreNegocio').value,
    telefonoWhatsappVehiculos: document.getElementById('telefonoWhatsappVehiculos').value,
    telefonoWhatsappServicios: document.getElementById('telefonoWhatsappServicios').value,
    correoNegocio: document.getElementById('correoNegocio').value,
    direccion: document.getElementById('direccion').value
  };
  
  console.log('Enviando configuración:', config);
  
  try {
    const response = await fetch('api/configuracion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ tipo: 'general', datos: config })
    });
    
    const result = await response.json();
    console.log('Respuesta:', result);
    
    if (result.success) {
      alert('Configuración guardada exitosamente');
    } else {
      alert('Error: ' + (result.message || 'No se pudo guardar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al guardar la configuración: ' + error.message);
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

document.getElementById('formHorarios')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const config = {
    horarioSemana: document.getElementById('horarioSemana').value,
    horarioSabado: document.getElementById('horarioSabado').value
  };
  
  try {
    const response = await fetch('api/configuracion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ tipo: 'horarios', datos: config })
    });
    
    const result = await response.json();
    if (result.success) {
      alert('Horarios guardados exitosamente');
    } else {
      alert('Error: ' + (result.message || 'No se pudo guardar'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al guardar los horarios');
  }
});

// Cerrar modales al hacer clic fuera
window.onclick = (e) => {
  if (e.target.classList.contains('modal')) {
    e.target.classList.remove('active');
  }
};

// Cargar configuración al inicio
window.addEventListener('DOMContentLoaded', async () => {
  try {
    const response = await fetch('api/configuracion.php');
    const config = await response.json();
    
    if (config.general) {
      document.getElementById('nombreNegocio').value = config.general.nombreNegocio || 'AutoMarket';
      document.getElementById('telefonoWhatsappVehiculos').value = config.general.telefonoWhatsappVehiculos || '';
      document.getElementById('telefonoWhatsappServicios').value = config.general.telefonoWhatsappServicios || '';
      document.getElementById('correoNegocio').value = config.general.correoNegocio || '';
      document.getElementById('direccion').value = config.general.direccion || '';
    }
    
    if (config.pagos) {
      document.getElementById('urlPagos').value = config.pagos.urlPagos || '';
    }
    
    if (config.horarios) {
      document.getElementById('horarioSemana').value = config.horarios.horarioSemana || '';
      document.getElementById('horarioSabado').value = config.horarios.horarioSabado || '';
    }
  } catch (error) {
    console.error('Error al cargar configuración:', error);
  }
});