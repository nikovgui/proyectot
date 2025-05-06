<?php
session_start();
session_destroy();
header("Location: http://localhost/sneaker_store/login.html");
exit;
?>
