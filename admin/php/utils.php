<?php
function loadJSON($path) {
    if (!file_exists($path)) return ["vehiculos" => []];
    $json = file_get_contents($path);
    return json_decode($json, true);
}

function saveJSON($path, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($path, $json);
}
?>
