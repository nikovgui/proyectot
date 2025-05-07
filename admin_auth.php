<?php
// 📌 **Evitar llamar `session_start()` si la sesión ya está iniciada**
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDBConnection() {
    try {
        return new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

function verifyAdmin($username, $password) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && hash("sha256", $password) === $admin["password"]) {
        $_SESSION["admin"] = true; // 📌 Guardar en sesión si el usuario es admin
        return true;
    } 

    return false;
}
?>
