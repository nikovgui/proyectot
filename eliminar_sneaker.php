<?php
require_once "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] === "DELETE" && isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    $stmt = $conn->prepare("DELETE FROM sneakers WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => "Zapatilla eliminada correctamente"]);
}
?>
