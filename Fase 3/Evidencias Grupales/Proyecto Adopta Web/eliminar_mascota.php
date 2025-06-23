<?php
session_start();
include("conexion.php");
$conexion->set_charset("utf8mb4");

// Verificar que se recibió el ID de la mascota y del refugio
if (!isset($_POST['mascota_id']) || !is_numeric($_POST['mascota_id']) ||
    !isset($_POST['refugio_id']) || !is_numeric($_POST['refugio_id'])) {
    die("Parámetros inválidos.");
}

$mascota_id = (int)$_POST['mascota_id'];
$refugio_id = (int)$_POST['refugio_id'];

// Verificar que la mascota pertenece al refugio
$stmt = $conexion->prepare("SELECT id FROM mascotas WHERE id = ? AND refugio_id = ?");
$stmt->bind_param("ii", $mascota_id, $refugio_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Mascota no encontrada o no pertenece al refugio.";
    exit();
}

// Eliminar mascota
$stmtEliminar = $conexion->prepare("DELETE FROM mascotas WHERE id = ?");
$stmtEliminar->bind_param("i", $mascota_id);
if ($stmtEliminar->execute()) {
    header("Location: perfilrefugio.php?id=$refugio_id");
    exit();
} else {
    echo "Error al eliminar la mascota.";
}
?>
