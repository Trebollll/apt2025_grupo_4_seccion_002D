<?php
include("conexion.php");
$conexion->set_charset("utf8mb4");

if (isset($_GET['region_id'])) {
    $region_id = (int) $_GET['region_id'];

    $stmt = $conexion->prepare("SELECT id, nombre FROM comuna WHERE region_id = ?");
    $stmt->bind_param("i", $region_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $comunas = [];
    while ($row = $resultado->fetch_assoc()) {
        $comunas[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($comunas);
}
