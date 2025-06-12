<?php

session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

$tipo_sesion = $_SESSION["usuario_tipo"];
$id_sesion = $_SESSION["usuario_id"];

include("conexion.php");
$conexion->set_charset("utf8mb4");

$usuario_id = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $usuario_id = (int)$_POST['id'];
    }
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $usuario_id = (int)$_GET['id'];
}

if (!$usuario_id) {
    echo "<p style='color:red;text-align:center;'>ID de adoptante no válido.</p>";
    exit();
}

if ($tipo_sesion === "Adoptante" && $usuario_id !== $id_sesion) {
    echo "<p style='color:red;text-align:center;'>No tienes permiso para editar este perfil.</p>";
    exit();
}

if ($tipo_sesion === "Refugio") {
    echo "<p style='color:red;text-align:center;'>Solo los Adoptantes y Administradores pueden acceder a esta página.</p>";
    exit();
}

// Cargar datos del adoptante
$sql = "SELECT * FROM usuarios WHERE id = ? AND tipo = 'Adoptante'";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "<p style='color:red;text-align:center;'>Adoptante no encontrado.</p>";
    exit();
}

$adoptante = $resultado->fetch_assoc();

// Cargar regiones
$region = [];
$sql_region = "SELECT id, nombre FROM region";
$resultado_region = $conexion->query($sql_region);
if ($resultado_region && $resultado_region->num_rows > 0) {
    while ($fila = $resultado_region->fetch_assoc()) {
        $region[] = $fila;
    }
}

function validarRut($rut) {
    $rut = preg_replace('/[^kK0-9]/', '', $rut);
    if (strlen($rut) < 2) return false;
    $dv = strtoupper(substr($rut, -1));
    $numero = substr($rut, 0, -1);
    $suma = 0;
    $multiplo = 2;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $suma += $numero[$i] * $multiplo;
        $multiplo = $multiplo === 7 ? 2 : $multiplo + 1;
    }
    $resto = $suma % 11;
    $verificador = 11 - $resto;
    if ($verificador === 11) $verificador = '0';
    elseif ($verificador === 10) $verificador = 'K';
    else $verificador = (string)$verificador;
    return $dv === $verificador;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $rut = $conexion->real_escape_string($_POST['rut']);
    if (!validarRut($rut)) {
        $mensaje = "❌ El RUT ingresado no es válido.";
    } else {
        $stmt_rut = $conexion->prepare("SELECT id FROM usuarios WHERE rut = ? AND id != ?");
        $stmt_rut->bind_param("si", $rut, $usuario_id);
        $stmt_rut->execute();
        $stmt_rut->store_result();
        if ($stmt_rut->num_rows > 0) {
            $mensaje = "❌ El RUT ingresado ya está registrado.";
        }
        $stmt_rut->close();
    }

    if (!isset($mensaje)) {
        $email = $conexion->real_escape_string($_POST['email']);
        $edad = $conexion->real_escape_string($_POST['edad']);
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $genero = $conexion->real_escape_string($_POST['genero']);
        $direccion = $conexion->real_escape_string($_POST['direccion']);
        $numero_contacto = $conexion->real_escape_string($_POST['numero_contacto']);
        $region_id = $conexion->real_escape_string($_POST['region_id']);
        $comuna_id = $conexion->real_escape_string($_POST['comuna_id']);

        // Si se ingresa nueva contraseña
        $update_password = "";
        if (!empty($_POST['contrasena'])) {
            $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
            $update_password = ", contrasena = '$contrasena'";
        }

        $foto = $adoptante['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $extension = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
            $rut_limpio = preg_replace('/[^a-zA-Z0-9]/', '', $rut);
            $nombre_foto = date("Ymd_His") . "_" . $rut_limpio . "." . strtolower($extension);
            $ruta_destino = "fotos/" . $nombre_foto;
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $ruta_destino)) {
                $foto = $ruta_destino;
            }
        }

        $sql_update = "UPDATE usuarios SET
            nombre = '$nombre', rut = '$rut', email = '$email', edad = " . ($edad !== "" ? $edad : "NULL") . ",
            fecha_nacimiento = " . ($fecha_nacimiento ? "'$fecha_nacimiento'" : "NULL") . ",
            genero = '$genero', direccion = '$direccion', numero_contacto = '$numero_contacto',
            region_id = $region_id, comuna_id = $comuna_id, foto = " . ($foto ? "'$foto'" : "NULL") .
            "$update_password
            WHERE id = $usuario_id";

        if ($conexion->query($sql_update)) {
            header("Location: perfiladoptante.php?id=$usuario_id");
            exit();
        } else {
            $mensaje = "❌ Error al actualizar: " . $conexion->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar perfil de Adoptante</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print {
        input[type="file"], button {
            display: none;
        }
        }
        /* Menú desplegable */
        #btn-menu {
        display: none;
        }
        .menu-toggle {
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 50;
        }
        .menu-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        }
        #btn-menu:checked ~ .menu-container {
        display: block;
        }
        .side-menu {
        background-color: #1c1c1c;
        width: 250px;
        height: 100%;
        padding: 1.5rem 1rem;
        position: relative;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        }
        #btn-menu:checked ~ .menu-container .side-menu {
        transform: translateX(0);
        }
        .side-menu a {
        display: block;
        padding: 0.75rem 1rem;
        color: #c7c7c7;
        text-decoration: none;
        border-left: 4px solid transparent;
        transition: 0.3s;
        }
        .side-menu a:hover {
        border-left-color: #c7c7c7;
        background-color: #2a2a2a;
        }
        .close-menu {
        position: absolute;
        top: 1rem;
        right: 1rem;
        color: white;
        font-size: 1.25rem;
        cursor: pointer;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <!-- Botón menú -->
    <input type="checkbox" id="btn-menu">
    <div class="menu-toggle">
      <label for="btn-menu" class="cursor-pointer">
        <i data-lucide="menu" class="w-6 h-6 text-blue-700"></i>
      </label>
    </div>
  
    <!-- Menú desplegable -->
    <div class="menu-container">
      <div class="side-menu">
        <label for="btn-menu" class="close-menu">✕</label>
        <div class="mt-12 mb-4">
        </div>
        <nav>
          <?php
            $usuario_id = $_SESSION["usuario_id"];
            $usuario_tipo = $_SESSION["usuario_tipo"];
          ?>

          <?php if ($usuario_tipo === "Adoptante" || $usuario_tipo === "Administrador"): ?>
            <a href="<?= $usuario_tipo === 'Administrador' ? 'indexadmin.php' : 'index.php' ?>">Inicio</a>
          <?php endif; ?>

          <?php if ($usuario_tipo === "Adoptante"): ?>
            <a href="editaradoptante.php?id=<?= $usuario_id ?>">Editar Perfil</a>
            <a href="perfiladoptante.php?id=<?= $usuario_id ?>">Ver mi Perfil</a>
            <a href="indexrefugios.php">Refugios</a>
            <a href="mascotasadoptadas.php?id=<?= $usuario_id ?>">Mascotas Adoptadas</a>
            <a href="solicitudesadoptante.php?id=<?= $usuario_id ?>">Solicitudes de Adopción</a>

          <?php elseif ($usuario_tipo === "Refugio"): ?>
            <a href="editarrefugio.php?id=<?= $usuario_id ?>">Editar Perfil</a>
            <a href="perfilrefugio.php?id=<?= $usuario_id ?>">Ver mi Perfil</a>
            <a href="registromascota.php?id=<?= $usuario_id ?>">Registrar Mascota</a>

          <?php endif; ?>

          <a href="logout.php">Cerrar Sesión</a>
        </nav>
      </div>
    </div>
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-8">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900">Editar perfil de Adoptante</h1>
                <p class="mt-2 text-gray-600">Complete todos los campos requeridos</p>
                <?php if (isset($mensaje)): ?>
                    <p style="text-align:center; color:red; font-weight:bold;"><?= $mensaje ?></p>
                <?php endif; ?>
                <form id="workerForm" class="space-y-6" method="POST" enctype="multipart/form-data" action="editaradoptante.php">
                    <input type="hidden" name="id" value="<?= $adoptante['id'] ?>">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                    </div>

                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                        <input value="<?= htmlspecialchars($adoptante['nombre']) ?>" type="text" name="nombre" id="nombre" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="rut" class="block text-sm font-medium text-gray-700">RUT</label>
                        <input value="<?= htmlspecialchars($adoptante['rut']) ?>" type="text" name="rut" id="rut" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico Personal</label>
                        <input value="<?= htmlspecialchars($adoptante['email']) ?>" type="email" id="email" name="email" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="edad" class="block text-sm font-medium text-gray-700">Edad</label>
                        <input value="<?= htmlspecialchars($adoptante['edad']) ?>" type="number" id="edad" name="edad" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div class="form-group">
                      <label for="fecha_nacimiento" class="block mb-2 text-sm font-medium text-gray-700">Fecha de Nacimiento</label>
                      <input value="<?= htmlspecialchars($adoptante['fecha_nacimiento']) ?>" type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div class="form-group">
                      <label for="genero" class="block text-sm font-medium text-gray-700">Género</label>
                      <select id="genero" name="genero" class="w-full border p-2 rounded">
                            <option value="">Seleccionar</option>
                            <option value="Masculino" <?= $adoptante['genero'] === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                            <option value="Femenino" <?= $adoptante['genero'] === 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                            <option value="Otro" <?= $adoptante['genero'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
                            <option value="Prefiero no especificar" <?= $adoptante['genero'] === 'Prefiero no especificar' ? 'selected' : '' ?>>Prefiero no especificar</option>
                        </select>
                    </div>

                    <div>
                        <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                        <input value="<?= htmlspecialchars($adoptante['direccion']) ?>" type="text" id="direccion" name="direccion" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="numero_contacto" class="block text-sm font-medium text-gray-700">Número de Contacto</label>
                        <input value="<?= htmlspecialchars($adoptante['numero_contacto']) ?>" type="tel" id="numero_contacto" name="numero_contacto" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div class="form-group">
                      <label for="region_id" class="block text-sm font-medium text-gray-700">Región</label>
                      <select name="region_id" id="region" required class="w-full border p-2 rounded" required>
                          <option value="">Seleccione una región</option>
                            <?php foreach ($region as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= $adoptante['region_id'] == $r['id'] ? 'selected' : '' ?>>
                                    <?= $r['nombre'] ?>
                                </option>
                            <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="form-group">
                      <label for="comuna_id" class="block text-sm font-medium text-gray-700">Comuna</label>
                      <select name="comuna_id" id="comuna" required class="w-full border p-2 rounded">
                          <option value="">Seleccione una comuna</option>
                      </select>
                    </div>
                    
                    <div class="mb-4">
                      <label class="block mb-2 text-sm font-medium text-gray-700" for="foto">Foto de perfil (Opcional)</label>
                      <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700" id="foto" name="foto" type="file" accept="image/*">
                    </div>

                    <div>
                        <label for="contrasena" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                    </div>

                    <div class="col-span-2 flex justify-center space-x-4 pt-6">
                      <button type="submit" class="px-6 py-2 rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                          Guardar Cambios
                      </button>
                      <button type="button" class="px-6 py-2 rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700" onclick="window.location.href='index.php'">
                          Volver al Inicio
                      </button>
                    </div>
                  </form>
            </div>
        </div>
    </div>
    
    <script>
    document.getElementById('region').addEventListener('change', function () {
        const regionId = this.value;
        const comunaSelect = document.getElementById('comuna');

        comunaSelect.innerHTML = '<option value="">Cargando comunas...</option>';

        fetch('obtener_comunas.php?region_id=' + regionId)
            .then(response => response.json())
            .then(data => {
                comunaSelect.innerHTML = '<option value="">Seleccione una comuna</option>';
                data.forEach(comuna => {
                    const option = document.createElement('option');
                    option.value = comuna.id;
                    option.textContent = comuna.nombre;
                    comunaSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error al cargar comunas:', error);
                comunaSelect.innerHTML = '<option value="">Error al cargar</option>';
            });
    });
    </script>

    <script>
      lucide.createIcons();
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const regionId = document.getElementById('region').value;
            if (regionId) {
                document.getElementById('region').dispatchEvent(new Event('change'));
            }
        });
    </script>

</body>
</html>
