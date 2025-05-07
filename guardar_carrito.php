<?php
session_start();
header("Content-Type: application/json");

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!isset($data["carrito"]) || empty($data["carrito"])) {
    error_log("Error: Carrito vacío en `guardar_carrito.php`.");
    echo json_encode(["success" => false, "error" => "Carrito vacío."]);
    exit;
}

$_SESSION["carrito"] = $data["carrito"];
error_log("Carrito guardado correctamente en sesión.");

echo json_encode(["success" => true]);
?>
