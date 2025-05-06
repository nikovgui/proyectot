<?php
require "vendor/autoload.php"; // Asegurar que tienes PHPMailer instalado
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// 🔧 **Evitar que errores PHP rompan el JSON**
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Content-Type: application/json");

// Conectar a la base de datos
try {
    $conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    echo json_encode(["error" => "Error de conexión a la base de datos."]);
    exit;
}

// Obtener correo del usuario
$email = trim($_POST["email"] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Correo inválido ingresado: " . $email);
    echo json_encode(["error" => "Correo inválido."]);
    exit;
}

// Verificar si el usuario existe en la base de datos
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    error_log("Intento de recuperación con correo no registrado: " . $email);
    echo json_encode(["error" => "El correo no está registrado."]);
    exit;
}

// Generar **token único** y guardar en la base de datos
$token = bin2hex(random_bytes(32));
$stmt = $conn->prepare("UPDATE usuarios SET reset_token = ? WHERE id = ?");
$stmt->execute([$token, $usuario["id"]]);

// Configurar PHPMailer
$mail = new PHPMailer(true);
try {
    // 🔧 **Modo depuración para identificar problemas en PHPMailer**
    $mail->SMTPDebug = 0; // Cambia a 2 si necesitas más detalles de depuración

    // 🔧 **Configuración SMTP (ajusta según tu proveedor de correo)**
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com"; // Cambia esto por tu servidor SMTP
    $mail->SMTPAuth = true;
    $mail->Username = "aguirre.nikolai1@gmail.com"; // Tu correo de envío
    $mail->Password = "rldv npgi igxc pabw"; // Tu contraseña de correo
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom("aguirre.nikolai1@gmail.com", "OGKicks Soporte");
    $mail->addAddress($email);
    $mail->Subject = "Recuperación de contraseña";
    $mail->Body = "Haz clic en el siguiente enlace para cambiar tu contraseña: 
                   http://localhost/sneaker_store/reset_password.html?token=$token";

    if ($mail->send()) {
        error_log("Correo de recuperación enviado a: " . $email);
        echo json_encode(["message" => "Se ha enviado un enlace de recuperación a tu correo."]);
    } else {
        error_log("Error al enviar el correo: " . strip_tags($mail->ErrorInfo));
        echo json_encode(["error" => "Error al enviar el correo."]);
    }
} catch (Exception $e) {
    error_log("Excepción de PHPMailer: " . strip_tags($e->getMessage()));
    echo json_encode(["error" => "Error al enviar el correo."]);
}
?>
