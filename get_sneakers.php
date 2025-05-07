<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once "admin_auth.php";


// 📌 **Conectar a la base de datos**
try {
    $conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

// 📌 **Buscar zapatilla por ID si se especifica en la URL**
if (isset($_GET["id"])) {
    $id = intval($_GET["id"]); 
    
    $stmt = $conn->prepare("SELECT id, name, images, prices, sizes FROM sneakers WHERE id = ?");
    $stmt->execute([$id]);
    $sneakerEncontrada = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sneakerEncontrada) {
        // 📌 **Corregir la decodificación de JSON**
        $sneakerEncontrada["images"] = json_decode($sneakerEncontrada["images"], true) ?: [];
        $sneakerEncontrada["prices"] = json_decode($sneakerEncontrada["prices"], true) ?: [];
        $sneakerEncontrada["sizes"] = json_decode($sneakerEncontrada["sizes"], true) ?: [];

        echo json_encode($sneakerEncontrada);
    } else {
        echo json_encode(["error" => "Zapatilla no encontrada en la base de datos."]);
    }
} else {
    // 📌 **Obtener todas las zapatillas**
    $stmt = $conn->query("SELECT id, name, images, prices, sizes FROM sneakers");
    $sneakers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sneakers as &$sneaker) {
        $sneaker["images"] = json_decode($sneaker["images"], true) ?: [];
        $sneaker["prices"] = json_decode($sneaker["prices"], true) ?: [];
        $sneaker["sizes"] = json_decode($sneaker["sizes"], true) ?: [];
    }

    echo json_encode($sneakers);
}
?>
