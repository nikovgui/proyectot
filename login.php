<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");


// 📌 **Habilitar errores para depuración**
error_reporting(E_ALL);
ini_set('display_errors', 1); // Mostrar errores en el navegador durante pruebas
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/apache/logs/php_error.log');
ob_start(); // Capturar cualquier salida inesperada de PHP

// 📌 **Conectar a la base de datos**
try {
    $conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    ob_end_clean();
    die(json_encode(["error" => "Error de conexión: " . $e->getMessage()]));
}

// 📌 **Verificar si los datos POST llegan correctamente**
$usuario = isset($_POST["usuario"]) ? trim($_POST["usuario"]) : '';
$password = isset($_POST["password"]) ? trim($_POST["password"]) : '';

if (empty($usuario) || empty($password)) {
    ob_end_clean();
    die(json_encode(["error" => "⚠ Todos los campos son obligatorios."]));
}

// 📌 **Buscar el usuario en la base de datos**
$stmt = $conn->prepare("SELECT id, nombre, username, correo, contrasena FROM usuarios WHERE username = ? OR correo = ?");
$stmt->execute([$usuario, $usuario]);
$usuarioEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuarioEncontrado["username"] === "admin") {
    $_SESSION["admin"] = true; 
}


// 📌 **Verificar la contraseña con password_verify()**
if (!password_verify($password, $usuarioEncontrado["contrasena"])) {
    ob_end_clean();
    die(json_encode(["error" => "⚠ Usuario o contraseña incorrectos."]));
}

// 📌 **Guardar datos en sesión**
$_SESSION["usuario"] = [
    "id" => $usuarioEncontrado["id"],
    "nombre" => $usuarioEncontrado["nombre"],
    "username" => $usuarioEncontrado["username"],
    "correo" => $usuarioEncontrado["correo"]
];

// 📌 **Si el usuario es administrador, establecer $_SESSION["admin"]**
$_SESSION["admin"] = ($usuarioEncontrado["username"] === "admin");

// 📌 **Cerrar el buffer antes de enviar JSON para evitar salida inesperada**
ob_end_clean();
die(json_encode(["success" => true, "admin" => $_SESSION["admin"]]));
?>
