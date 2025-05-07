<?php
session_start();
require_once "admin_auth.php";

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if (verifyAdmin($username, $password)) {
        $_SESSION["admin"] = $username;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "⚠ Credenciales incorrectas.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Administrador</title>
</head>
<body>
    <h2>Login Administrador</h2>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit" name="login">Ingresar</button>
    </form>
</body>
</html>
