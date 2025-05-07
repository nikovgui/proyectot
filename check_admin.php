<?php
session_start();
header("Content-Type: application/json");

if (isset($_SESSION["usuario"]) && $_SESSION["usuario"]["username"] === "admin") {
    echo json_encode(["admin" => true]);
} else {
    echo json_encode(["admin" => false]);
}
?>
