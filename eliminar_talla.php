<?php
require_once "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] === "DELETE" && isset($_GET["id"]) && isset($_GET["size"])) {
    $id = intval($_GET["id"]);
    $size = $_GET["size"];

    $stmt = $conn->prepare("SELECT sizes, prices FROM sneakers WHERE id = ?");
    $stmt->execute([$id]);
    $sneaker = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sneaker) {
        $sizes = json_decode($sneaker["sizes"], true);
        $prices = json_decode($sneaker["prices"], true);

        if (($key = array_search($size, $sizes)) !== false) {
            unset($sizes[$key]);
            unset($prices[$size]);
        }

        $stmt = $conn->prepare("UPDATE sneakers SET sizes = ?, prices = ? WHERE id = ?");
        $stmt->execute([json_encode(array_values($sizes)), json_encode($prices), $id]);
        
        echo json_encode(["success" => "Talla eliminada correctamente"]);
    }
}
?>
