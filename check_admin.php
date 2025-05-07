<?php
session_start();
header("Content-Type: application/json");

// 📌 **Verificar si el usuario es administrador**
if (isset($_SESSION["admin"]) && $_SESSION["admin"] === true) {
    echo json_encode(["admin" => true]);
} else {
    echo json_encode(["admin" => false]);
}
?>
