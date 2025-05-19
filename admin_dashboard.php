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
    <title>Admin - OGKicks</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/9e7abfd42a.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --primary-color: #1e3a8a;
            --accent-color: #ff6b6b;
            --background-light: #f8fafc;
            --text-dark: #1e293b;
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: var(--background-light);
            color: var(--text-dark);
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 12px 20px;
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }

        .sneaker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            padding: 20px 0;
        }

        .sneaker-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            text-align: center;
            cursor: pointer;
        }

        .sneaker-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
        }

        .sneaker-image {
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
        }

        .sneaker-image img {
            max-height: 85%;
            object-fit: contain;
            transition: transform 0.5s ease;
        }

        .sneaker-card:hover .sneaker-image img {
            transform: scale(1.1);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            justify-content: center;
            align-items: center;
            z-index: 1050;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 20px;
            max-width: 450px;
            width: 90%;
            box-shadow: var(--card-shadow);
            text-align: center;
        }

        .modal img {
            width: 180px;
            border-radius: 10px;
        }

        .size-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .btn-accent, .btn-danger {
            border-radius: 50px;
            font-weight: 600;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            margin-top: 10px;
            width: 100%;
        }

        .btn-accent {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-danger {
            background-color: red;
            color: white;
        }

        .close {
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="home.html">OGKicks</a>
    </div>
</nav>

<div class="container">
    <h2>Administración de Zapatillas</h2>
    <div class="sneaker-grid">
        <?php foreach ($sneakers as $sneaker): ?>
            <div class="sneaker-card" onclick="mostrarModal(<?= $sneaker['id'] ?>)">
                <div class="sneaker-image">
                    <img src="<?= json_decode($sneaker['images'])[0] ?>" alt="<?= $sneaker['name'] ?>">
                </div>
                <h3 class="sneaker-name"><?= $sneaker['name'] ?></h3>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="sneakerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h2 id="modal-title"></h2>
        <img id="modal-image" src="" alt="Imagen Zapatilla">
        <div id="modal-sizes"></div>
        <input type="number" id="new-size" placeholder="Talla">
        <input type="number" id="new-price" placeholder="Precio">
        <button class="btn-accent" onclick="agregarTalla()">Agregar Talla</button>
        <button class="btn-danger" onclick="eliminarZapatilla()">Eliminar Zapatilla</button>
    </div>
</div>

<script>
    let sneakersData = [];
    let selectedSneakerId = null;

    document.addEventListener("DOMContentLoaded", function () {
        cargarSneakers();
    });

    function cargarSneakers() {
        fetch("http://localhost/sneaker_store/get_sneakers.php")
            .then(response => response.json())
            .then(data => {
                sneakersData = data;
            })
            .catch(error => console.error("Error cargando zapatillas:", error));
    }

    function mostrarModal(id) {
        const sneaker = sneakersData.find(s => s.id === id);
        if (!sneaker) {
            console.error(`No se encontró la zapatilla con ID ${id}`);
            return;
        }

        document.getElementById("modal-title").innerText = sneaker.name;
        document.getElementById("modal-image").src = sneaker.images[0];

        const sizesContainer = document.getElementById("modal-sizes");
        sizesContainer.innerHTML = "<h3>Tallas Disponibles</h3>";
        Object.entries(sneaker.prices).forEach(([size, price]) => {
            sizesContainer.innerHTML += `
                <div class="size-option">
                    <span>Talla ${size} - $${price}</span>
                    <button class="btn-danger" onclick="eliminarTalla('${id}', '${size}')">Eliminar</button>
                </div>
            `;
        });

        selectedSneakerId = sneaker.id;
        document.getElementById("sneakerModal").style.display = "flex";
    }

    function cerrarModal() {
        document.getElementById("sneakerModal").style.display = "none";
    }

    function agregarTalla() {
        const size = document.getElementById("new-size").value;
        const price = document.getElementById("new-price").value;
        if (!size || !price) {
            alert("Ingresa talla y precio.");
            return;
        }

        const formData = new FormData();
        formData.append("id", selectedSneakerId);
        formData.append("size", size);
        formData.append("price", price);

        fetch("agregar_talla.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar datos y actualizar modal sin recargar la página
                cargarSneakers();
                setTimeout(() => mostrarModal(selectedSneakerId), 300);
                // Limpiar inputs
                document.getElementById("new-size").value = "";
                document.getElementById("new-price").value = "";
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Error:", error));
    }

    function eliminarZapatilla() {
        if (confirm("¿Seguro que deseas eliminar esta zapatilla?")) {
            fetch(`eliminar_sneaker.php?id=${selectedSneakerId}`, {
                method: "DELETE"
            }).then(() => location.reload());
        }
    }

    function eliminarTalla(id, size) {
        if (confirm(`¿Eliminar talla ${size}?`)) {
            const formData = new FormData();
            formData.append("id", id);
            formData.append("size", size);

            fetch("eliminar_talla.php", {
                method: "POST",
                body: formData
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      cargarSneakers();
                      setTimeout(() => mostrarModal(selectedSneakerId), 300);
                  } else {
                      alert("Error al eliminar talla: " + data.message);
                  }
              })
              .catch(error => console.error("Error:", error));
        }
    }
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
