<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'vendor/autoload.php';

use Transbank\Webpay\WebpayPlus\Transaction;

// Recuperar datos del formulario
$idProducto = $_POST['id_producto'] ?? null;
$precio = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_INT);
$nombre = $_POST['nombre'] ?? null;
$apellido = $_POST['apellido'] ?? null;
$direccion = $_POST['direccion'] ?? null;
$email = filter_var($_POST['email'] ?? null, FILTER_VALIDATE_EMAIL);
$telefono = $_POST['telefono'] ?? null;

if (!$idProducto || !$precio || !$nombre || !$apellido || !$direccion || !$email || !$telefono) {
    die(json_encode(["error" => "Faltan datos para procesar el pago."]));
}

// **Guardar datos en sesión para recuperarlos en `pago_exitoso.php`**
$_SESSION['compra'] = [
    'nombre' => $nombre,
    'apellido' => $apellido,
    'direccion' => $direccion,
    'email' => $email,
    'telefono' => $telefono,
    'producto_id' => $idProducto,
    'precio' => $precio // Guardar el precio seleccionado
];

// Generar número de pedido aleatorio
$buyOrder = strtoupper(substr(md5(uniqid()), 0, 10));
$sessionId = uniqid("sess_", true);
$returnUrl = "http://localhost:8000/pago_exitoso.php";

// Iniciar la transacción con Webpay
$transaction = new Transaction();
$response = $transaction->create($buyOrder, $sessionId, $precio, $returnUrl);

if ($response && isset($response->url, $response->token)) {
    echo json_encode([
        "url" => $response->url,
        "token" => $response->token
    ]);
} else {
    echo json_encode(["error" => "Error al iniciar el pago."]);
}
?>
