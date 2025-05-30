<?php
require_once "db_connection.php";

// Obtener zapatillas de colección
$stmt = $conn->prepare("SELECT id, name, images, prices, categories, 'sneaker' as type FROM sneakers WHERE categories LIKE :coleccion OR categories LIKE :coleccion2");
$stmt->execute([
    'coleccion' => '%Colección%',
    'coleccion2' => '%Coleccion%'
]);
$sneakers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener ropa de colección
$stmt = $conn->prepare("SELECT id, nombre as name, marca as brand, precio, imagen as image, categoria, talla as size, color, genero as gender, 'clothing' as type FROM ropa WHERE categoria LIKE :coleccion OR categoria LIKE :coleccion2");
$stmt->execute([
    'coleccion' => '%Colección%',
    'coleccion2' => '%Coleccion%'
]);
$ropa = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Combinar y procesar todos los productos
$allProducts = [];

// Procesar zapatillas
foreach ($sneakers as $product) {
    $prices = json_decode($product['prices'], true) ?: [];
    $displayPrice = !empty($prices) ? '$'.number_format((float)reset($prices), 0, ',', '.') : 'Consultar';
    
    $allProducts[] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price_display' => $displayPrice,
        'image' => !empty(json_decode($product['images'], true)) ? json_decode($product['images'], true)[0] : 'https://via.placeholder.com/300x300?text=Imagen+no+disponible',
        'type' => $product['type'],
        'badge_text' => 'EDICIÓN LIMITADA',
        'url' => 'detallezapatilla.html?id='.$product['id'] // URL modificada para zapatillas
    ];
}

// Procesar ropa
foreach ($ropa as $product) {
    $allProducts[] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price_display' => isset($product['precio']) ? '$'.number_format($product['precio'], 0, ',', '.') : 'Consultar',
        'image' => $product['image'] ?? 'https://via.placeholder.com/300x300?text=Imagen+no+disponible',
        'type' => $product['type'],
        'badge_text' => 'COLECCIÓN EXCLUSIVA',
        'url' => 'detalleropa.html?id='.$product['id'] // URL modificada para ropa
    ];
}

// Opcional: Mezclar aleatoriamente los productos
shuffle($allProducts);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Colecciones - OGKicks</title>
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
            padding: 0;
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
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }

        .header-section {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .header-section h1 {
            font-weight: 700;
            color: var(--primary-color);
        }

        .header-section p {
            color: #666;
            max-width: 700px;
            margin: 0 auto;
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
            text-decoration: none;
            display: block;
            color: inherit;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: inherit;
        }

        .product-image {
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            position: relative;
        }

        .product-image img {
            max-height: 85%;
            object-fit: contain;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-info {
            padding: 15px;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .product-price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .collection-badge {
            background-color: var(--accent-color);
            color: white;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-top: 8px;
        }

        .no-products {
            text-align: center;
            padding: 40px 0;
            color: #666;
        }

        .no-products i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ddd;
        }

        .type-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="home.html">OGKicks</a>
    </div>
</nav>

<div class="container">
    <div class="header-section">
        <h1>Colecciones Exclusivas</h1>
        <p>Descubre nuestras ediciones limitadas y colaboraciones especiales. Productos únicos que marcan tendencia.</p>
    </div>

    <!-- Sección única para todos los productos -->
    <?php if (empty($allProducts)): ?>
        <div class="no-products">
            <i class="fas fa-box-open"></i>
            <h4>No hay productos de colección disponibles</h4>
            <p>Pronto tendremos nuevas ediciones especiales</p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($allProducts as $product): ?>
                <a href="<?= htmlspecialchars($product['url']) ?>" class="product-card">
                    <div class="product-image">
                        <span class="type-indicator">
                            <?= $product['type'] === 'sneaker' ? 'Zapatilla' : 'Ropa' ?>
                        </span>
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-price"><?= $product['price_display'] ?></p>
                        <span class="collection-badge"><?= $product['badge_text'] ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>