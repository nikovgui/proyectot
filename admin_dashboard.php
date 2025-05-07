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
        /* 📌 Estilos generales */
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f4f4f4; 
            margin: 0; 
            padding: 20px; 
        }
        
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1); 
        }

        /* 📌 Botón de volver */
        .back-btn { 
            font-size: 24px; 
            font-weight: bold; 
            border: none; 
            background: none; 
            cursor: pointer; 
            color: #333; 
            padding: 10px; 
            position: absolute; 
            top: 20px; 
            left: 20px; 
            transition: color 0.3s ease; 
        }

        .back-btn:hover { 
            color: #ff4500; 
        }

        /* 📌 Encabezado */
        h2 { 
            text-align: center; 
            color: #333; 
            margin-bottom: 20px; 
        }

        /* 📌 Zapatillas en el inventario */
        .sneaker { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 15px; 
            border-bottom: 1px solid #ddd; 
            background: #fff; 
            border-radius: 8px; 
            margin-bottom: 10px; 
            transition: box-shadow 0.3s ease; 
        }

        .sneaker:hover { 
            box-shadow: 0px 0px 10px rgba(0,0,0,0.2); 
        }

        .sneaker img { 
            width: 60px; 
            height: 60px; 
            object-fit: cover; 
            border-radius: 5px; 
        }

        /* 📌 Botones de acción */
        .btn { 
            padding: 8px 12px; 
            cursor: pointer; 
            border-radius: 5px; 
            font-size: 14px; 
            border: none; 
        }

        .delete { 
            background: red; 
            color: white; 
        }

        .delete:hover { 
            background: darkred; 
        }

        .delete-size { 
            background: orange; 
            color: white; 
        }

        .delete-size:hover { 
            background: darkorange; 
        }
    </style>
</head>
<body>

    <!-- 📌 Botón de volver -->
    <button class="back-btn" onclick="window.history.back()"><i class="fa-solid fa-backward"></i></button>

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
    <script src="https://kit.fontawesome.com/9e7abfd42a.js" crossorigin="anonymous"></script>

</body>
</html>
