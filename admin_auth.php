<?php
function getDBConnection() {
    return new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}

function verifyAdmin($username, $password) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT password FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $admin && hash("sha256", $password) === $admin["password"];
}
?>
