<?php
/**
 * Script de configuración inicial
 * Ejecutar una sola vez: http://tu-sitio.com/setup.php
 * Después de ejecutar, eliminar este archivo por seguridad
 */

// Crear directorio data si no existe
if (!file_exists('data')) {
    mkdir('data', 0755, true);
    $mensaje[] = "✅ Directorio 'data/' creado";
} else {
    $mensaje[] = "ℹ️ Directorio 'data/' ya existe";
}

// Crear directorio uploads si no existe
if (!file_exists('uploads')) {
    mkdir('uploads', 0755, true);
    $mensaje[] = "✅ Directorio 'uploads/' creado";
} else {
    $mensaje[] = "ℹ️ Directorio 'uploads/' ya existe";
}

// ===== VEHICULOS =====
$vehiculos = [
    'vehiculos' => [
        [
            'id' => 1,
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'anio' => 2023,
            'precio' => 85000000,
            'kilometraje' => 15000,
            'tipo' => 'sedan',
            'transmision' => 'Automática',
            'combustible' => 'Gasolina',
            'color' => 'Blanco',
            'descripcion' => 'Vehículo en excelente estado, único dueño, todos los mantenimientos al día. Incluye garantía extendida y aire acondicionado.',
            'imagenes' => [
                'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=800',
                'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?w=800'
            ]
        ],
        [
            'id' => 2,
            'marca' => 'Mazda',
            'modelo' => 'CX-5',
            'anio' => 2022,
            'precio' => 120000000,
            'kilometraje' => 25000,
            'tipo' => 'suv',
            'transmision' => 'Automática',
            'combustible' => 'Gasolina',
            'color' => 'Rojo',
            'descripcion' => 'SUV espaciosa y confortable, perfecta para la familia. Sistema de navegación, cámara de reversa, sensores de parqueo.',
            'imagenes' => [
                'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?w=800',
                'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=800'
            ]
        ],
        [
            'id' => 3,
            'marca' => 'Chevrolet',
            'modelo' => 'Silverado',
            'anio' => 2021,
            'precio' => 140000000,
            'kilometraje' => 35000,
            'tipo' => 'pickup',
            'transmision' => 'Manual',
            'combustible' => 'Diesel',
            'color' => 'Negro',
            'descripcion' => 'Pickup robusta ideal para trabajo pesado. Doble cabina, capacidad de carga 1 tonelada, tracción 4x4.',
            'imagenes' => [
                'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?w=800'
            ]
        ]
    ]
];

// ===== SERVICIOS =====
$servicios = [
    'servicios' => [
        [
            'id' => 1,
            'nombre' => 'Cambio de Aceite Premium',
            'categoria' => 'Mantenimiento',
            'precio' => 150000,
            'duracion' => '45 minutos',
            'descripcion_corta' => 'Cambio de aceite sintético de alta calidad con filtro premium',
            'descripcion' => 'Servicio completo de cambio de aceite sintético de la más alta calidad. Incluye inspección de niveles, revisión de frenos y presión de llantas. Utilizamos únicamente aceites certificados y filtros originales.',
            'caracteristicas' => [
                'Aceite sintético premium',
                'Filtro de aceite original',
                'Inspección de 21 puntos',
                'Revisión de frenos',
                'Verificación de presión de llantas',
                'Lavado exterior básico incluido'
            ],
            'imagen' => 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?w=800'
        ],
        [
            'id' => 2,
            'nombre' => 'Alineación y Balanceo',
            'categoria' => 'Mantenimiento',
            'precio' => 80000,
            'duracion' => '1 hora',
            'descripcion_corta' => 'Alineación computarizada y balanceo de 4 llantas',
            'descripcion' => 'Servicio de alineación con tecnología láser de última generación. Garantiza el desgaste uniforme de las llantas y una conducción suave y segura.',
            'caracteristicas' => [
                'Alineación láser computarizada',
                'Balanceo de 4 llantas',
                'Ajuste de geometría',
                'Inspección de suspensión',
                'Reporte impreso de alineación'
            ],
            'imagen' => 'https://images.unsplash.com/photo-1625047509168-a7026f36de04?w=800'
        ],
        [
            'id' => 3,
            'nombre' => 'Lavado Completo Detallado',
            'categoria' => 'Lavado',
            'precio' => 120000,
            'duracion' => '2 horas',
            'descripcion_corta' => 'Lavado interior y exterior con tratamiento de pintura',
            'descripcion' => 'Servicio de lavado profesional completo. Incluye lavado exterior con cera, aspirado y limpieza profunda interior, limpieza de motor, brillado de llantas y vidrios.',
            'caracteristicas' => [
                'Lavado exterior con espuma activa',
                'Encerado y pulido',
                'Aspirado completo interior',
                'Limpieza de tapicería',
                'Limpieza de motor',
                'Brillado de llantas y neumáticos',
                'Limpieza de vidrios interior/exterior',
                'Aromatización'
            ],
            'imagen' => 'https://images.unsplash.com/photo-1601362840469-51e4d8d58785?w=800'
        ]
    ]
];

// ===== CITAS =====
$citas = [
    'citas' => []
];

// ===== CONFIGURACION =====
$configuracion = [
    'general' => [
        'nombreNegocio' => 'Autostok',
        'telefonoWhatsappVehiculos' => '+57 300 123 4567',
        'telefonoWhatsappServicios' => '+57 300 765 4321',
        'correoNegocio' => 'info@autostok.com',
        'direccion' => 'Calle 123 #45-67, Itagüí, Antioquia, Colombia'
    ],
    'pagos' => [
        'urlPagos' => 'https://www.psepagos.co/PSEHostingUI/ShowTicketOffice.aspx?ID=2979'
    ],
    'horarios' => [
        'horarioSemana' => '8:00 AM - 6:00 PM',
        'horarioSabado' => '9:00 AM - 2:00 PM'
    ],
    'nosotros' => [
        'descripcionNosotros' => 'Somos una empresa dedicada a ofrecer los mejores vehículos y servicios automotrices, con más de 10 años de experiencia en el mercado.',
        'anosExperiencia' => '10',
        'clientesSatisfechos' => '500',
        'vehiculosVendidos' => '1000'
    ]
];

// Guardar archivos
$archivos = [
    'vehiculos.json' => $vehiculos,
    'servicios.json' => $servicios,
    'citas.json' => $citas,
    'solicitudes.json' => $solicitudes,
    'configuracion.json' => $configuracion
];

$mensaje = [];

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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Inicial - AutoMarket</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Avenir', sans-serif;
            background: linear-gradient(135deg, #000 0%, #1a1a1a 100%);
            color: #fff;
            padding: 30px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255,215,0,0.05);
            border: 2px solid rgba(255,215,0,0.3);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(255,215,0,0.2);
        }
        h1 {
            color: #FFD700;
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-align: center;
            text-shadow: 0 0 20px rgba(255,215,0,0.5);
        }
        .subtitle {
            text-align: center;
            color: rgba(255,255,255,0.7);
            margin-bottom: 40px;
            font-size: 1.1rem;
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
        }
        .permiso-item {
            padding: 12px;
            margin-bottom: 10px;
            background: rgba(255,215,0,0.05);
            border-radius: 5px;
            font-size: 1.05rem;
        }
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
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
            display: inline-block;
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
        }
        .warning {
            background: rgba(255,165,0,0.1);
            border: 2px solid rgba(255,165,0,0.3);
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            text-align: center;
        }
        .warning strong {
            color: #FFA500;
            font-size: 1.2rem;
        }
        .info-box {
            background: rgba(0,150,255,0.1);
            border: 2px solid rgba(0,150,255,0.3);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .info-box h3 {
            color: #0af;
            margin-bottom: 15px;
        }
        .info-box ul {
            list-style: none;
            padding: 0;
        }
        .info-box li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(0,150,255,0.2);
        }
        .info-box li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚗 AutoMarket</h1>
        <p class="subtitle">Configuración Inicial del Sistema</p>

        <div class="results">
            <h2>📦 Archivos Creados</h2>
            <?php foreach ($mensaje as $msg): ?>
                <div class="result-item"><?php echo $msg; ?></div>
            <?php endforeach; ?>
        </div>

        <div class="permisos">
            <h2>🔐 Permisos de Directorios</h2>
            <?php foreach ($permisos as $dir => $estado): ?>
                <div class="permiso-item"><strong><?php echo $dir; ?></strong>: <?php echo $estado; ?></div>
            <?php endforeach; ?>
        </div>

        <div class="info-box">
            <h3>📋 Datos de Ejemplo Creados:</h3>
            <ul>
                <li>✅ 3 vehículos de ejemplo (Toyota Corolla, Mazda CX-5, Chevrolet Silverado)</li>
                <li>✅ 3 servicios de ejemplo (Cambio de Aceite, Alineación, Lavado)</li>
                <li>✅ Archivos de citas y solicitudes listos</li>
                <li>✅ Configuración inicial del sistema</li>
                <li>✅ Sistema de citas listo para usar</li>
            </ul>
        </div>

        <div class="actions">
            <a href="index.php" class="btn btn-primary">Ver Sitio Web</a>
            <a href="catalogo.php" class="btn btn-secondary">Ver Catálogo</a>
            <a href="test_vehiculos.php" class="btn btn-secondary">Test Vehículos</a>
            <a href="admin/login.php" class="btn btn-secondary">Ir al Admin</a>
        </div>

        <div class="warning">
            <strong>⚠️ IMPORTANTE</strong><br>
            Por seguridad, elimina este archivo (setup.php) después de ejecutarlo.<br>
            <br>
            <strong>Credenciales de Administrador:</strong><br>
            Usuario: <code style="background: rgba(0,0,0,0.5); padding: 5px 10px; border-radius: 5px;">admin</code><br>
            Contraseña: <code style="background: rgba(0,0,0,0.5); padding: 5px 10px; border-radius: 5px;">admin123</code><br>
            <br>
            <small>Cambiar en admin/login.php por seguridad</small>
        </div>
    </div>
</body>
</html>