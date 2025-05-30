<?php
session_start();
require_once "admin_auth.php";

if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

// Procesar formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $brand = $_POST["brand"];
    $category = $_POST["category"];
    $sizes = isset($_POST["sizes"]) ? $_POST["sizes"] : [];
    $color = $_POST["color"];
    $material = $_POST["material"];
    $gender = $_POST["gender"];
    $price = $_POST["price"];
    $stock = $_POST["stock"];
    
    // Definir la carpeta de almacenamiento
    $uploadDir = "img/ropa/";

    // Crear la carpeta si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imagePaths = [];

    // Procesar imágenes principales
    if (!empty($_FILES["main_images"]["tmp_name"][0])) {
        foreach ($_FILES["main_images"]["tmp_name"] as $key => $tmp_name) {
            $fileName = uniqid() . '_' . basename($_FILES["main_images"]["name"][$key]);
            $targetFile = $uploadDir . $fileName;

            // Validar que el archivo es una imagen
            $fileType = mime_content_type($tmp_name);
            if (str_starts_with($fileType, "image")) {
                if (move_uploaded_file($tmp_name, $targetFile)) {
                    $imagePaths[] = "http://localhost/sneaker_store/" . $targetFile;
                } else {
                    echo "<script>alert('⚠ Error al subir imagen: " . $_FILES["main_images"]["name"][$key] . "');</script>";
                }
            } else {
                echo "<script>alert('⚠ El archivo " . $_FILES["main_images"]["name"][$key] . " no es una imagen válida.');</script>";
            }
        }
    }

    // Convertir arrays a JSON para almacenar en la base de datos
    $sizesJson = json_encode($sizes);
    $imagesJson = json_encode($imagePaths);

    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO ropa (nombre, marca, precio, imagen, imagenes, categoria, talla, tallas, color, material, genero, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Usamos la primera imagen como imagen principal (para compatibilidad)
    $mainImage = !empty($imagePaths) ? $imagePaths[0] : null;
    
    if ($stmt->execute([$name, $brand, $price, $mainImage, $imagesJson, $category, $sizes[0] ?? '', $sizesJson, $color, $material, $gender, $stock])) {
        echo "<script>alert('✅ Prenda de ropa agregada correctamente!'); window.location.href = 'ropa.html';</script>";
    } else {
        echo "<script>alert('⚠ Error al agregar la prenda.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Ropa - Panel Admin</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .admin-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #343a40;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        input[type="text"],
        input[type="number"],
        select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus, textarea:focus {
            border-color: #007bff;
            outline: none;
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .image-preview {
            width: 150px;
            height: 150px;
            border: 2px dashed #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
        }
        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
        }
        .btn-submit {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            background: #f1f1f1;
            padding: 8px 12px;
            border-radius: 5px;
        }
        .checkbox-item input {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h2>Agregar Nueva Prenda de Ropa</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nombre de la prenda</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="brand">Marca</label>
                    <select id="brand" name="brand" required>
                        <option value="">Seleccionar marca</option>
                        <option value="EA7">EA7</option>
                        <option value="HOODRICH">HOODRICH</option>
                        <option value="SIKSILK">SIKSILK</option>
                        <option value="LACOSTE">LACOSTE</option>
                        <option value="CONVERSE">CONVERSE</option>
                        <option value="NIKE">NIKE</option>
                        <option value="ADIDAS">ADIDAS</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category">Categoría principal</label>
                    <select id="category" name="category" required>
                        <option value="">Seleccionar categoría</option>
                        <option value="Camisetas">Camisetas</option>
                        <option value="Pantalones">Pantalones</option>
                        <option value="Sudaderas">Sudaderas</option>
                        <option value="Chaquetas">Chaquetas</option>
                        <option value="Shorts">Shorts</option>
                        <option value="Accesorios">Accesorios</option>
                        <option value="Colecciones">Coleccion</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Tallas disponibles</label>
                <div class="checkbox-group">
                    <label class="checkbox-item">
                        <input type="checkbox" name="sizes[]" value="XS"> XS
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="sizes[]" value="S"> S
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="sizes[]" value="M"> M
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="sizes[]" value="L"> L
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="sizes[]" value="XL"> XL
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="sizes[]" value="XXL"> XXL
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="sizes[]" value="Única"> Única
                    </label>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" id="color" name="color" required>
                </div>
                <div class="form-group">
                    <label for="material">Material</label>
                    <input type="text" id="material" name="material">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Género</label>
                    <select id="gender" name="gender" required>
                        <option value="unisex">Unisex</option>
                        <option value="hombre">Hombre</option>
                        <option value="mujer">Mujer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Precio ($)</label>
                    <input type="number" id="price" name="price" min="0" step="0.01" required>
                </div>
            </div>

            <div class="form-group">
                <label for="stock">Stock disponible</label>
                <input type="number" id="stock" name="stock" min="0" required>
            </div>

            <div class="form-group">
                <label for="main_images">Imágenes de la prenda (Múltiples)</label>
                <input type="file" id="main_images" name="main_images[]" accept="image/*" multiple required>
                <div class="image-preview-container" id="imagePreviewContainer"></div>
            </div>

            <button type="submit" class="btn-submit">Agregar Prenda</button>
        </form>
    </div>

    <script>
        // Vista previa de múltiples imágenes
        document.getElementById('main_images').addEventListener('change', function(event) {
            const files = event.target.files;
            const previewContainer = document.getElementById('imagePreviewContainer');
            previewContainer.innerHTML = '';
            
            if (files.length > 0) {
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const preview = document.createElement('div');
                        preview.className = 'image-preview';
                        preview.innerHTML = `
                            <img src="${e.target.result}" alt="Vista previa">
                            <button type="button" class="remove-image" onclick="removeImagePreview(this)">×</button>
                        `;
                        previewContainer.appendChild(preview);
                    }
                    
                    reader.readAsDataURL(file);
                }
            } else {
                previewContainer.innerHTML = '';
            }
        });

        function removeImagePreview(button) {
            const previewContainer = document.getElementById('imagePreviewContainer');
            const preview = button.parentElement;
            previewContainer.removeChild(preview);
            
            // Actualizar el input de archivos (complejo en JavaScript)
            // En una implementación real, podrías usar un array para manejar los archivos seleccionados
        }

        // Validación antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            // Validar que se seleccionó al menos una talla
            const sizeCheckboxes = document.querySelectorAll('input[name="sizes[]"]:checked');
            if (sizeCheckboxes.length === 0) {
                alert('Selecciona al menos una talla');
                e.preventDefault();
                return;
            }
            
            // Validar precio
            const price = parseFloat(document.getElementById('price').value);
            if (price <= 0) {
                alert('El precio debe ser mayor que 0');
                e.preventDefault();
                return;
            }
            
            // Validar stock
            const stock = parseInt(document.getElementById('stock').value);
            if (stock < 0) {
                alert('El stock no puede ser negativo');
                e.preventDefault();
                return;
            }
            
            // Validar imágenes
            const fileInput = document.getElementById('main_images');
            if (fileInput.files.length === 0) {
                alert('Debes subir al menos una imagen');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>