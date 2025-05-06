<?php
session_start();
header("Content-Type: application/json");

// Conectar a la base de datos
try {
    $conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error de conexión a la base de datos."]);
    exit;
}

// Obtener datos del usuario
$correo = $_POST["correo"] ?? '';
$password = $_POST["password"] ?? '';

if (empty($correo) || empty($password)) {
    echo json_encode(["error" => "Todos los campos son obligatorios."]);
    exit;
}

// Buscar el usuario en la base de datos
$stmt = $conn->prepare("SELECT id, nombre, correo, contrasena FROM usuarios WHERE correo = ?");
$stmt->execute([$correo]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || !password_verify($password, $usuario["contrasena"])) {
    echo json_encode(["error" => "Correo o contraseña incorrectos."]);
    exit;
}

// Guardar datos en sesión
$_SESSION["usuario"] = [
    "id" => $usuario["id"],
    "nombre" => $usuario["nombre"],
    "correo" => $usuario["correo"]
];

echo json_encode(["success" => true]);
?>
