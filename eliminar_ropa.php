<?php
session_start();
require_once "admin_auth.php";
require_once "db_connection.php";

if (!isset($_SESSION["admin"])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

// Manejar tanto DELETE como POST para mayor compatibilidad
if (($_SERVER["REQUEST_METHOD"] === "DELETE" || $_SERVER["REQUEST_METHOD"] === "POST") && isset($_REQUEST["id"])) {
    $id = intval($_REQUEST["id"]);
    
    try {
        $stmt = $conn->prepare("DELETE FROM ropa WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            header('Content-Type: application/json');
            echo json_encode(["success" => true, "message" => "Prenda eliminada correctamente"]);
        } else {
            header("HTTP/1.1 404 Not Found");
            echo json_encode(["success" => false, "message" => "No se encontró la prenda"]);
        }
    } catch (PDOException $e) {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(["success" => false, "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(["success" => false, "message" => "Solicitud inválida"]);
}
?>