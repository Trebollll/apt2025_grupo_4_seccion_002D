<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || !isset($_SESSION["usuario_tipo"])) {
    header("Location: login.php");
    exit();
}

// Verificar que sea Refugio
if ($_SESSION["usuario_tipo"] !== "Refugio") {
    echo "<p style='color:red; text-align:center;'>Acceso denegado. Solo los refugios pueden editar mascotas.</p>";
    exit();
}

// Validar que la mascota pertenezca al refugio
$mascota_id = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $mascota_id = intval($_POST['id']);
    }
} else {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $mascota_id = intval($_GET['id']);
    }
}

if (!$mascota_id) {
    die("ID de mascota no válido.");
}

include("conexion.php");
$conexion->set_charset("utf8mb4");

// Verificar que la mascota pertenezca al refugio
$stmt = $conexion->prepare("SELECT * FROM mascotas WHERE id = ? AND refugio_id = ?");
$stmt->bind_param("ii", $mascota_id, $_SESSION["usuario_id"]);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "<p style='color:red; text-align:center;'>No tienes permisos para editar esta mascota.</p>";
    exit();
}

$mascota = $resultado->fetch_assoc();

// Cargar regiones
$region = [];
$sql_region = "SELECT id, nombre FROM region";
$resultado_region = $conexion->query($sql_region);

if ($resultado_region && $resultado_region->num_rows > 0) {
    while ($fila = $resultado_region->fetch_assoc()) {
        $region[] = $fila;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $sexo = $conexion->real_escape_string($_POST['sexo']);
    $raza = $conexion->real_escape_string($_POST['raza']);
    $estado_salud = $conexion->real_escape_string($_POST['estado_salud']);
    $especie = $conexion->real_escape_string($_POST['especie']);
    $edad = is_numeric($_POST['edad']) ? intval($_POST['edad']) : null;
    $nro_chip = $conexion->real_escape_string($_POST['nro_chip']);
    $tamaño = $conexion->real_escape_string($_POST['tamaño']);
    $region_id = intval($_POST['region_id']);
    $comuna_id = intval($_POST['comuna_id']);

    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
        $nombre_foto = date("Ymd_His") . "." . strtolower($extension);
        $ruta_destino = "fotos/" . $nombre_foto;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $ruta_destino)) {
            $foto = $ruta_destino;
        }
    }


    $sql = "UPDATE mascotas SET 
        nombre = ?, sexo = ?, raza = ?, estado_salud = ?, especie = ?, edad = ?, nro_chip = ?, tamaño = ?, 
        region_id = ?, comuna_id = ?, foto = IFNULL(?, foto)
        WHERE id = ? AND refugio_id = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssssissssii", 
        $nombre, $sexo, $raza, $estado_salud, $especie, $edad, $nro_chip, $tamaño,
        $region_id, $comuna_id, $foto, $mascota_id, $_SESSION["usuario_id"]
        );

    if ($stmt->execute()) {
        header("Location: perfilrefugio.php?id=" . $_SESSION["usuario_id"]);
        exit();
    } else {
        $mensaje = "❌ Error al actualizar: " . $conexion->error;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar perfil de Mascota</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
    <style>
    @media print {
        input[type="file"], button {
        display: none;
        }
    }

    .dark-mode {
        background-color: #1a1a1a;
        color: #e0e0e0;
    }

    .dark-mode .bg-white {
        background-color: #2c2c2c !important;
    }

    .dark-mode .text-gray-800 {
        color: #e0e0e0 !important;
    }

    .dark-mode .text-gray-500 {
        color: #c0c0c0 !important;
    }

    .dark-mode .bg-gray-200 {
        background-color: #3a3a3a !important;
    }

    .dark-mode .bg-gray-600 {
        background-color: #444 !important;
    }

    .dark-mode .border-gray-300 {
        border-color: #555 !important;
    }

    /* Menú desplegable con transición */
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
        background-color: rgba(0, 0, 0, 0);
        opacity: 0;
        pointer-events: none;
        transition: background-color 0.3s ease, opacity 0.3s ease;
        z-index: 40;
    }

    #btn-menu:checked ~ .menu-container {
        background-color: rgba(0, 0, 0, 0.5);
        opacity: 1;
        pointer-events: auto;
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

    .dark-mode .side-menu {
        background-color: #2a2a2a;
    }

    .dark-mode .side-menu a {
        color: #dddddd;
    }

    .dark-mode .side-menu a:hover {
        background-color: #3a3a3a;
        border-left-color: #ffffff;
    }

    .dark-mode .close-menu {
        color: #ffffff;
    }

    .dark-mode .menu-container {
        background-color: rgba(0, 0, 0, 0.6);
    }

    /* Transiciones suaves */
    body,
    .bg-white,
    .text-gray-800,
    .text-gray-500,
    .bg-gray-200,
    .bg-gray-600,
    .border-gray-300,
    .side-menu,
    .side-menu a,
    .menu-container {
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, opacity 0.3s ease, transform 0.3s ease;
    }

    .menu-icono {
        color: #000000;
        transition: color 0.3s ease;
    }

    .dark-mode .menu-icono {
        color: #ffffff;
    }

    .dark-mode,
    .dark-mode body {
        background-color: #1a1a1a !important;
        color: #e0e0e0 !important;
    }
    /* ==== CAMPOS DE FORMULARIO EN MODO OSCURO ==== */
    .dark-mode input,
    .dark-mode select,
    .dark-mode textarea {
    background-color: #2c2c2c !important;
    color: #ffffff !important;
    border-color: #555 !important;
    }

    .dark-mode select option {
    background-color: #2c2c2c !important;
    color: #ffffff !important;
    }

    /* ==== TEXTO DENTRO DE LOS CARDS ==== */
    .dark-mode .card {
    background-color: #2c2c2c !important;
    border-color: #555 !important;
    }

    .dark-mode .card p,
    .dark-mode .card span {
    color: #ffffff !important;
    }

    .dark-mode .card .text-gray-500 {
    color: #cccccc !important;
    }

    /* ==== BOTONES EN MODO OSCURO ==== */
    .dark-mode .card button {
    background-color: #444 !important;
    color: #ffffff !important;
    }

    .dark-mode .card button:hover {
    background-color: #555 !important;
    }

    .dark-mode h1,
    .dark-mode p {
    color: #ffffff !important;
    }

    /* Etiquetas de campos en modo nocturno */
    .dark-mode label {
    color: #ffffff !important;
    }

    /* Botones en modo nocturno */
    .dark-mode button,
    .dark-mode input[type="submit"],
    .dark-mode input[type="button"],
    .dark-mode .file\:bg-blue-600 {
    background-color: #444 !important;
    color: #fff !important;
    border-color: #555 !important;
    }

    /* Hover para botones en modo nocturno */
    .dark-mode button:hover,
    .dark-mode input[type="submit"]:hover,
    .dark-mode input[type="button"]:hover,
    .dark-mode .file\:bg-blue-600:hover {
    background-color: #555 !important;
    }

    /* Estilo en modo nocturno para el botón "Seleccionar archivo" */
    .dark-mode input[type="file"]::file-selector-button {
    background-color: #1f1f1f !important;  /* Gris oscuro tirando a negro */
    color: #ffffff !important;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    }

    /* Hover más claro en modo nocturno */
    .dark-mode input[type="file"]::file-selector-button:hover {
    background-color: #2d2d2d !important;
    }
    </style>
</head>

<body class="bg-gray-100 text-gray-800">
    <!-- Botón menú -->
    <input type="checkbox" id="btn-menu">
    <div class="menu-toggle">
      <label for="btn-menu" class="cursor-pointer">
        <i data-lucide="menu" class="menu-icono w-6 h-6"></i>
      </label>
    </div>

    <div class="fixed top-1 right-1 z-50">
        <button id="modoToggle" class="bg-gray-700 text-white px-3 py-2 rounded shadow flex items-center justify-center w-10 h-10">
            <i id="modoIcono" data-lucide="moon" class="w-5 h-5"></i>
        </button>
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
        <div class="max-w-3xl mx-auto bg-white border border-gray-300 shadow-[0_4px_10px_rgba(0,0,0,0.1)] rounded-2xl p-8 transition-all duration-300">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900">Editar perfil de Mascota</h1>
                <p class="mt-2 text-gray-600">Complete todos los campos requeridos</p>
                <?php if (isset($mensaje)): ?>
                    <p style="text-align:center; color:green; font-weight:bold;"><?= $mensaje ?></p>
                <?php endif; ?>
                <form id="workerForm" class="space-y-6" method="POST" enctype="multipart/form-data" action="editarmascota.php">
                    <input type="hidden" name="id" value="<?= $mascota['id'] ?>">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                    </div>

                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                        <input value="<?= htmlspecialchars($mascota['nombre']) ?>" type="text" name="nombre" id="nombre" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div class="form-group">
                      <label for="sexo" class="block text-sm font-medium text-gray-700">Sexo</label>
                      <select id="sexo" name="sexo" class="w-full border p-2 rounded">
                        <option value="">Seleccionar</option>
                        <option value="Macho" <?= $mascota['sexo'] === 'Macho' ? 'selected' : '' ?>>Macho</option>
                        <option value="Hembra" <?= $mascota['sexo'] === 'Hembra' ? 'selected' : '' ?>>Hembra</option>
                      </select>
                    </div>

                    <div>
                        <label for="especie" class="block text-sm font-medium text-gray-700">Especie</label>
                        <input value="<?= htmlspecialchars($mascota['especie']) ?>" type="text" id="especie" name="especie" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="raza" class="block text-sm font-medium text-gray-700">Raza</label>
                        <input value="<?= htmlspecialchars($mascota['raza']) ?>" type="text" id="raza" name="raza" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="estado_salud" class="block text-sm font-medium text-gray-700">Estado de Salud</label>
                        <input value="<?= htmlspecialchars($mascota['estado_salud']) ?>" type="text" id="estado_salud" name="estado_salud" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="edad" class="block text-sm font-medium text-gray-700">Edad</label>
                        <input value="<?= htmlspecialchars($mascota['edad']) ?>" type="number" id="edad" name="edad" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="nro_chip" class="block text-sm font-medium text-gray-700">N° de Chip</label>
                        <input value="<?= htmlspecialchars($mascota['nro_chip']) ?>" type="text" id="nro_chip" name="nro_chip" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div>
                        <label for="tamaño" class="block text-sm font-medium text-gray-700">Tamaño</label>
                        <input value="<?= htmlspecialchars($mascota['tamaño']) ?>" type="text" id="tamaño" name="tamaño" class="w-full px-4 py-1 border border-gray-300 bg-white text-black rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
                    </div>

                    <div class="form-group">
                      <label for="region_id" class="block text-sm font-medium text-gray-700">Región</label>
                      <select name="region_id" id="region" required class="w-full border p-2 rounded" required>
                          <option value="">Seleccione una región</option>
                          <?php foreach ($region as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $r['id'] == $mascota['region_id'] ? 'selected' : '' ?>>
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
                      <label class="block mb-2 text-sm font-medium text-gray-700" for="foto">Foto de la mascota</label>
                      <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700" id="foto" name="foto" type="file" accept="image/*">
                    </div>

                    <div class="col-span-2 flex justify-center space-x-4 pt-6">
                        <button type="submit" class="px-6 py-2 rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            Guardar Cambios
                        </button>
                        <button type="button" 
                                class="px-6 py-2 rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700" 
                                onclick="window.location.href='perfilrefugio.php?id=<?= $_SESSION['usuario_id'] ?>'">
                            Volver al perfil
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
  const btnToggle = document.getElementById("modoToggle");
  const html = document.documentElement;

  function actualizarIconoModo() {
    const modoActual = html.classList.contains("dark-mode");
    const nuevoIcono = modoActual ? "sun" : "moon";
    btnToggle.innerHTML = `<i id="modoIcono" data-lucide="${nuevoIcono}" class="w-5 h-5"></i>`;
    lucide.createIcons();
  }

  if (localStorage.getItem("modo") === "oscuro") {
    html.classList.add("dark-mode");
  }
  actualizarIconoModo();

  btnToggle.addEventListener("click", () => {
    html.classList.toggle("dark-mode");
    localStorage.setItem("modo", html.classList.contains("dark-mode") ? "oscuro" : "claro");
    actualizarIconoModo();
  });
</script>

<script>
  lucide.createIcons();
</script>


</body>
</html>
