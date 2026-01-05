?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Imágenes - Panel de Administración</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Avenir', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
      color: #fff;
      padding: 20px;
      min-height: 100vh;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    h1 {
      text-align: center;
      color: #FFD700;
      margin-bottom: 40px;
      font-size: 2.5rem;
      text-shadow: 0 0 20px rgba(255,215,0,0.3);
    }

    .form-section {
      background: rgba(255,215,0,0.05);
      border: 2px solid rgba(255,215,0,0.3);
      border-radius: 15px;
      padding: 30px;
      margin-bottom: 30px;
    }

    .form-section h2 {
      color: #FFD700;
      margin-bottom: 20px;
      font-size: 1.5rem;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      color: #e0e0e0;
      margin-bottom: 8px;
      font-weight: 600;
    }

    input[type="text"],
    input[type="file"],
    textarea {
      width: 100%;
      padding: 12px;
      background: rgba(0,0,0,0.6);
      border: 2px solid rgba(255,215,0,0.3);
      border-radius: 8px;
      color: #fff;
      font-family: 'Avenir', sans-serif;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="file"]:focus,
    textarea:focus {
      outline: none;
      border-color: #FFD700;
      box-shadow: 0 0 15px rgba(255,215,0,0.2);
      background: rgba(0,0,0,0.8);
    }

    input[type="file"] {
      padding: 10px;
      cursor: pointer;
    }

    input[type="file"]::file-selector-button {
      padding: 8px 20px;
      background: #FFD700;
      color: #000;
      border: none;
      border-radius: 5px;
      font-weight: 600;
      cursor: pointer;
      margin-right: 10px;
      transition: all 0.3s ease;
    }

    input[type="file"]::file-selector-button:hover {
      background: #FFA500;
      transform: scale(1.05);
    }

    textarea {
      resize: vertical;
      min-height: 80px;
    }

    .preview-container {
      margin-top: 15px;
      border-radius: 8px;
      overflow: hidden;
      border: 2px solid rgba(255,215,0,0.3);
      background: rgba(0,0,0,0.3);
      padding: 10px;
    }

    .preview-label {
      color: #FFD700;
      font-size: 0.9rem;
      margin-bottom: 10px;
      display: block;
    }

    .preview {
      max-width: 100%;
      max-height: 300px;
      border-radius: 5px;
      display: none;
    }

    .preview.visible {
      display: block;
    }

    .file-info {
      color: #999;
      font-size: 0.85rem;
      margin-top: 8px;
    }

    .button-group {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }

    button {
      flex: 1;
      padding: 12px 20px;
      background: linear-gradient(135deg, #FFD700, #FFA500);
      color: #000;
      border: none;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      font-family: 'Avenir', sans-serif;
    }

    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(255,215,0,0.4);
    }

    .btn-secondary {
      background: rgba(255,215,0,0.2);
      color: #FFD700;
      border: 2px solid rgba(255,215,0,0.5);
    }

    .btn-secondary:hover {
      background: rgba(255,215,0,0.3);
    }

    .mensaje {
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      display: none;
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

    .back-link {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      background: rgba(255,215,0,0.2);
      color: #FFD700;
      text-decoration: none;
      border-radius: 8px;
      transition: all 0.3s ease;
      border: 2px solid rgba(255,215,0,0.5);
    }

    .back-link:hover {
      background: rgba(255,215,0,0.3);
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

    @media (max-width: 768px) {
      h1 {
        font-size: 1.8rem;
      }

      .button-group {
        flex-direction: column;
      }

      .preview {
        max-height: 200px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>🖼️ Editar Imágenes de Inicio</h1>

    <div id="mensaje" class="mensaje"></div>

    <!-- Sección 1: Vehículos -->
    <div class="form-section">
      <h2>Sección 1: Vehículos</h2>
      
      <div class="form-group">
        <label>Título</label>
        <input type="text" id="titulo1" value="Vehículos">
      </div>

      <div class="form-group">
        <label>Descripción</label>
        <textarea id="descripcion1">Descubre nuestra exclusiva selección de vehículos premium. Calidad, estilo y potencia en cada modelo.</textarea>
      </div>

      <div class="form-group">
        <label>Imagen</label>
        <input type="file" id="imagen1" accept="image/*" onchange="previewImagen('imagen1', 'preview1')">
        <div class="file-info">Formatos soportados: JPG, PNG, WebP. Tamaño máximo: 5MB</div>
        <div class="preview-container">
          <span class="preview-label">Vista previa actual:</span>
          <img id="preview1" class="preview" alt="Preview">
        </div>
      </div>

      <div class="form-group">
        <label>Enlace de destino</label>
        <input type="text" id="enlace1" value="vehiculos/catalogo.php">
      </div>
    </div>

    <!-- Sección 2: Servicios -->
    <div class="form-section">
      <h2>Sección 2: Servicios</h2>
      
      <div class="form-group">
        <label>Título</label>
        <input type="text" id="titulo2" value="Servicios">
      </div>

      <div class="form-group">
        <label>Descripción</label>
        <textarea id="descripcion2">Taller especializado, mantenimiento, accesorios y todo lo que tu vehículo necesita.</textarea>
      </div>

      <div class="form-group">
        <label>Imagen</label>
        <input type="file" id="imagen2" accept="image/*" onchange="previewImagen('imagen2', 'preview2')">
        <div class="file-info">Formatos soportados: JPG, PNG, WebP. Tamaño máximo: 5MB</div>
        <div class="preview-container">
          <span class="preview-label">Vista previa actual:</span>
          <img id="preview2" class="preview" alt="Preview">
        </div>
      </div>

      <div class="form-group">
        <label>Enlace de destino</label>
        <input type="text" id="enlace2" value="servicios/servicios.php">
      </div>
    </div>

    <div class="button-group">
      <button onclick="guardarCambios()">💾 Guardar Cambios</button>
      <button class="btn-secondary" onclick="cargarConfiguracion()">🔄 Recargar</button>
    </div>

    <a href="index.php" class="back-link">← Volver al Panel</a>
  </div>

  <script>
    // Función para previsualizar imagen
    function previewImagen(inputId, previewId) {
      const input = document.getElementById(inputId);
      const preview = document.getElementById(previewId);
      
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
          preview.src = e.target.result;
          preview.classList.add('visible');
        };
        reader.readAsDataURL(input.files[0]);
      }
    }

    // Cargar configuración actual
    async function cargarConfiguracion() {
      try {
        const response = await fetch('../data/configuracion.json');
        const config = await response.json();
        const imagenes = config.imagenes || {};

        // Sección 1
        document.getElementById('titulo1').value = imagenes.index_seccion1?.titulo || 'Vehículos';
        document.getElementById('descripcion1').value = imagenes.index_seccion1?.descripcion || '';
        document.getElementById('enlace1').value = imagenes.index_seccion1?.enlace || 'vehiculos/catalogo.php';
        
        if (imagenes.index_seccion1?.imagen) {
          document.getElementById('preview1').src = '../' + imagenes.index_seccion1.imagen;
          document.getElementById('preview1').classList.add('visible');
        }

        // Sección 2
        document.getElementById('titulo2').value = imagenes.index_seccion2?.titulo || 'Servicios';
        document.getElementById('descripcion2').value = imagenes.index_seccion2?.descripcion || '';
        document.getElementById('enlace2').value = imagenes.index_seccion2?.enlace || 'servicios/servicios.php';
        
        if (imagenes.index_seccion2?.imagen) {
          document.getElementById('preview2').src = '../' + imagenes.index_seccion2.imagen;
          document.getElementById('preview2').classList.add('visible');
        }
      } catch (error) {
        console.error('Error cargando configuración:', error);
      }
    }

    // Guardar cambios
    async function guardarCambios() {
      const formData = new FormData();
      
      formData.append('titulo1', document.getElementById('titulo1').value);
      formData.append('descripcion1', document.getElementById('descripcion1').value);
      formData.append('enlace1', document.getElementById('enlace1').value);
      formData.append('imagen1', document.getElementById('imagen1').files[0]);
      
      formData.append('titulo2', document.getElementById('titulo2').value);
      formData.append('descripcion2', document.getElementById('descripcion2').value);
      formData.append('enlace2', document.getElementById('enlace2').value);
      formData.append('imagen2', document.getElementById('imagen2').files[0]);

      try {
        const response = await fetch('api/guardar-imagenes.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        const mensajeEl = document.getElementById('mensaje');
        if (result.success) {
          mensajeEl.className = 'mensaje exito';
          mensajeEl.textContent = '✓ ' + result.message;
          setTimeout(() => mensajeEl.style.display = 'none', 4000);
          
          // Recargar configuración después de 1 segundo
          setTimeout(() => cargarConfiguracion(), 1000);
        } else {
          mensajeEl.className = 'mensaje error';
          mensajeEl.textContent = '✗ Error: ' + (result.message || 'No se pudieron guardar los cambios');
        }
      } catch (error) {
        console.error('Error:', error);
        const mensajeEl = document.getElementById('mensaje');
        mensajeEl.className = 'mensaje error';
        mensajeEl.textContent = '✗ Error al guardar los cambios';
      }
    }

    // Cargar configuración al iniciar
    cargarConfiguracion();
  </script>

</body>
</html>

<?php