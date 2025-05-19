<?php
require_once "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $size = $_POST['size'];
    $price = $_POST['price'];

    // Validación básica
    if (!$id || !$size || !$price) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        exit;
    }

    // Obtiene precios actuales
    $stmt = $conn->prepare("SELECT prices FROM sneakers WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(["success" => false, "message" => "Zapatilla no encontrada"]);
        exit;
    }

    $prices = json_decode($row["prices"], true);
    $prices[$size] = $price;

    // Actualiza el JSON de precios
    $stmt = $conn->prepare("UPDATE sneakers SET prices = ? WHERE id = ?");
    $success = $stmt->execute([json_encode($prices), $id]);

    echo json_encode(["success" => $success]);
}
