<?php
session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

include("conexion.php");
$conexion->set_charset("utf8mb4");

// Validar que la solicitud proviene de un formulario válido
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['accion']) || !isset($_POST['solicitud_id'])) {
    header("Location: index.php");
    exit();
}

$accion = $_POST['accion'];
$solicitud_id = intval($_POST['solicitud_id']);
$mascota_id = isset($_POST['mascota_id']) ? intval($_POST['mascota_id']) : 0;

// Obtener el refugio_id actual
$refugio_id = $_SESSION["usuario_id"];

// Verificar que la solicitud existe y pertenece al refugio
$sql = "SELECT * FROM solicitudes WHERE id = ? AND refugio_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $solicitud_id, $refugio_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$solicitud = $result->fetch_assoc(); // Obtener datos de la solicitud

if ($accion === 'aceptar') {
    // Aceptar: marcar esta solicitud como Aceptado
    $stmt = $conexion->prepare("UPDATE solicitudes SET estado = 'Aceptado' WHERE id = ?");
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();

    // Cambiar estado de la mascota a "Adoptado"
    if ($mascota_id) {
        $stmt = $conexion->prepare("UPDATE mascotas SET estado = 'Adoptado', adoptante_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $solicitud['adoptante_id'], $mascota_id);
        $stmt->execute();

        // Rechazar otras solicitudes de la misma mascota
        $stmt = $conexion->prepare("UPDATE solicitudes SET estado = 'Rechazado' WHERE mascota_id = ? AND id != ?");
        $stmt->bind_param("ii", $mascota_id, $solicitud_id);
        $stmt->execute();
    }

} elseif ($accion === 'rechazar') {
    // Rechazar: cambiar estado a Rechazado
    $stmt = $conexion->prepare("UPDATE solicitudes SET estado = 'Rechazado' WHERE id = ?");
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();
}

// Redirigir al perfil del refugio
header("Location: perfilrefugio.php?id=$refugio_id");
exit();
?>
