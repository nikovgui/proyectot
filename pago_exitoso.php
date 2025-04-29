<?php
session_start();

require 'vendor/autoload.php';

use Transbank\Webpay\WebpayPlus\Transaction;

// Conectar a la base de datos
$conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$token = $_GET['token_ws'] ?? null;

if (!$token) {
    echo "<h1>Error</h1><p>No se recibió token de Webpay.</p>";
    exit;
}

try {
    // Confirmar la transacción con Webpay
    $transaction = new Transaction();
    $response = $transaction->commit($token);

    if ($response && isset($response->buyOrder, $response->amount) && $response->isApproved()) {
        // **Recuperar datos del usuario**
        if (!isset($_SESSION['compra'])) {
            echo "<h1>Error</h1><p>No se encontraron los datos de la compra.</p>";
            exit;
        }

        $compra = $_SESSION['compra'];

        // **Obtener datos del producto comprado**
        $stmt = $conn->prepare("SELECT id, name, images FROM sneakers WHERE id = :id");
        $stmt->execute(['id' => $compra['producto_id']]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            echo "<h1>Pago exitoso</h1>";
            echo "<p>Producto no encontrado en la base de datos.</p>";
            exit;
        }

        // **Decodificar imágenes correctamente**
        $producto["images"] = json_decode(stripslashes($producto["images"]), true);

        // **Generar número de boleta aleatorio**
        $numeroBoleta = strtoupper(substr(md5(uniqid()), 0, 10));

        // **Calcular fecha estimada de entrega (+14 días)**
        $fechaEntrega = date("Y-m-d", strtotime("+14 days"));

        // **Registrar la boleta en la base de datos**
        $stmt = $conn->prepare("INSERT INTO boletas (numero_boleta, nombre, apellido, direccion, email, telefono, producto_id, monto_pagado, fecha_entrega) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $numeroBoleta,
            $compra['nombre'],
            $compra['apellido'],
            $compra['direccion'],
            $compra['email'],
            $compra['telefono'],
            $compra['producto_id'],
            $compra['precio'], 
            $fechaEntrega
        ]);

        // **Registrar el seguimiento del pedido automáticamente**
        $stmt = $conn->prepare("INSERT INTO seguimiento_pedidos (numero_boleta, estado) VALUES (?, 'Pedido recibido')");
        $stmt->execute([$numeroBoleta]);

        // **Mostrar detalles del pago exitoso**
        echo "<h1>¡Pago exitoso!</h1>";
        echo "<p><strong>Número de boleta:</strong> $numeroBoleta</p>";
        echo "<p><strong>Producto comprado:</strong> " . htmlspecialchars($producto['name']) . "</p>";
        
        // **Mostrar la imagen de la zapatilla**
        if (!empty($producto["images"])) {
            echo "<img src='" . htmlspecialchars($producto['images'][0]) . "' alt='Imagen de la zapatilla' width='200'>";
        } else {
            echo "<p>No se encontró imagen para este producto.</p>";
        }

        echo "<p><strong>Nombre:</strong> " . htmlspecialchars($compra['nombre']) . "</p>";
        echo "<p><strong>Apellido:</strong> " . htmlspecialchars($compra['apellido']) . "</p>";
        echo "<p><strong>Dirección:</strong> " . htmlspecialchars($compra['direccion']) . "</p>";
        echo "<p><strong>Correo:</strong> " . htmlspecialchars($compra['email']) . "</p>";
        echo "<p><strong>Teléfono:</strong> " . htmlspecialchars($compra['telefono']) . "</p>";
        echo "<p><strong>Monto pagado:</strong> $" . number_format($compra['precio'], 0, ',', '.') . "</p>";
        echo "<p><strong>Fecha estimada de entrega:</strong> $fechaEntrega</p>";
        echo "<a href='home.html'>Volver a la tienda</a>";
        echo "<a href='generar_pdf.php?boleta=$numeroBoleta' target='_blank'>Descargar comprobante</a>";

    } else {
        echo "<h1>Pago rechazado</h1>";
        echo "<p>Lo sentimos, tu pago no fue aprobado.</p>";
        echo "<a href='index.html'>Volver a intentar</a>";
    }

} catch (Exception $e) {
    echo "<h1>Error</h1><p>Error al procesar el pago: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
