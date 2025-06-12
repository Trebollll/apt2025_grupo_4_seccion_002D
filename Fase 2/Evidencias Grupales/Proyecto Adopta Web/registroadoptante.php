<?php

session_start();

error_reporting(E_ALL);
ini_set("display_errors", 1);

if (isset($_SESSION["usuario_id"])) {
    header("Location: index.php");
    exit();
}

include("conexion.php");

$conexion->set_charset("utf8mb4");

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

    // Escapar y recibir datos del formulario
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $rut = $conexion->real_escape_string($_POST['rut']);
    // Validar formato del RUT
    if (!validarRut($rut)) {
        $mensaje = "❌ El RUT ingresado no es válido.";
    } else {
        // Verificar si el RUT ya existe
        $stmt_rut = $conexion->prepare("SELECT id FROM usuarios WHERE rut = ?");
        $stmt_rut->bind_param("s", $rut);
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
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT); // Encriptar contraseña
    $tipo = 'Adoptante';

    // Manejo de la foto
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
        $rut_limpio = preg_replace('/[^a-zA-Z0-9]/', '', $rut);
        $nombre_foto = date("Ymd_His") . "_" . $rut_limpio . "." . strtolower($extension);
        $ruta_destino = "fotos/" . $nombre_foto;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $ruta_destino)) {
            $foto = $ruta_destino;
        }
    }

    // Documentos
    $documentos_guardados = [];
    if (isset($_FILES['documentos']) && count($_FILES['documentos']['name']) > 0) {
        $directorio_doc = "documentos/";
        if (!is_dir($directorio_doc)) {
            mkdir($directorio_doc, 0777, true);
        }

        foreach ($_FILES['documentos']['tmp_name'] as $index => $tmp_name) {
            if ($_FILES['documentos']['error'][$index] === UPLOAD_ERR_OK) {
                $nombre_original = basename($_FILES['documentos']['name'][$index]);
                $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                $nombre_archivo = date("Ymd_His") . "_" . uniqid() . "." . strtolower($extension);
                $ruta_archivo = $directorio_doc . $nombre_archivo;

                if (move_uploaded_file($tmp_name, $ruta_archivo)) {
                    $documentos_guardados[] = $ruta_archivo;
                }
            }
        }
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO usuarios (
        nombre, rut, email, edad, fecha_nacimiento, genero,
        direccion, numero_contacto, region_id, comuna_id,
        contrasena, tipo, foto
    ) VALUES (
        '$nombre', '$rut', '$email', " . ($edad !== "" ? $edad : "NULL") . ", " .
        ($fecha_nacimiento ? "'$fecha_nacimiento'" : "NULL") . ", '$genero',
        '$direccion', '$numero_contacto', $region_id, $comuna_id,
        '$contrasena', '$tipo', " . ($foto ? "'$foto'" : "NULL") . "
    )";

    if ($conexion->query($sql)) {
        $nuevo_id = $conexion->insert_id;
        if (!empty($documentos_guardados)) {
            $stmt_doc = $conexion->prepare("INSERT INTO documentos (usuario_id, ruta, fecha_subida) VALUES (?, ?, NOW())");
            foreach ($documentos_guardados as $ruta) {
                $stmt_doc->bind_param("is", $nuevo_id, $ruta);
                $stmt_doc->execute();
            }
        }
        header("Location: login.php");
        exit();
    } else {
        $mensaje = "❌ Error: " . $conexion->error;
    }
}

$conexion->close();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear perfil de Adoptante</title>
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
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-8">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900">Crear perfil de Adoptante</h1>
                <p class="mt-2 text-gray-600">Complete todos los campos requeridos</p>
                <?php if (isset($mensaje)): ?>
                    <p style="text-align:center; color:red; font-weight:bold;"><?= $mensaje ?></p>
                <?php endif; ?>
                <form id="workerForm" class="space-y-6" method="POST" enctype="multipart/form-data" action="registroadoptante.php">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                    </div>

                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                        <input type="text" name="nombre" id="nombre" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="rut" class="block text-sm font-medium text-gray-700">RUT</label>
                        <input type="text" name="rut" id="rut" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico Personal</label>
                        <input type="email" id="email" name="email" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="edad" class="block text-sm font-medium text-gray-700">Edad</label>
                        <input type="number" id="edad" name="edad" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div class="form-group">
                      <label for="fecha_nacimiento" class="block mb-2 text-sm font-medium text-gray-700">Fecha de Nacimiento</label>
                      <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div class="form-group">
                      <label for="genero" class="block text-sm font-medium text-gray-700">Género</label>
                      <select id="genero" name="genero" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out">
                          <option value="">Seleccionar</option>
                          <option value="Masculino">Masculino</option>
                          <option value="Femenino">Femenino</option>
                          <option value="Otro">Otro</option>
                          <option value="Prefiero no especificar">Prefiero no especificar</option>
                      </select>
                    </div>

                    <div>
                        <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                        <input type="text" id="direccion" name="direccion" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="numero_contacto" class="block text-sm font-medium text-gray-700">Número de Contacto</label>
                        <input type="tel" id="numero_contacto" name="numero_contacto" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div class="form-group">
                      <label for="region_id" class="block text-sm font-medium text-gray-700">Región</label>
                      <select name="region_id" id="region" required class="w-full border p-2 rounded" required>
                          <option value="">Seleccione una región</option>
                          <?php foreach ($region as $r): ?>
                              <option value="<?= $r['id'] ?>"><?= $r['nombre'] ?></option>
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
                        <input type="password" id="contrasena" name="contrasena" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Documentación (PDF, DOC, JPG, PNG)</label>
                        <input type="file" name="documentos[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 
                            focus:outline-none file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold 
                            file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                        <p class="text-xs text-gray-500 mt-1">Puede subir varios archivos.</p>
                    </div>

                    <div class="col-span-2 flex justify-center space-x-4 pt-6">
                      <button type="submit" class="px-6 py-2 rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                          Registrar Adoptante
                      </button>
                      <button type="button" class="px-6 py-2 rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700" onclick="window.location.href='login.php'">
                          Volver al Inicio de Sesión
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

</body>
</html>
