<?php
session_start();
header("Content-Type: application/json");

// 📌 **Habilitar errores para depuración**
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/apache/logs/php_error.log');
ob_start(); // Capturar cualquier salida inesperada de PHP

try {
    $conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo json_encode(["success" => "Conexión correcta"]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
}
exit;


// 📌 **Verificar si los datos POST llegan correctamente**
$usuario = isset($_POST["usuario"]) ? trim($_POST["usuario"]) : '';
$password = isset($_POST["password"]) ? trim($_POST["password"]) : '';

if (empty($usuario) || empty($password)) {
    ob_end_clean();
    echo json_encode(["error" => "⚠ Todos los campos son obligatorios.", "debug" => $_POST]);
    exit;
}

// 📌 **Buscar el usuario en la base de datos**
$stmt = $conn->prepare("SELECT id, nombre, username, correo, contrasena FROM usuarios WHERE username = ? OR correo = ?");
$stmt->execute([$usuario, $usuario]);
$usuarioEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuarioEncontrado || !password_verify($password, $usuarioEncontrado["contrasena"])) {
    ob_end_clean();
    echo json_encode(["error" => "⚠ Usuario o contraseña incorrectos."]);
    exit;
}

// 📌 **Guardar datos en sesión**
$_SESSION["usuario"] = [
    "id" => $usuarioEncontrado["id"],
    "nombre" => $usuarioEncontrado["nombre"],
    "username" => $usuarioEncontrado["username"],
    "correo" => $usuarioEncontrado["correo"]
];

ob_end_clean();
echo json_encode(["success" => true]);
?>
