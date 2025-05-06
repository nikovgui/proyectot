<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Conectar a la base de datos
try {
    $conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

// **Insertar zapatillas en la base de datos si no existen**
$sneakers = [
    [
        "name" => "Jordan 4 Black Cat",
        "images" => json_encode([
            "http://localhost/img/bc1.jpg",
            "http://localhost/img/bc2.jpg",
            "http://localhost/img/bc3.jpg",
            "http://localhost/img/bc4.jpg",
            "http://localhost/img/bc5.jpg"
        ]),
        "prices" => json_encode([
            "7" => 200,
            "8" => 210,
            "9" => 220,
            "13" => 250
        ])
    ],
    [
        "name" => "Jordan 4 Retro Bred",
        "images" => json_encode([
            "http://localhost/img/bred1.jpg",
            "http://localhost/img/bred2.jpg",
            "http://localhost/img/bred3.jpg",
            "http://localhost/img/bred4.jpg",
        ]),
        "prices" => json_encode([
            "7" => 230,
            "8" => 240,
            "9" => 250
        ])
    ],
    [
        "name" => "Nike Air Force 1 x Louis Vuitton",
        "images" => json_encode([
            "http://localhost/img/nikelv1.jpg",
            "http://localhost/img/nikelv2.jpg",
            "http://localhost/img/nikelv3.jpg",
            "http://localhost/img/nikelv4.jpg",
        ]),
        "prices" => json_encode([
            "7" => 7700,
            "8" => 10000
        ])
    ]
];

foreach ($sneakers as $sneaker) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM sneakers WHERE name = ?");
    $stmt->execute([$sneaker['name']]);
    $exists = $stmt->fetchColumn();

    if ($exists == 0) {
        $stmt = $conn->prepare("INSERT INTO sneakers (name, images, prices) VALUES (?, ?, ?)");
        $stmt->execute([$sneaker['name'], $sneaker['images'], $sneaker['prices']]);
    }
}

// **Buscar zapatilla por ID si se especifica en la URL**
if (isset($_GET["id"])) {
    $id = intval($_GET["id"]); 
    
    $stmt = $conn->prepare("SELECT id, name, images, prices FROM sneakers WHERE id = ?");
    $stmt->execute([$id]);
    $sneakerEncontrada = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sneakerEncontrada) {
        // **Corregir la decodificación de imágenes eliminando las barras invertidas**
        $sneakerEncontrada["images"] = json_decode(stripslashes($sneakerEncontrada["images"]), true);
        $sneakerEncontrada["prices"] = json_decode($sneakerEncontrada["prices"], true);
        echo json_encode($sneakerEncontrada);
    } else {
        echo json_encode(["error" => "Zapatilla no encontrada en la base de datos."]);
    }
} else {
    // **Obtener todas las zapatillas**
    $stmt = $conn->query("SELECT id, name, images, prices FROM sneakers");
    $sneakers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sneakers as &$sneaker) {
        $sneaker["images"] = json_decode(stripslashes($sneaker["images"]), true);
        $sneaker["prices"] = json_decode($sneaker["prices"], true);
    }

    echo json_encode($sneakers);
}
?>
