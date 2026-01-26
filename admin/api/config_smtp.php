<?php
session_start();
header('Content-Type: application/json');

// Habilitar reporte de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar en pantalla, solo en JSON

// Verificar autenticación de super admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_rol'] !== 'super_admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado. Solo super_admin puede modificar esta configuración.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$configFile = __DIR__ . '/../../data/configuracion.json';

try {
    if ($method === 'POST') {
        // Guardar configuración de correo
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }
        
        // Validar datos requeridos
        if (empty($data['host'])) {
            throw new Exception('El servidor SMTP es requerido');
        }
        if (empty($data['username'])) {
            throw new Exception('El usuario SMTP es requerido');
        }
        if (empty($data['port'])) {
            throw new Exception('El puerto SMTP es requerido');
        }
        
        // Cargar configuración actual
        $config = [];
        if (file_exists($configFile)) {
            $configContent = file_get_contents($configFile);
            $config = json_decode($configContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Si hay error, iniciar config vacía
                $config = [];
            }
        }
        
        // Si no existe la sección smtp, crearla
        if (!isset($config['smtp'])) {
            $config['smtp'] = [];
        }
        
        // Mantener contraseña anterior si no se envió una nueva
        $passwordAnterior = $config['smtp']['password'] ?? '';
        
        // Actualizar configuración de correo
        $config['smtp'] = [
            'host' => trim($data['host']),
            'port' => intval($data['port']),
            'encryption' => $data['encryption'] ?? 'tls',
            'username' => trim($data['username']),
            'password' => !empty($data['password']) ? $data['password'] : $passwordAnterior,
            'from_name' => trim($data['from_name'] ?? 'Auto Stok'),
            'enabled' => isset($data['enabled']) ? (bool)$data['enabled'] : false
        ];
        
        // Crear directorio si no existe
        $dataDir = dirname($configFile);
        if (!file_exists($dataDir)) {
            if (!mkdir($dataDir, 0755, true)) {
                throw new Exception('No se pudo crear el directorio: ' . $dataDir);
            }
        }
        
        // Verificar permisos de escritura
        if (file_exists($configFile) && !is_writable($configFile)) {
            throw new Exception('El archivo configuracion.json no tiene permisos de escritura');
        }
        
        if (!file_exists($configFile) && !is_writable($dataDir)) {
            throw new Exception('El directorio data/ no tiene permisos de escritura');
        }
        
        // Guardar
        $jsonContent = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $guardado = file_put_contents($configFile, $jsonContent);
        
        if ($guardado === false) {
            throw new Exception('No se pudo escribir en el archivo de configuración. Verifica permisos del archivo y directorio.');
        }
        
        // Registrar log
        registrarLog(
            'sistema',
            'configurar_smtp',
            $_SESSION['admin_username'] ?? 'Admin',
            [
                'smtp_host' => $data['host'], 
                'smtp_username' => $data['username'],
                'enabled' => $config['smtp']['enabled']
            ]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Configuración de correo guardada correctamente',
            'debug' => [
                'file' => $configFile,
                'exists' => file_exists($configFile),
                'writable' => is_writable($configFile),
                'bytes_written' => $guardado
            ]
        ]);
        
    } elseif ($method === 'GET') {
        // Obtener configuración actual (sin mostrar contraseña completa)
        $config = [];
        if (file_exists($configFile)) {
            $configContent = file_get_contents($configFile);
            $config = json_decode($configContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $config = [];
            }
        }
        
        $smtp = $config['smtp'] ?? [];
        
        // Ocultar contraseña real
        $smtpToSend = $smtp;
        if (!empty($smtp['password'])) {
            $smtpToSend['password'] = ''; // No enviar contraseña al cliente
            $smtpToSend['has_password'] = true;
        } else {
            $smtpToSend['has_password'] = false;
        }
        
        echo json_encode([
            'success' => true,
            'smtp' => $smtpToSend,
            'debug' => [
                'file_exists' => file_exists($configFile),
                'file_path' => $configFile
            ]
        ]);
        
    } else {
        throw new Exception('Método no soportado: ' . $method);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'method' => $method,
            'config_file' => $configFile,
            'file_exists' => file_exists($configFile),
            'data_dir_exists' => file_exists(dirname($configFile)),
            'data_dir_writable' => is_writable(dirname($configFile))
        ]
    ]);
}

function registrarLog($tipo, $accion, $usuario, $datos) {
    $logFile = __DIR__ . '/../../data/logs.json';
    $logsData = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : ['logs' => []];
    
    if (!is_array($logsData)) {
        $logsData = ['logs' => []];
    }
    if (!isset($logsData['logs']) || !is_array($logsData['logs'])) {
        $logsData['logs'] = [];
    }
    
    $logsData['logs'][] = [
        'fecha' => date('Y-m-d H:i:s'),
        'tipo' => $tipo,
        'accion' => $accion,
        'usuario' => $usuario,
        'datos' => $datos,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    if (count($logsData['logs']) > 500) {
        $logsData['logs'] = array_slice($logsData['logs'], -500);
    }
    
    @file_put_contents($logFile, json_encode($logsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}