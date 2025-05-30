<?php
session_start();
require_once "admin_auth.php";

if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

require_once "db_connection.php";

// Obtener zapatillas
$stmt = $conn->query("SELECT * FROM sneakers");
$sneakers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener ropa
$stmt = $conn->query("SELECT id, nombre, marca, precio, imagen, categoria, talla, color, genero FROM ropa");
$ropa = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para procesar y estandarizar los datos de ropa
function procesarRopaAdmin($item) {
    return [
        'id' => $item['id'] ?? 0,
        'name' => $item['nombre'] ?? 'Nombre no disponible',
        'brand' => $item['marca'] ?? 'Marca no disponible',
        'price' => $item['precio'] ?? 0,
        'image' => $item['imagen'] ?? 'https://via.placeholder.com/300x300?text=Imagen+no+disponible',
        'category' => $item['categoria'] ?? 'Categoría no disponible',
        'size' => $item['talla'] ?? 'Talla no disponible',
        'color' => $item['color'] ?? 'Color no disponible',
        'gender' => $item['genero'] ?? 'unisex'
    ];
}

// Procesar todos los items de ropa
$ropa = array_map('procesarRopaAdmin', $ropa);
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

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            padding: 20px 0;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            text-align: center;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
        }

        .product-image img {
            max-height: 85%;
            object-fit: contain;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image img {
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

        .nav-tabs .nav-link {
            color: var(--primary-color);
            font-weight: 600;
        }

        .nav-tabs .nav-link.active {
            color: var(--accent-color);
            border-bottom: 3px solid var(--accent-color);
        }

        .add-product-btn {
            margin-bottom: 20px;
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
    <ul class="nav nav-tabs" id="adminTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="sneakers-tab" data-bs-toggle="tab" data-bs-target="#sneakers" type="button" role="tab">Zapatillas</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ropa-tab" data-bs-toggle="tab" data-bs-target="#ropa" type="button" role="tab">Ropa</button>
        </li>
    </ul>

    <div class="tab-content" id="adminTabsContent">
        <!-- Tab de Zapatillas -->
        <div class="tab-pane fade show active" id="sneakers" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Administración de Zapatillas</h2>
                <button class="btn btn-primary add-product-btn" onclick="window.location.href='add_sneaker.php'">
                    <i class="fas fa-plus"></i> Agregar Zapatilla
                </button>
            </div>
            <div class="product-grid">
                <?php foreach ($sneakers as $sneaker): ?>
                    <div class="product-card" onclick="mostrarModal(<?= $sneaker['id'] ?>, 'sneakers')">
                        <div class="product-image">
                            <?php 
                            $images = json_decode($sneaker['images']);
                            $firstImage = !empty($images) ? $images[0] : 'https://via.placeholder.com/300x300?text=Imagen+no+disponible';
                            ?>
                            <img src="<?= htmlspecialchars($firstImage) ?>" alt="<?= htmlspecialchars($sneaker['name']) ?>">
                        </div>
                        <h3 class="product-name"><?= htmlspecialchars($sneaker['name']) ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tab de Ropa -->
        <div class="tab-pane fade" id="ropa" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Administración de Ropa</h2>
                <button class="btn btn-primary add-product-btn" onclick="window.location.href='add_clothing.php'">
                    <i class="fas fa-plus"></i> Agregar Ropa
                </button>
            </div>
            <div class="product-grid">
                <?php if (empty($ropa)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-tshirt fa-3x mb-3 text-muted"></i>
                        <h4>No hay productos de ropa disponibles</h4>
                    </div>
                <?php else: ?>
                    <?php foreach ($ropa as $item): ?>
                        <div class="product-card" onclick="mostrarModal(<?= $item['id'] ?>, 'ropa')">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <h3 class="product-name"><?= htmlspecialchars($item['name']) ?></h3>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ambos tipos de productos -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h2 id="modal-title"></h2>
        <img id="modal-image" src="" alt="Imagen Producto">
        <div id="modal-details"></div>
        <div id="modal-sizes-container" style="display: none;">
            <div id="modal-sizes"></div>
            <input type="number" id="new-size" placeholder="Talla">
            <input type="number" id="new-price" placeholder="Precio">
            <button class="btn-accent" onclick="agregarTalla()">Agregar Talla</button>
        </div>
        <button class="btn-danger" onclick="eliminarProducto()">Eliminar Producto</button>
    </div>
</div>

<script>
    let productsData = {
        sneakers: [],
        ropa: [] // Cambiado de 'clothing' a 'ropa'
    };
    let selectedProductId = null;
    let selectedProductType = null;

    document.addEventListener("DOMContentLoaded", function () {
        cargarProductos();
    });

    function cargarProductos() {
        // Cargar zapatillas
        fetch("http://localhost/sneaker_store/get_sneakers.php")
            .then(response => response.json())
            .then(data => {
                productsData.sneakers = data.map(sneaker => {
                    return {
                        ...sneaker,
                        images: typeof sneaker.images === 'string' ? JSON.parse(sneaker.images) : sneaker.images,
                        prices: typeof sneaker.prices === 'string' ? JSON.parse(sneaker.prices) : sneaker.prices
                    };
                });
                console.log("Zapatillas cargadas:", productsData.sneakers);
            })
            .catch(error => console.error("Error cargando zapatillas:", error));

        // Cargar ropa
        fetch("http://localhost/sneaker_store/get_ropa.php")
            .then(response => response.json())
            .then(data => {
                productsData.ropa = Array.isArray(data) ? data : [data];
                console.log("Ropa cargada:", productsData.ropa);
            })
            .catch(error => console.error("Error cargando ropa:", error));
    }

    function mostrarModal(id, type) {
        console.log("Mostrando modal para:", id, type);
        selectedProductId = id;
        selectedProductType = type;
        
        // Verificar que los datos existen
        if (!productsData[type]) {
            console.error(`Tipo de producto no válido: ${type}`, productsData);
            return;
        }

        const product = productsData[type].find(p => p.id == id);
        if (!product) {
            console.error(`No se encontró el producto con ID ${id}`, productsData[type]);
            return;
        }

        console.log("Producto encontrado:", product);

        // Asegurar que los campos requeridos existan
        const productName = product.name || 'Nombre no disponible';
        let productImage = 'https://via.placeholder.com/300x300?text=Imagen+no+disponible';
        
        if (type === 'sneakers') {
            productImage = Array.isArray(product.images) && product.images.length > 0 ? 
                          product.images[0] : 
                          (typeof product.images === 'string' ? product.images : productImage);
        } else {
            productImage = product.image || productImage;
        }

        document.getElementById("modal-title").innerText = productName;
        document.getElementById("modal-image").src = productImage;
        document.getElementById("modal-image").alt = productName;

        const detailsContainer = document.getElementById("modal-details");
        detailsContainer.innerHTML = `
            <p><strong>Marca:</strong> ${product.brand || 'Marca no disponible'}</p>
            <p><strong>Precio:</strong> $${product.price || '0'}</p>
            ${type === 'ropa' ? `
                <p><strong>Categoría:</strong> ${product.category || 'Categoría no disponible'}</p>
                <p><strong>Talla:</strong> ${product.size || 'Talla no disponible'}</p>
                <p><strong>Color:</strong> ${product.color || 'Color no disponible'}</p>
                <p><strong>Género:</strong> ${product.gender || 'unisex'}</p>
            ` : ''}
        `;

        // Mostrar sección de tallas solo para zapatillas
        const sizesContainer = document.getElementById("modal-sizes-container");
        if (type === 'sneakers') {
            sizesContainer.style.display = 'block';
            const sizesContent = document.getElementById("modal-sizes");
            sizesContent.innerHTML = "<h3>Tallas Disponibles</h3>";
            
            if (product.prices && typeof product.prices === 'object') {
                Object.entries(product.prices).forEach(([size, price]) => {
                    sizesContent.innerHTML += `
                        <div class="size-option">
                            <span>Talla ${size} - $${price}</span>
                            <button class="btn-danger" onclick="eliminarTalla('${id}', '${size}')">Eliminar</button>
                        </div>
                    `;
                });
            } else {
                sizesContent.innerHTML += "<p>No hay tallas disponibles</p>";
            }
        } else {
            sizesContainer.style.display = 'none';
        }

        document.getElementById("productModal").style.display = "flex";
    }

    function cerrarModal() {
        document.getElementById("productModal").style.display = "none";
    }

    function agregarTalla() {
        if (selectedProductType !== 'sneakers') return;
        
        const size = document.getElementById("new-size").value;
        const price = document.getElementById("new-price").value;
        if (!size || !price) {
            alert("Ingresa talla y precio.");
            return;
        }

        const formData = new FormData();
        formData.append("id", selectedProductId);
        formData.append("size", size);
        formData.append("price", price);

        fetch("agregar_talla.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cargarProductos();
                setTimeout(() => mostrarModal(selectedProductId, selectedProductType), 300);
                document.getElementById("new-size").value = "";
                document.getElementById("new-price").value = "";
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Error:", error));
    }

    function eliminarProducto() {
        const endpoint = selectedProductType === 'sneakers' ? 
            'eliminar_sneaker.php' :  // Cambiado a singular para coincidir con tu archivo
            'eliminar_ropa.php';
        
        if (confirm(`¿Seguro que deseas eliminar este ${selectedProductType === 'sneakers' ? 'zapatilla' : 'prenda de ropa'}?`)) {
            // Cambiamos a método DELETE como espera tu backend
            fetch(`${endpoint}?id=${selectedProductId}`, {
                method: "DELETE"
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP! estado: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Respuesta del servidor:", data);
                if (data.success) {
                    location.reload();
                } else {
                    throw new Error(data.message || "Error al eliminar");
                }
            })
            .catch(error => {
                console.error("Error al eliminar:", error);
                alert(`Error al eliminar: ${error.message}`);
            });
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
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cargarProductos();
                    setTimeout(() => mostrarModal(selectedProductId, selectedProductType), 300);
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