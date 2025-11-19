<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "utils.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["success" => false, "message" => "Datos inválidos"]);
    exit;
}

$jsonPath = "../../vehiculos/vehiculos.json";
$db = loadJSON($jsonPath);

$id = "veh" . str_pad(rand(1, 9999), 3, "0", STR_PAD_LEFT);
$vehiculo = [
    "id" => $id,
    "marca" => $data["marca"] ?? "",
    "modelo" => $data["modelo"] ?? "",
    "anio" => intval($data["anio"] ?? 0),
    "precio" => floatval($data["precio"] ?? 0),
    "categoria" => $data["categoria"] ?? "",
    "tipo" => $data["tipo"] ?? "",
    "transmision" => $data["transmision"] ?? "",
    "combustible" => $data["combustible"] ?? "",
    "color" => $data["color"] ?? "",
    "kilometraje" => intval($data["kilometraje"] ?? 0),
    "placa" => $data["placa"] ?? "",
    "ciudad" => $data["ciudad"] ?? "",
    "descripcion" => $data["descripcion"] ?? "",
    "imagenes" => $data["imagenes"] ?? [],
    "whatsapp" => $data["whatsapp"] ?? ""
];

$db["vehiculos"][] = $vehiculo;
saveJSON($jsonPath, $db);

echo json_encode(["success" => true, "message" => "Vehículo agregado correctamente", "vehiculo" => $vehiculo]);
?>
