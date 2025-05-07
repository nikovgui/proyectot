<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require __DIR__ . '/vendor/autoload.php';

use Transbank\Webpay\WebpayPlus\Transaction;

// 📌 **Conectar a la base de datos**
try {
    $conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("❌ Error de conexión a la base de datos: " . $e->getMessage());
    die("<h1>Error</h1><p>No se pudo conectar a la base de datos.</p>");
}

// 📌 **Validar que `token_ws` se reciba correctamente**
$token = $_GET['token_ws'] ?? null;
if (!$token) {
    error_log("❌ Error: Token de Webpay no recibido.");
    die("<h1>Error</h1><p>No se recibió el token de Webpay. Intenta nuevamente.</p>");
}

// 📌 **Recuperar datos de sesión**
if (!isset($_SESSION['compra']) || empty($_SESSION['compra'])) {
    error_log("❌ Error: No se encontraron datos de compra en la sesión.");
    die("<h1>Error</h1><p>No se encontraron los datos de compra en la sesión.</p>");
}

$compra = $_SESSION['compra'];
error_log("📌 Datos de la compra en sesión: " . json_encode($compra));

// 📌 **Verificar que los datos de usuario están presentes**
$camposUsuario = ["nombre", "apellido", "direccion", "email", "telefono"];
foreach ($camposUsuario as $campo) {
    if (!isset($compra[$campo])) {
        error_log("⚠ Advertencia: Falta el campo '$campo' en la sesión.");
        $compra[$campo] = "Información no disponible";
    }
}

// 📌 **Confirmar la transacción con Webpay**
try {
    $transaction = new Transaction();
    $response = $transaction->commit($token);

    if (!$response || !$response->isApproved()) {
        error_log("❌ Pago rechazado por Webpay.");
        die("<h1>Pago rechazado</h1><p>Tu pago no fue aprobado.</p>");
    }

    error_log("✅ Pago aprobado con Webpay - Monto: " . $response->amount);

    // 📌 **Generar número de boleta**
    $numeroBoleta = strtoupper(substr(md5(uniqid()), 0, 10));
    $fechaEntrega = date("Y-m-d", strtotime("+14 days"));

    // 📌 **Registrar boleta y productos**
    try {
        foreach ($compra["productos"] as $producto) {
            $stmt = $conn->prepare("INSERT INTO boletas (numero_boleta, nombre, apellido, direccion, email, telefono, producto_id, monto_pagado, fecha_entrega) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $numeroBoleta, $compra['nombre'], $compra['apellido'], $compra['direccion'],
                $compra['email'], $compra['telefono'], $producto['productoId'], $producto['precio'], $fechaEntrega
            ]);
        }
    } catch (PDOException $e) {
        error_log("❌ Error en la base de datos al guardar la boleta: " . $e->getMessage());
        die("<h1>Error</h1><p>No se pudo registrar la boleta en la base de datos.</p>");
    }

    // 📌 **Mostrar detalles del pago exitoso**
    echo "<h1>¡Pago exitoso!</h1>";
    echo "<p><strong>Número de boleta:</strong> $numeroBoleta</p>";
    echo "<p><strong>Nombre:</strong> " . htmlspecialchars($compra['nombre']) . "</p>";
    echo "<p><strong>Apellido:</strong> " . htmlspecialchars($compra['apellido']) . "</p>";
    echo "<p><strong>Dirección:</strong> " . htmlspecialchars($compra['direccion']) . "</p>";
    echo "<p><strong>Correo:</strong> " . htmlspecialchars($compra['email']) . "</p>";
    echo "<p><strong>Teléfono:</strong> " . htmlspecialchars($compra['telefono']) . "</p>";
    echo "<p><strong>Fecha estimada de entrega:</strong> $fechaEntrega</p>";

    echo "<h2>Productos Comprados:</h2>";
    foreach ($compra["productos"] as $producto) {
        echo "<p><strong>Producto ID:</strong> " . htmlspecialchars($producto['productoId']) . "</p>";
        echo "<p><strong>Precio:</strong> $" . number_format((float)$producto['precio'], 0, ',', '.') . "</p>";
    }

    echo "<a href='http://localhost/sneaker_store/home.html'>Volver a la tienda</a>";
    echo "<a href='http://localhost/sneaker_store/generar_pdf.php?boleta=$numeroBoleta' target='_blank'>Descargar comprobante</a>";

} catch (Exception $e) {
    error_log("❌ Error en Webpay: " . $e->getMessage());
    die("<h1>Error</h1><p>Ocurrió un problema al procesar el pago.</p>");
}
?>
