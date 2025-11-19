<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if(!$data){
  echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
  exit;
}

file_put_contents('config_pse.json', json_encode($data, JSON_PRETTY_PRINT));
echo json_encode(['status' => 'ok', 'message' => 'Configuración guardada correctamente']);
?>
