<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once "admin_auth.php";

try {
    $conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

function procesarSneaker($sneaker) {
    // Decodificar imágenes
    $sneaker["images"] = json_decode($sneaker["images"], true);
    if (!is_array($sneaker["images"])) $sneaker["images"] = [];

    // Decodificar precios
    $sneaker["prices"] = json_decode($sneaker["prices"], true);
    if (!is_array($sneaker["prices"])) $sneaker["prices"] = [];

    // Decodificar tallas
    $sneaker["sizes"] = json_decode($sneaker["sizes"], true);
    if (!is_array($sneaker["sizes"])) $sneaker["sizes"] = [];

  
    // Si por alguna razón sizes está vacío pero prices no,
    // rellenamos sizes con las claves de prices
    if (empty($sneaker["sizes"]) && !empty($sneaker["prices"])) {
        $sneaker["sizes"] = array_keys($sneaker["prices"]);
    }
    

    return $sneaker;
}

if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);

    $stmt = $conn->prepare("SELECT id, name, images, prices, sizes FROM sneakers WHERE id = ?");
    $stmt->execute([$id]);
    $sneakerEncontrada = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sneakerEncontrada) {
        $sneakerEncontrada = procesarSneaker($sneakerEncontrada);
        echo json_encode($sneakerEncontrada);
    } else {
        echo json_encode(["error" => "Zapatilla no encontrada en la base de datos."]);
    }
} else {
    $stmt = $conn->query("SELECT id, name, images, prices, sizes FROM sneakers");
    $sneakers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sneakers as &$sneaker) {
        $sneaker = procesarSneaker($sneaker);
    }

    echo json_encode($sneakers);
}
?>
