<?php
session_start();
require_once "admin_auth.php";

if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

require_once "db_connection.php";

$stmt = $conn->query("SELECT * FROM sneakers");
$sneakers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administración - Inventario</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; text-align: center; }
        .container { max-width: 800px; margin: 50px auto; background: white; padding: 20px; border-radius: 10px; 
                     box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        .sneaker { display: flex; align-items: center; justify-content: space-between; padding: 10px; border-bottom: 1px solid #ddd; }
        .sneaker img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
        .btn { padding: 5px 10px; cursor: pointer; border-radius: 5px; }
        .delete { background: red; color: white; border: none; }
        .delete-size { background: orange; color: white; border: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Inventario de Zapatillas</h2>
        <?php foreach ($sneakers as $sneaker): ?>
            <div class="sneaker">
                <img src="<?= json_decode($sneaker['images'])[0] ?>" alt="Imagen">
                <div>
                    <h3><?= $sneaker['name'] ?></h3>
                    <?php 
                        // 📌 **Verifica que `sizes` sea un array válido antes de usar `implode()`**
                        $tallas = json_decode($sneaker["sizes"], true) ?: [];
                        $tallasTexto = !empty($tallas) ? implode(", ", (array) $tallas) : "No disponibles";
                    ?>
                    <p><strong>Tallas disponibles:</strong> <?= $tallasTexto ?></p>
                </div>
                <button class="delete" onclick="eliminarZapatilla(<?= $sneaker['id'] ?>)">Eliminar zapatilla</button>
            </div>
            <div>
                <p><strong>Eliminar talla específica:</strong></p>
                <?php foreach ($tallas as $size): ?>
                    <button class="delete-size" onclick="eliminarTalla(<?= $sneaker['id'] ?>, '<?= $size ?>')">Eliminar talla <?= $size ?></button>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function eliminarZapatilla(id) {
            if (confirm("¿Seguro que deseas eliminar esta zapatilla?")) {
                fetch(`eliminar_sneaker.php?id=${id}`, { method: "DELETE" })
                .then(() => location.reload());
            }
        }

        function eliminarTalla(id, size) {
            if (confirm(`¿Eliminar talla ${size}?`)) {
                fetch(`eliminar_talla.php?id=${id}&size=${size}`, { method: "DELETE" })
                .then(() => location.reload());
            }
        }
    </script>
</body>
</html>
