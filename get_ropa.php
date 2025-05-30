<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once "admin_auth.php"; // Descomenta si necesitas autenticación

// Conexión directa (igual que en get_sneakers.php)
try {
    $conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

function procesarRopa($item) {
    // Asegurar que los campos esenciales existan
    $item['id'] = $item['id'] ?? 0;
    $item['name'] = $item['nombre'] ?? '';
    $item['brand'] = $item['marca'] ?? '';
    $item['category'] = $item['categoria'] ?? '';
    $item['size'] = $item['talla'] ?? '';
    $item['color'] = $item['color'] ?? '';
    $item['price'] = $item['precio'] ?? 0;
    $item['image'] = $item['imagen'] ?? 'https://via.placeholder.com/300x300?text=Imagen+no+disponible';
    $item['gender'] = $item['genero'] ?? '';
    $item['material'] = $item['material'] ?? '';
    $item['stock'] = $item['stock'] ?? 0;
    
    return $item;
}

if (isset($_GET["id"])) {
    // Búsqueda por ID
    $id = intval($_GET["id"]);

    $stmt = $conn->prepare("SELECT * FROM ropa WHERE id = ?");
    $stmt->execute([$id]);
    $ropaEncontrada = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ropaEncontrada) {
        $ropaEncontrada = procesarRopa($ropaEncontrada);
        echo json_encode($ropaEncontrada);
    } else {
        echo json_encode(["error" => "Prenda no encontrada en la base de datos."]);
    }
} else {
    // Obtener toda la ropa
    $stmt = $conn->query("SELECT * FROM ropa");
    $ropa = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ropaFormateada = array_map('procesarRopa', $ropa);
    
    echo json_encode($ropaFormateada);
}
?>