<?php
require('fpdf/fpdf.php');
require 'vendor/autoload.php';


if (!isset($_GET['boleta'])) {
    die("Error: No se recibió el número de boleta.");
}

$boleta = $_GET['boleta'];

// **Conectar a la base de datos**
$conn = new PDO("mysql:host=localhost;dbname=sneaker_store;charset=utf8", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// **Obtener datos de la compra**
$stmt = $conn->prepare("SELECT * FROM boletas WHERE numero_boleta = ?");
$stmt->execute([$boleta]);
$compra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$compra) {
    die("Error: No se encontró la boleta.");
}

// **Crear el PDF**
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "Comprobante de Compra", 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "Numero de boleta: " . $compra['numero_boleta'], 0, 1);
$pdf->Cell(0, 10, "Nombre: " . $compra['nombre'] . " " . $compra['apellido'], 0, 1);
$pdf->Cell(0, 10, "Direccion: " . $compra['direccion'], 0, 1);
$pdf->Cell(0, 10, "Correo: " . $compra['email'], 0, 1);
$pdf->Cell(0, 10, "Telefono: " . $compra['telefono'], 0, 1);
$pdf->Cell(0, 10, "Monto pagado: $" . number_format($compra['monto_pagado'], 0, ',', '.'), 0, 1);
$pdf->Cell(0, 10, "Fecha estimada de entrega: " . $compra['fecha_entrega'], 0, 1);

$pdf->Output();
?>
