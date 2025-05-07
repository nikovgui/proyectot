<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/vendor/autoload.php';
use Transbank\Webpay\WebpayPlus\Transaction;

// 📌 **Recuperar datos enviados desde `formulario_pago.html`**
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!isset($data["carrito"]) || empty($data["carrito"])) {
    error_log("❌ Error: No se recibieron productos para procesar el pago.");
    echo json_encode(["error" => "No se recibieron productos para procesar el pago."]);
    exit;
}

// 📌 **Verificar que los datos de usuario estén presentes**
$camposUsuario = ["nombre", "apellido", "direccion", "email", "telefono"];
foreach ($camposUsuario as $campo) {
    if (!isset($data[$campo]) || empty($data[$campo])) {
        error_log("❌ Error: Faltan datos del usuario ('$campo').");
        echo json_encode(["error" => "Faltan datos del usuario."]);
        exit;
    }
}

// 📌 **Calcular el total de la compra sumando los productos**
$totalCompra = array_reduce($data["carrito"], function ($sum, $producto) {
    return $sum + intval($producto["precio"]);
}, 0);

if ($totalCompra <= 0) {
    error_log("❌ Error: Total de compra inválido.");
    echo json_encode(["error" => "El total de la compra es inválido."]);
    exit;
}

// 📌 **Guardar datos en sesión**
$_SESSION["compra"] = [
    "nombre" => htmlspecialchars($data["nombre"]),
    "apellido" => htmlspecialchars($data["apellido"]),
    "direccion" => htmlspecialchars($data["direccion"]),
    "email" => htmlspecialchars($data["email"]),
    "telefono" => htmlspecialchars($data["telefono"]),
    "productos" => $data["carrito"],
    "total" => $totalCompra
];

error_log("📌 Compra guardada en sesión correctamente: " . json_encode($_SESSION["compra"]));

// 📌 **Generar número de pedido aleatorio**
$buyOrder = strtoupper(substr(md5(uniqid()), 0, 10));
$sessionId = uniqid("sess_", true);
$returnUrl = "http://localhost/sneaker_store/pago_exitoso.php";

try {
    // 📌 **Iniciar la transacción con Webpay**
    $transaction = new Transaction();
    $response = $transaction->create($buyOrder, $sessionId, $totalCompra, $returnUrl);

    if ($response && isset($response->url, $response->token)) {
        echo json_encode([
            "url" => $response->url,
            "token" => $response->token
        ]);
    } else {
        error_log("❌ Error en Webpay: La respuesta no contiene un token válido.");
        echo json_encode(["error" => "Error al iniciar el pago."]);
    }

} catch (Exception $e) {
    error_log("❌ Error en Webpay: " . $e->getMessage());
    echo json_encode(["error" => "Error en Webpay: " . htmlspecialchars($e->getMessage())]);
}

?>
