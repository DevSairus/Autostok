/**
 * Funciones para la gestión de imágenes del home
 * Maneja la carga, vista previa y guardado de imágenes
 */

// Vista previa de imagen al seleccionar archivo
function previewImagen(inputId, previewId) {
  const input = document.getElementById(inputId);
  const preview = document.getElementById(previewId);
  
  if (input.files && input.files[0]) {
    const file = input.files[0];
    
    // Validar tamaño (5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('La imagen es demasiado grande. Máximo 5MB');
      input.value = '';
      return;
    }
    
    // Validar tipo
    const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    if (!tiposPermitidos.includes(file.type)) {
      alert('Tipo de archivo no permitido. Solo JPG, PNG, WebP, GIF');
      input.value = '';
      return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  }
}

// Guardar imagen y actualizar configuración
async function guardarImagen(numero) {
  const formData = new FormData();
  const imagenInput = document.getElementById(`imagen${numero}`);
  const mensajeEl = document.getElementById('mensajeImagenes');
  
  // Agregar campos de texto
  formData.append(`titulo${numero}`, document.getElementById(`titulo${numero}`).value);
  formData.append(`descripcion${numero}`, document.getElementById(`descripcion${numero}`).value);
  formData.append(`enlace${numero}`, document.getElementById(`enlace${numero}`).value);
  
  // Agregar imagen si se seleccionó una nueva
  if (imagenInput.files && imagenInput.files[0]) {
    formData.append(`imagen${numero}`, imagenInput.files[0]);
  }

  // Mostrar mensaje de carga
  mensajeEl.className = 'mensaje';
  mensajeEl.textContent = '⏳ Guardando cambios...';
  mensajeEl.style.display = 'block';
  mensajeEl.style.background = 'rgba(255,215,0,0.1)';
  mensajeEl.style.border = '2px solid rgba(255,215,0,0.5)';
  mensajeEl.style.color = '#FFD700';

  try {
    const response = await fetch('api/guardar-imagenes.php', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status}`);
    }

    const result = await response.json();
    
    if (result.success) {
      mensajeEl.className = 'mensaje exito';
      mensajeEl.textContent = '✓ ' + result.message;
      mensajeEl.style.display = 'block';
      
      // Si se subió una nueva imagen, actualizar la vista previa con cache-busting
      if (result.data && result.data.imagen) {
        const preview = document.getElementById(`preview${numero}`);
        preview.src = '../' + result.data.imagen + '?t=' + new Date().getTime();
        preview.style.display = 'block';
      }
      
      // Limpiar el input de archivo
      imagenInput.value = '';
      
      // Ocultar mensaje después de 3 segundos
      setTimeout(() => {
        mensajeEl.style.display = 'none';
      }, 3000);
    } else {
      mensajeEl.className = 'mensaje error';
      mensajeEl.textContent = '✗ Error: ' + (result.message || 'No se pudieron guardar los cambios');
      mensajeEl.style.display = 'block';
    }
  } catch (error) {
    console.error('Error completo:', error);
    mensajeEl.className = 'mensaje error';
    mensajeEl.textContent = '✗ Error de conexión: ' + error.message;
    mensajeEl.style.display = 'block';
  }
}

// Cargar configuración actual al iniciar la página
async function cargarConfiguracionImagenes() {
  try {
    // Cache busting para evitar problemas de caché
    const response = await fetch('../data/configuracion.json?t=' + new Date().getTime());
    
    if (!response.ok) {
      console.warn('No se pudo cargar configuración existente');
      return;
    }
    
    const config = await response.json();
    const imagenes = config.imagenes || {};

    // Cargar datos de sección 1
    if (imagenes.index_seccion1) {
      const sec1 = imagenes.index_seccion1;
      
      if (sec1.titulo) {
        document.getElementById('titulo1').value = sec1.titulo;
      }
      if (sec1.descripcion) {
        document.getElementById('descripcion1').value = sec1.descripcion;
      }
      if (sec1.enlace) {
        document.getElementById('enlace1').value = sec1.enlace;
      }
      if (sec1.imagen) {
        const img1 = document.getElementById('preview1');
        img1.src = '../' + sec1.imagen + '?t=' + new Date().getTime();
        img1.style.display = 'block';
      }
    }

    // Cargar datos de sección 2
    if (imagenes.index_seccion2) {
      const sec2 = imagenes.index_seccion2;
      
      if (sec2.titulo) {
        document.getElementById('titulo2').value = sec2.titulo;
      }
      if (sec2.descripcion) {
        document.getElementById('descripcion2').value = sec2.descripcion;
      }
      if (sec2.enlace) {
        document.getElementById('enlace2').value = sec2.enlace;
      }
      if (sec2.imagen) {
        const img2 = document.getElementById('preview2');
        img2.src = '../' + sec2.imagen + '?t=' + new Date().getTime();
        img2.style.display = 'block';
      }
    }
    
    console.log('✓ Configuración de imágenes cargada correctamente');
  } catch (error) {
    console.error('Error cargando configuración de imágenes:', error);
  }
}

// Ejecutar al cargar la página
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', cargarConfiguracionImagenes);
} else {
  cargarConfiguracionImagenes();
}