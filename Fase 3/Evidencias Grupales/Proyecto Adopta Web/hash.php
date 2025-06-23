<?php
$clave = "admin12345";
$hash = password_hash($clave, PASSWORD_DEFAULT);
echo $hash;
?>
