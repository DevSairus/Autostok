<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "utils.php";

$data = json_decode(file_get_contents("php://input"), true);
$id = $data["id"] ?? null;
if (!$id) {
    echo json_encode(["success" => false, "message" => "ID no proporcionado"]);
    exit;
}

$jsonPath = "../../vehiculos/vehiculos.json";
$db = loadJSON($jsonPath);

$originalCount = count($db["vehiculos"]);
$db["vehiculos"] = array_values(array_filter($db["vehiculos"], fn($v) => $v["id"] !== $id));

if (count($db["vehiculos"]) === $originalCount) {
    echo json_encode(["success" => false, "message" => "Vehículo no encontrado"]);
    exit;
}

saveJSON($jsonPath, $db);
echo json_encode(["success" => true, "message" => "Vehículo eliminado correctamente"]);
?>
