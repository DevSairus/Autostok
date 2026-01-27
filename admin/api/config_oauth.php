<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(0);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_rol'] !== 'super_admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$configFile = __DIR__ . '/../../data/configuracion.json';

try {
    if ($method === 'POST') {
        // Guardar configuración OAuth2
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar JSON');
        }
        
        // Validar campos requeridos
        if (empty($data['tenant_id'])) {
            throw new Exception('Tenant ID es requerido');
        }
        if (empty($data['client_id'])) {
            throw new Exception('Client ID es requerido');
        }
        if (empty($data['from_email'])) {
            throw new Exception('Email del remitente es requerido');
        }
        
        // Cargar configuración actual
        $config = [];
        if (file_exists($configFile)) {
            $configContent = file_get_contents($configFile);
            $config = json_decode($configContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $config = [];
            }
        }
        
        // Mantener client_secret anterior si no se envió uno nuevo
        $clientSecretAnterior = isset($config['oauth']['client_secret']) ? $config['oauth']['client_secret'] : '';
        
        // Actualizar configuración OAuth2
        $config['oauth'] = [
            'tenant_id' => trim($data['tenant_id']),
            'client_id' => trim($data['client_id']),
            'client_secret' => !empty($data['client_secret']) ? $data['client_secret'] : $clientSecretAnterior,
            'from_email' => trim($data['from_email']),
            'from_name' => trim($data['from_name'] ?? 'Auto Stok'),
            'enabled' => isset($data['enabled']) ? (bool)$data['enabled'] : false
        ];
        
        // Crear directorio si no existe
        $dataDir = dirname($configFile);
        if (!file_exists($dataDir)) {
            if (!mkdir($dataDir, 0755, true)) {
                throw new Exception('No se pudo crear el directorio de datos');
            }
        }
        
        // Guardar
        $jsonContent = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $guardado = file_put_contents($configFile, $jsonContent);
        
        if ($guardado === false) {
            throw new Exception('No se pudo escribir en el archivo de configuración');
        }
        
        // Registrar log
        registrarLog(
            'sistema',
            'configurar_oauth2',
            $_SESSION['admin_username'] ?? 'Admin',
            [
                'from_email' => $data['from_email'],
                'enabled' => $config['oauth']['enabled']
            ]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Configuración OAuth2 guardada correctamente'
        ]);
        
    } elseif ($method === 'GET') {
        // Obtener configuración actual
        $config = [];
        if (file_exists($configFile)) {
            $configContent = file_get_contents($configFile);
            $config = json_decode($configContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $config = [];
            }
        }
        
        $oauth = $config['oauth'] ?? [];
        
        // Ocultar client_secret completo
        $oauthToSend = $oauth;
        if (!empty($oauth['client_secret'])) {
            $oauthToSend['client_secret'] = '';
            $oauthToSend['has_client_secret'] = true;
        } else {
            $oauthToSend['has_client_secret'] = false;
        }
        
        echo json_encode([
            'success' => true,
            'oauth' => $oauthToSend
        ]);
        
    } else {
        throw new Exception('Método no soportado');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit;

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