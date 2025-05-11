<?php
session_start();
require_once "admin_auth.php";

if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

// 📌 **Procesar formulario cuando se envía**
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $sizes_prices = [];
    

    // 📌 **Recopilar tallas y precios**
    foreach ($_POST["sizes"] as $size) {
        $sizes_prices[$size] = $_POST["prices"][$size] ?? 0;
    }
    
    $prices = json_encode($sizes_prices, JSON_UNESCAPED_SLASHES);
    
    // 📌 **Definir la carpeta de almacenamiento**
    $uploadDir = "img/";

    // 📌 **Crear la carpeta si no existe**
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imagePaths = [];

    foreach ($_FILES["images"]["tmp_name"] as $key => $tmpName) {
        $fileName = basename($_FILES["images"]["name"][$key]);
        $targetFile = $uploadDir . $fileName;

        // 📌 **Validar que el archivo es una imagen**
        $fileType = mime_content_type($tmpName);
        if (!str_starts_with($fileType, "image")) {
            echo "⚠ Error: El archivo `$fileName` no es una imagen válida.<br>";
            continue;
        }

        // 📌 **Mover archivo a la carpeta img/**
        if (move_uploaded_file($tmpName, $targetFile)) {
            $imagePaths[] = "http://localhost/sneaker_store/img/" . $fileName; // Ruta pública
        } else {
            echo "⚠ Error al subir imagen: $fileName<br>";
        }
    }

    // 📌 **Convertir imágenes a JSON para almacenar en la base de datos**
    $imagesJson = json_encode(array_values($imagePaths), JSON_UNESCAPED_SLASHES);

    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO sneakers (name, images, prices, sizes) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$name, $imagesJson, $prices, json_encode(array_keys($sizes_prices))])) {
        echo "<script>alert('✅ Zapatilla agregada correctamente!');</script>";
    } else {
        echo "<script>alert('⚠ Error al agregar zapatilla.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Zapatillas</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background-color: #f4f4f4; }
        .container { max-width: 500px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); margin: 50px auto; }
        h2 { margin-bottom: 20px; color: #333; }
        form input, form button, form select { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; }
        form button { background-color: #007bff; color: white; cursor: pointer; }
        form button:hover { background-color: #0056b3; }
        .preview-images { display: flex; justify-content: center; gap: 10px; margin-top: 10px; }
        .preview-images img { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; }
        .sizes-container { display: flex; flex-direction: column; text-align: left; margin-top: 10px; }
        .size-input { display: flex; align-items: center; gap: 10px; margin-bottom: 5px; }
        .size-input input { width: 60px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Agregar Zapatilla</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Nombre de la zapatilla" required>

            <!-- 📌 Checkboxes para seleccionar tallas y precios -->
            <div class="sizes-container">
                <p><strong>Selecciona tallas y precios:</strong></p>
                <div class="size-input">
                    <label><input type="checkbox" name="sizes[]" value="6"> 6</label>
                    <input type="number" name="prices[6]" placeholder="$" min="0">
                </div>
                <div class="size-input">
                    <label><input type="checkbox" name="sizes[]" value="7"> 7</label>
                    <input type="number" name="prices[7]" placeholder="$" min="0">
                </div>
                <div class="size-input">
                    <label><input type="checkbox" name="sizes[]" value="8"> 8</label>
                    <input type="number" name="prices[8]" placeholder="$" min="0">
                </div>
                <div class="size-input">
                    <label><input type="checkbox" name="sizes[]" value="9"> 9</label>
                    <input type="number" name="prices[9]" placeholder="$" min="0">
                </div>
                <div class="size-input">
                    <label><input type="checkbox" name="sizes[]" value="10"> 10</label>
                    <input type="number" name="prices[10]" placeholder="$" min="0">
                </div>
                <div class="size-input">
                    <label><input type="checkbox" name="sizes[]" value="11"> 11</label>
                    <input type="number" name="prices[11]" placeholder="$" min="0">
                </div>
                <label>
                    <input type="checkbox" name="en_oferta" value="1"> Marcar como oferta
                </label>

            </div>

            <input type="file" name="images[]" multiple accept="image/*" id="image-input">
            <div class="preview-images" id="preview"></div>
            <button type="submit">Agregar Zapatilla</button>
        </form>
    </div>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    fetch("http://localhost/sneaker_store/check_admin.php")
        .then(response => response.json())
        .then(data => {
            console.log("Admin status:", data.admin); // 📌 Agrega esto para depuración
            if (data.admin) {
                document.getElementById("add-sneaker-button").style.display = "block";
            }
        })
        .catch(error => console.error("Error verificando administrador:", error));
});



        document.getElementById("image-input").addEventListener("change", function(event) {
            let previewContainer = document.getElementById("preview");
            previewContainer.innerHTML = ""; // Limpia imágenes previas

            Array.from(event.target.files).forEach(file => {
                let reader = new FileReader();
                reader.onload = function(e) {
                    let imgElement = document.createElement("img");
                    imgElement.src = e.target.result;
                    previewContainer.appendChild(imgElement);
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
</body>
</html>
