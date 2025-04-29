<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Conectar a la base de datos
$conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// **Verificar si se envió un número de boleta**
if (!isset($_GET["boleta"])) {
    echo json_encode(["error" => "No se ingresó un número de boleta."]);
    exit;
}

$numero_boleta = $_GET["boleta"];

// **Consultar el estado del pedido**
$stmt = $conn->prepare("SELECT estado, fecha_actualizacion FROM seguimiento_pedidos WHERE numero_boleta = ?");
$stmt->execute([$numero_boleta]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo json_encode(["error" => "No se encontró un pedido con esa boleta."]);
} else {
    echo json_encode($pedido);
}
?>
