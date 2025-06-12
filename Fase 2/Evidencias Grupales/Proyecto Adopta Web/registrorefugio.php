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
    // Solo si no hay mensaje de error, seguimos con el procesamiento
    $representante = $conexion->real_escape_string($_POST['representante']);
    $email = $conexion->real_escape_string($_POST['email']);
    $horario = $conexion->real_escape_string($_POST['horario']);
    $numero_contacto = $conexion->real_escape_string($_POST['numero_contacto']);
    $redes_sociales = $conexion->real_escape_string($_POST['redes_sociales']);
    $direccion = $conexion->real_escape_string($_POST['direccion']);
    $especies = $conexion->real_escape_string($_POST['especies']);
    $fecha_creacion = $_POST['fecha_creacion'];
    $region_id = $conexion->real_escape_string($_POST['region_id']);
    $comuna_id = $conexion->real_escape_string($_POST['comuna_id']);
    $mapa_url = $conexion->real_escape_string($_POST["mapa_url"]);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT); // Encriptar contraseña
    $tipo = 'Refugio';

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

    // Guardar documentos
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

    // Insertar usuario
    $sql = "INSERT INTO usuarios (
        nombre, rut, representante, email, horario, numero_contacto, redes_sociales, 
        direccion, especies, fecha_creacion, region_id, comuna_id, mapa_url, descripcion, contrasena, tipo, foto
        ) VALUES (
            '$nombre', '$rut', '$representante', '$email', '$horario', '$numero_contacto', '$redes_sociales', 
            '$direccion', '$especies', " . ($fecha_creacion ? "'$fecha_creacion'" : "NULL") . ", 
            $region_id, $comuna_id, '$mapa_url', '$descripcion', '$contrasena', '$tipo', " . ($foto ? "'$foto'" : "NULL") . "
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
  <title>Crear perfil de Refugio</title>
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
                <h1 class="text-3xl font-bold text-gray-900">Crear perfil de Refugio</h1>
                <p class="mt-2 text-gray-600">Complete todos los campos requeridos</p>
                <?php if (isset($mensaje)): ?>
                    <p style="text-align:center; color:red; font-weight:bold;"><?= $mensaje ?></p>
                <?php endif; ?>
                <form id="workerForm" class="space-y-6" method="POST" enctype="multipart/form-data" action="registrorefugio.php">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                    </div>

                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Refugio</label>
                        <input type="text" name="nombre" id="nombre" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="rut" class="block text-sm font-medium text-gray-700">RUT</label>
                        <input type="text" name="rut" id="rut" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="representante" class="block text-sm font-medium text-gray-700">Representante Legal</label>
                        <input type="text" name="representante" id="representante" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico del Refugio</label>
                        <input type="email" id="email" name="email" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Horarios</label>
                        <textarea name="horario" rows="4" class="w-full px-4 py-2 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Redes Sociales</label>
                        <textarea name="redes_sociales" rows="4" class="w-full px-4 py-2 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required></textarea>
                    </div>

                    <div>
                        <label for="numero_contacto" class="block text-sm font-medium text-gray-700">Número de Contacto</label>
                        <input type="tel" id="numero_contacto" name="numero_contacto" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="direccion" class="block text-sm font-medium text-gray-700">Ubicación</label>
                        <input type="text" name="direccion" id="direccion" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Especies</label>
                        <textarea name="especies" rows="4" class="w-full px-4 py-2 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ubicación de Google Maps (Iframe)</label>
                        <textarea name="mapa_url" rows="4" class="w-full px-4 py-2 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required></textarea>
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

                    <div class="form-group">
                      <label for="fecha_creacion" class="block mb-2 text-sm font-medium text-gray-700">Fecha de Creación</label>
                      <input type="date" id="fecha_creacion" name="fecha_creacion" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>
                    
                    <div class="mb-4">
                      <label class="block mb-2 text-sm font-medium text-gray-700" for="foto">Foto del logo</label>
                      <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700" id="foto" name="foto" type="file" accept="image/*" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea name="descripcion" rows="4" class="w-full px-4 py-2 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required></textarea>
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
                          Registrar Refugio
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
