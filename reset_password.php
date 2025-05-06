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

// Obtener token y nueva contraseña
$token = $_POST["token"] ?? '';
$password = $_POST["password"] ?? '';

if (empty($token) || empty($password)) {
    echo json_encode(["error" => "Datos incompletos."]);
    exit;
}

// Buscar el usuario con el token
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE reset_token = ?");
$stmt->execute([$token]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo json_encode(["error" => "Token inválido o expirado."]);
    exit;
}

// Encriptar nueva contraseña
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Actualizar contraseña y borrar el token
$stmt = $conn->prepare("UPDATE usuarios SET contrasena = ?, reset_token = NULL WHERE id = ?");
$stmt->execute([$passwordHash, $usuario["id"]]);

echo json_encode(["message" => "Contraseña actualizada correctamente."]);
?>
