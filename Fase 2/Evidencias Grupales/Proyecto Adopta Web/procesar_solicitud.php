<?php
session_start();
include("conexion.php");
$conexion->set_charset("utf8mb4");

if (!isset($_SESSION["usuario_id"]) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$mascota_id = intval($_POST['mascota_id']);
$refugio_id = intval($_POST['refugio_id']);
$adoptante_id = $_SESSION["usuario_id"];

// Verificar si ya existe una solicitud del mismo adoptante para esta mascota
$sql_check = "SELECT COUNT(*) AS total FROM solicitudes 
              WHERE mascota_id = ? AND adoptante_id = ?";
$stmt = $conexion->prepare($sql_check);
$stmt->bind_param("ii", $mascota_id, $adoptante_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    $_SESSION['mensaje'] = "❌ Ya has enviado una solicitud para esta mascota.";
    header("Location: perfilmascota.php?id=$mascota_id");
    exit();
}

// Insertar nueva solicitud
$sql_insert = "INSERT INTO solicitudes (mascota_id, refugio_id, adoptante_id, estado) 
               VALUES (?, ?, ?, 'En espera')";
$stmt_insert = $conexion->prepare($sql_insert);
$stmt_insert->bind_param("iii", $mascota_id, $refugio_id, $adoptante_id);

if ($stmt_insert->execute()) {
    $_SESSION['mensaje'] = "✅ Solicitud enviada correctamente.";
} else {
    $_SESSION['mensaje'] = "❌ Error al enviar la solicitud.";
}

header("Location: perfilmascota.php?id=$mascota_id");
exit();
