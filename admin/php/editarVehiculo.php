<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "utils.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || empty($data["id"])) {
    echo json_encode(["success" => false, "message" => "ID no proporcionado"]);
    exit;
}

$jsonPath = "../../vehiculos/vehiculos.json";
$db = loadJSON($jsonPath);

$found = false;
foreach ($db["vehiculos"] as &$veh) {
    if ($veh["id"] === $data["id"]) {
        $veh = array_merge($veh, $data);
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode(["success" => false, "message" => "Vehículo no encontrado"]);
    exit;
}

saveJSON($jsonPath, $db);
echo json_encode(["success" => true, "message" => "Vehículo actualizado correctamente"]);
?>
