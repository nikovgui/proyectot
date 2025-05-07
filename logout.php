<?php
session_start();

// 📌 **Verificar si hay una sesión activa antes de destruirla**
if (isset($_SESSION["admin"])) {
    session_destroy();
}

// 📌 **Redirigir al login con una validación adicional**
header("Location: http://localhost/sneaker_store/login.html");
exit;
?>
