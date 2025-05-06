<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"] ?? '');
    $correo = trim($_POST["correo"] ?? '');
    $fecha_nacimiento = $_POST["fecha_nacimiento"] ?? '';
    $contrasena = $_POST["password"] ?? '';

    // Validar datos obligatorios
    if (empty($nombre) || empty($correo) || empty($fecha_nacimiento) || empty($contrasena)) {
        echo json_encode(["error" => "Todos los campos son obligatorios"]);
        exit;
    }

    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["error" => "El correo electrónico no es válido"]);
        exit;
    }

    // Validar seguridad de la contraseña
    $passwordPattern = "/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
    if (!preg_match($passwordPattern, $contrasena)) {
        echo json_encode(["error" => "La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un símbolo especial."]);
        exit;
    }

    // Encriptar contraseña
    $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

    try {
        // Conectar a la base de datos
        $conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verificar si el correo ya está registrado
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        if ($stmt->fetch()) {
            echo json_encode(["error" => "El correo ya está registrado"]);
            exit;
        }

        // Insertar usuario en la base de datos
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, fecha_nacimiento, contrasena) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nombre, $correo, $fecha_nacimiento, $contrasenaHash])) {
            echo json_encode(["success" => "Registro exitoso! Ahora puedes iniciar sesión.", "redirect" => "http://localhost/sneaker_store/login.html"]);
        } else {
            echo json_encode(["error" => "Error al registrar usuario"]);
        }

    } catch (PDOException $e) {
        echo json_encode(["error" => "Error SQL: " . $e->getMessage()]);
    }
}
?>
