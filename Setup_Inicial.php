<?php
/**
 * Script de configuración inicial - Autostok
 * Ejecutar una sola vez: http://tu-sitio.com/setup.php
 * Después de ejecutar, eliminar este archivo por seguridad
 */

$mensaje = [];

// Crear directorios necesarios
$directorios = ['data', 'uploads', 'admin/api'];

foreach ($directorios as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            $mensaje[] = "✅ Directorio '$dir/' creado";
        } else {
            $mensaje[] = "❌ Error al crear directorio '$dir/'";
        }
    } else {
        $mensaje[] = "ℹ️ Directorio '$dir/' ya existe";
    }
}

// ===== ESTRUCTURA DE DATOS VACÍA =====

// Vehículos vacío
$vehiculos = [
    'vehiculos' => []
];

// Servicios vacío
$servicios = [
    'servicios' => []
];

// Productos vacío
$productos = [
    'productos' => []
];

// Citas vacío
$citas = [
    'citas' => []
];

// Solicitudes vacío
$solicitudes = [
    'solicitudes' => []
];

// Usuarios - Solo super administrador
$usuarios = [
    'usuarios' => [
        [
            'id' => 1,
            'username' => 'superadmin',
            'password' => password_hash('Admin123', PASSWORD_DEFAULT),
            'nombre' => 'Super Administrador',
            'email' => 'admin@autostok.com',
            'rol' => 'super_admin',
            'activo' => true,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'ultimo_acceso' => null
        ]
    ],
    'roles' => [
        'super_admin' => [
            'nombre' => 'Super Administrador',
            'permisos' => ['all']
        ],
        'administrador' => [
            'nombre' => 'Administrador',
            'permisos' => ['vehiculos', 'servicios', 'productos', 'citas', 'solicitudes', 'configuracion']
        ],
        'ventas' => [
            'nombre' => 'Vendedor',
            'permisos' => ['vehiculos', 'solicitudes', 'citas']
        ],
        'taller' => [
            'nombre' => 'Taller',
            'permisos' => ['servicios', 'productos', 'citas']
        ],
        'visualizador' => [
            'nombre' => 'Visualizador',
            'permisos' => ['view_only']
        ]
    ]
];

// Configuración inicial
$configuracion = [
    'general' => [
        'nombreNegocio' => 'Autostok',
        'telefonoWhatsappVehiculos' => '+57 300 123 4567',
        'telefonoWhatsappServicios' => '+57 300 765 4321',
        'telefonoWhatsappAlmacen' => '+57 300 111 2222',
        'correoNegocio' => 'info@autostok.com',
        'correoCallCenter' => 'callcenter@autostok.com'
    ],
    'nosotros' => [
        'descripcionNosotros' => 'Somos una empresa líder en el sector automotriz, comprometidos con la excelencia en cada servicio que ofrecemos.',
        'anosExperiencia' => '10',
        'clientesSatisfechos' => '500',
        'vehiculosVendidos' => '1000'
    ],
    'sucursales' => [
        'sucursal1' => [
            'nombre' => 'Sucursal Norte',
            'direccion' => 'Calle 123 #45-67, Itagüí, Antioquia',
            'telefono' => '+57 300 123 4567',
            'whatsapp' => '+57 300 123 4567',
            'correo' => 'norte@autostok.com',
            'horarioSemana' => '8:00 AM - 6:00 PM',
            'horarioSabado' => '9:00 AM - 2:00 PM',
            'mapa' => ''
        ],
        'sucursal2' => [
            'nombre' => 'Sucursal Sur',
            'direccion' => 'Carrera 45 #12-34, Itagüí, Antioquia',
            'telefono' => '+57 300 765 4321',
            'whatsapp' => '+57 300 765 4321',
            'correo' => 'sur@autostok.com',
            'horarioSemana' => '8:00 AM - 6:00 PM',
            'horarioSabado' => '9:00 AM - 2:00 PM',
            'mapa' => ''
        ]
    ]
];

// Guardar archivos
$archivos = [
    'vehiculos.json' => $vehiculos,
    'servicios.json' => $servicios,
    'productos.json' => $productos,
    'citas.json' => $citas,
    'solicitudes.json' => $solicitudes,
    'usuarios.json' => $usuarios,
    'configuracion.json' => $configuracion
];

foreach ($archivos as $nombre => $datos) {
    $ruta = 'data/' . $nombre;
    if (file_put_contents($ruta, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        $mensaje[] = "✅ $nombre creado exitosamente";
    } else {
        $mensaje[] = "❌ Error al crear $nombre";
    }
}

// Verificar permisos
$permisos = [];
$permisos['data/'] = is_writable('data/') ? '✅ Escritura permitida' : '❌ Sin permisos de escritura';
$permisos['uploads/'] = is_writable('uploads/') ? '✅ Escritura permitida' : '❌ Sin permisos de escritura';
if (file_exists('admin/api')) {
    $permisos['admin/api/'] = is_writable('admin/api/') ? '✅ Escritura permitida' : '❌ Sin permisos de escritura';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Inicial - Autostok</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #000 0%, #1a1a1a 100%);
            color: #fff;
            padding: 30px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: rgba(255,215,0,0.05);
            border: 2px solid rgba(255,215,0,0.3);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(255,215,0,0.2);
        }
        h1 {
            color: #FFD700;
            font-size: 2.8rem;
            margin-bottom: 10px;
            text-align: center;
            text-shadow: 0 0 20px rgba(255,215,0,0.5);
        }
        .subtitle {
            text-align: center;
            color: rgba(255,255,255,0.7);
            margin-bottom: 40px;
            font-size: 1.2rem;
        }
        .success-icon {
            text-align: center;
            font-size: 5rem;
            margin-bottom: 20px;
            animation: bounce 1s ease infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .results {
            background: rgba(0,0,0,0.6);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .results h2 {
            color: #FFD700;
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .result-item {
            padding: 12px;
            margin-bottom: 10px;
            background: rgba(255,215,0,0.05);
            border-left: 4px solid #FFD700;
            border-radius: 5px;
            font-size: 1.05rem;
        }
        .permisos {
            background: rgba(0,0,0,0.6);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .permisos h2 {
            color: #FFD700;
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .permiso-item {
            padding: 12px;
            margin-bottom: 10px;
            background: rgba(255,215,0,0.05);
            border-radius: 5px;
            font-size: 1.05rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .credentials-box {
            background: linear-gradient(135deg, rgba(255,215,0,0.15), rgba(255,165,0,0.15));
            border: 2px solid #FFD700;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        .credentials-box h2 {
            color: #FFD700;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }
        .credential-item {
            background: rgba(0,0,0,0.5);
            padding: 15px 25px;
            border-radius: 10px;
            margin: 15px 0;
            font-size: 1.2rem;
        }
        .credential-item strong {
            color: #FFD700;
            display: inline-block;
            min-width: 150px;
            text-align: left;
        }
        .credential-item code {
            background: rgba(255,215,0,0.2);
            padding: 8px 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 1.3rem;
            color: #fff;
            border: 1px solid #FFD700;
        }
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255,215,0,0.4);
        }
        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: 2px solid rgba(255,255,255,0.3);
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        .warning {
            background: rgba(255,50,50,0.1);
            border: 2px solid rgba(255,50,50,0.4);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            text-align: center;
        }
        .warning h3 {
            color: #ff5555;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .info-box {
            background: rgba(0,150,255,0.1);
            border: 2px solid rgba(0,150,255,0.3);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
        }
        .info-box h3 {
            color: #0af;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }
        .info-box ul {
            list-style: none;
            padding: 0;
        }
        .info-box li {
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,150,255,0.2);
            font-size: 1.05rem;
        }
        .info-box li:last-child {
            border-bottom: none;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        .status-success {
            background: rgba(76,175,80,0.3);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        .status-empty {
            background: rgba(100,100,100,0.3);
            color: #999;
            border: 1px solid #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">🚗</div>
        <h1>Autostok</h1>
        <p class="subtitle">Sistema Configurado Exitosamente</p>

        <div class="credentials-box">
            <h2>🔐 Credenciales de Acceso</h2>
            <p style="margin-bottom: 20px; color: rgba(255,255,255,0.8);">
                Usa estas credenciales para acceder al panel administrativo
            </p>
            <div class="credential-item">
                <strong>Usuario:</strong>
                <code>superadmin</code>
            </div>
            <div class="credential-item">
                <strong>Contraseña:</strong>
                <code>Admin123</code>
            </div>
            <div style="margin-top: 20px; padding: 15px; background: rgba(255,215,0,0.1); border-radius: 10px; font-size: 0.95rem;">
                <strong style="color: #FFD700;">⚠️ Importante:</strong> Cambia la contraseña después del primer acceso
            </div>
        </div>

        <div class="results">
            <h2>📦 Archivos Creados</h2>
            <?php foreach ($mensaje as $msg): ?>
                <div class="result-item"><?php echo $msg; ?></div>
            <?php endforeach; ?>
        </div>

        <div class="permisos">
            <h2>🔒 Permisos de Directorios</h2>
            <?php foreach ($permisos as $dir => $estado): ?>
                <div class="permiso-item">
                    <strong><?php echo $dir; ?></strong>
                    <span><?php echo $estado; ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="info-box">
            <h3>📋 Estado del Sistema:</h3>
            <ul>
                <li>
                    <strong>Vehículos:</strong> 
                    <span class="status-badge status-empty">0 registros</span>
                    Sistema listo para agregar vehículos
                </li>
                <li>
                    <strong>Servicios:</strong> 
                    <span class="status-badge status-empty">0 registros</span>
                    Sistema listo para agregar servicios
                </li>
                <li>
                    <strong>Productos:</strong> 
                    <span class="status-badge status-empty">0 registros</span>
                    Sistema listo para agregar productos
                </li>
                <li>
                    <strong>Usuarios:</strong> 
                    <span class="status-badge status-success">1 Super Admin</span>
                    Sistema de usuarios activo
                </li>
                <li>
                    <strong>Citas:</strong> 
                    <span class="status-badge status-empty">0 pendientes</span>
                    Sistema de citas operativo
                </li>
                <li>
                    <strong>Solicitudes:</strong> 
                    <span class="status-badge status-empty">0 pendientes</span>
                    Sistema de solicitudes operativo
                </li>
                <li>
                    <strong>Configuración:</strong> 
                    <span class="status-badge status-success">Completa</span>
                    Sucursales y parámetros configurados
                </li>
            </ul>
        </div>

        <div class="actions">
            <a href="admin/login.php" class="btn btn-primary">
                🔐 Acceder al Panel Admin
            </a>
            <a href="index.php" class="btn btn-secondary">
                🌐 Ver Sitio Web
            </a>
        </div>

        <div class="warning">
            <h3>⚠️ IMPORTANTE - SEGURIDAD</h3>
            <p style="font-size: 1.1rem; margin: 15px 0;">
                <strong>Elimina este archivo (setup.php) inmediatamente</strong> después de verificar que todo funciona correctamente.
            </p>
            <p style="margin-top: 15px; color: rgba(255,255,255,0.7);">
                Dejar este archivo en el servidor representa un riesgo de seguridad.<br>
                Cualquier persona podría ejecutarlo y reiniciar tu base de datos.
            </p>
            <div style="margin-top: 20px; padding: 15px; background: rgba(0,0,0,0.3); border-radius: 10px;">
                <strong>Para eliminar:</strong><br>
                Ejecuta en tu servidor: <code style="background: rgba(0,0,0,0.5); padding: 5px 10px; border-radius: 5px;">rm setup.php</code><br>
                O elimínalo manualmente desde tu FTP/cPanel
            </div>
        </div>

        <div class="info-box" style="margin-top: 30px;">
            <h3>🎯 Próximos Pasos:</h3>
            <ul>
                <li><strong>1.</strong> Accede al panel de administración con las credenciales proporcionadas</li>
                <li><strong>2.</strong> Cambia la contraseña del super administrador</li>
                <li><strong>3.</strong> Configura los datos de tu negocio (nombre, teléfonos, dirección)</li>
                <li><strong>4.</strong> Agrega tus primeros vehículos, servicios y productos</li>
                <li><strong>5.</strong> Crea usuarios adicionales según necesites (vendedores, taller, etc.)</li>
                <li><strong>6.</strong> Configura las sucursales con ubicaciones reales</li>
                <li><strong>7.</strong> <strong style="color: #ff5555;">¡ELIMINA este archivo setup.php!</strong></li>
            </ul>
        </div>
    </div>

    <script>
        // Auto-scroll suave al cargar
        window.addEventListener('load', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>