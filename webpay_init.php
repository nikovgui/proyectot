<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/vendor/autoload.php';

use Transbank\Webpay\WebpayPlus\Transaction;

// Recuperar datos del formulario
$idProducto = filter_var($_POST['id_producto'] ?? null, FILTER_VALIDATE_INT);
$precio = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_INT);
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$email = filter_var($_POST['email'] ?? null, FILTER_VALIDATE_EMAIL);
$telefono = trim($_POST['telefono'] ?? '');

if (!$idProducto || !$precio || !$nombre || !$apellido || !$direccion || !$email || !$telefono) {
    error_log("Error: Faltan datos para procesar el pago.");
    echo json_encode(["error" => "Faltan datos para procesar el pago."]);
    exit;
}

// **Guardar datos en sesión para recuperarlos en `pago_exitoso.php`**
$_SESSION['compra'] = [
    'nombre' => htmlspecialchars($nombre),
    'apellido' => htmlspecialchars($apellido),
    'direccion' => htmlspecialchars($direccion),
    'email' => htmlspecialchars($email),
    'telefono' => htmlspecialchars($telefono),
    'producto_id' => $idProducto,
    'precio' => $precio // Guardar el precio seleccionado
];

// **Verificar que los datos de la sesión fueron guardados correctamente**
error_log("Datos guardados en sesión: " . json_encode($_SESSION['compra']));

// Generar número de pedido aleatorio
$buyOrder = strtoupper(substr(md5(uniqid()), 0, 10));
$sessionId = uniqid("sess_", true);
$returnUrl = "http://localhost/sneaker_store/pago_exitoso.php"; // RUTA CORREGIDA

try {
    // Iniciar la transacción con Webpay
    $transaction = new Transaction();
    $response = $transaction->create($buyOrder, $sessionId, $precio, $returnUrl);

    if ($response && isset($response->url, $response->token)) {
        echo json_encode([
            "url" => $response->url,
            "token" => $response->token
        ]);
    } else {
        error_log("Error en Webpay: La respuesta no contiene un token válido.");
        echo json_encode(["error" => "Error al iniciar el pago."]);
    }

} catch (Exception $e) {
    error_log("Error en Webpay: " . $e->getMessage());
    echo json_encode(["error" => "Error en Webpay: " . htmlspecialchars($e->getMessage())]);
}
?>
