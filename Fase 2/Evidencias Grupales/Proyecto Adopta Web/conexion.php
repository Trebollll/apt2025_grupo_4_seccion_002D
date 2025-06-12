<?php
$conexion = new mysqli("localhost", "root", "", "adoptaweb");

// Verificar conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>
