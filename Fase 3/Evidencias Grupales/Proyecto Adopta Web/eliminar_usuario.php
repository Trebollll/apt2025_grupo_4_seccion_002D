<?php
session_start();
include("conexion.php");
$conexion->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["usuario_id"])) {
    $usuario_id = intval($_POST["usuario_id"]);

    // 1. Eliminar archivos físicos de los documentos
    $stmt = $conexion->prepare("SELECT ruta FROM documentos WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($row = $resultado->fetch_assoc()) {
        if (file_exists($row['ruta'])) {
            unlink($row['ruta']);
        }
    }
    $stmt->close();

    // 2. Eliminar mascotas si es refugio
    $conexion->query("DELETE FROM mascotas WHERE refugio_id = $usuario_id");

    // 3. Eliminar usuario (esto elimina los documentos por ON DELETE CASCADE)
    $conexion->query("DELETE FROM usuarios WHERE id = $usuario_id");

    // 4. Redirigir a la lista
    header("Location: indexadmin.php?eliminado=1");
    exit();
} else {
    header("Location: indexadmin.php?error=1");
    exit();
}
?>
