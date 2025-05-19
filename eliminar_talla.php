<?php
require_once "db_connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $size = $_POST['size'] ?? null;

    if (!$id || !$size) {
        echo json_encode(['success' => false, 'message' => 'Faltan parámetros']);
        exit;
    }

    // Obtener la zapatilla
    $stmt = $conn->prepare("SELECT prices FROM sneakers WHERE id = ?");
    $stmt->execute([$id]);
    $sneaker = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sneaker) {
        echo json_encode(['success' => false, 'message' => 'Zapatilla no encontrada']);
        exit;
    }

    $prices = json_decode($sneaker['prices'], true);

    if (!isset($prices[$size])) {
        echo json_encode(['success' => false, 'message' => 'Talla no encontrada']);
        exit;
    }

    // Eliminar la talla
    unset($prices[$size]);

    // Guardar de nuevo en DB
    $updatedPrices = json_encode($prices);
    $updateStmt = $conn->prepare("UPDATE sneakers SET prices = ? WHERE id = ?");
    $success = $updateStmt->execute([$updatedPrices, $id]);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar DB']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
