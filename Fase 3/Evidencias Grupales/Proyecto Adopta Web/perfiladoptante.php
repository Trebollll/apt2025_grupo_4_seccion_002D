<?php

session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

include("conexion.php");

$conexion->set_charset("utf8mb4");

// Obtener ID desde la URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$resultado = $conexion->query("
    SELECT usuarios.*, region.nombre AS nombre_region, comuna.nombre AS nombre_comuna
    FROM usuarios
    LEFT JOIN region ON usuarios.region_id = region.id
    LEFT JOIN comuna ON usuarios.comuna_id = comuna.id
    WHERE usuarios.id = $id
");


if ($resultado->num_rows === 0) {
    echo "<p class='text-center text-red-600'>Adoptante no encontrado.</p>";
    exit();
}

$usuarios = $resultado->fetch_assoc();

$usuario_tipo = $_SESSION["usuario_tipo"];
$usuario_id_logueado = intval($_SESSION["usuario_id"]);
$perfil_id = intval($usuarios['id']);

if (
    $usuario_tipo === 'Adoptante' &&
    $usuario_id_logueado !== $perfil_id
) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Perfil no válido</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center h-screen">
        <div class="bg-white border border-red-200 rounded-lg shadow-md p-8 max-w-md text-center">
            <div class="text-red-600 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414 1.414a9 9 0 11-1.414-1.414L18.364 5.636z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01" />
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-800">Solo puedes ver tu propio perfil de adoptante.</h2>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Obtener documentos del adoptante
$documentos = [];
$consulta_docs = $conexion->prepare("SELECT id, ruta, fecha_subida FROM documentos WHERE usuario_id = ?");
$consulta_docs->bind_param("i", $id);
$consulta_docs->execute();
$resultado_docs = $consulta_docs->get_result();

while ($doc = $resultado_docs->fetch_assoc()) {
    $documentos[] = $doc;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Perfil de Adoptante</title>
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

    .dark-mode .bg-gray-50 {
      background-color: #1f1f1f !important;
    }

    .dark-mode .text-gray-700 {
      color: #ffffff !important;
    }

    .dark-mode .text-gray-600 {
      color: #cccccc !important;
    }

    .dark-mode .text-sm.text-gray-500 {
      color: #bbbbbb !important;
    }

    .dark-mode a.bg-blue-600 {
      background-color: #444 !important;
      color: #ffffff !important;
      transition: background-color 0.3s ease;
    }

    .dark-mode a.bg-blue-600:hover {
      background-color: #555 !important;
    }

    body{
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160'%3E%3Cg transform='rotate(-20 80 80)' fill='%23000000' fill-opacity='0.08'%3E%3Ccircle cx='50' cy='50' r='12'/%3E%3Ccircle cx='80' cy='38' r='12'/%3E%3Ccircle cx='110' cy='50' r='12'/%3E%3Cellipse cx='80' cy='95' rx='30' ry='24'/%3E%3C/g%3E%3C/svg%3E");
    background-size:180px 180px;
    background-repeat:repeat;
  }

  .dark-mode body{
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160'%3E%3Cg transform='rotate(-20 80 80)' fill='%23ffffff' fill-opacity='0.08'%3E%3Ccircle cx='50' cy='50' r='12'/%3E%3Ccircle cx='80' cy='38' r='12'/%3E%3Ccircle cx='110' cy='50' r='12'/%3E%3Cellipse cx='80' cy='95' rx='30' ry='24'/%3E%3C/g%3E%3C/svg%3E");
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

  <div class="container mx-auto px-4 py-8">
  <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6 md:p-8">

    <!-- Volver y Editar -->
    <div class="flex justify-between items-center mb-6">
      <a href="javascript:history.back()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Volver</a>
      <?php if (
          $_SESSION['usuario_tipo'] === 'Administrador' ||
          ($_SESSION['usuario_tipo'] === 'Adoptante' && $_SESSION['usuario_id'] == $usuarios['id'])
      ): ?>
          <a href="editaradoptante.php?id=<?= $usuarios['id']; ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Editar Perfil</a>
      <?php endif; ?>
    </div>

    <!-- Perfil Principal -->
    <div class="text-center mb-8">
      <div class="w-32 h-32 mx-auto mb-4">
        <?php if (!empty($usuarios['foto']) && file_exists($usuarios['foto'])): ?>
          <img src="<?php echo htmlspecialchars($usuarios['foto']); ?>" class="rounded-full w-full h-full object-cover border-4 border-blue-100 shadow-lg" alt="Foto perfil">
        <?php else: ?>
          <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usuarios['nombre']); ?>" class="rounded-full w-full h-full object-cover border-4 border-blue-100 shadow-lg" alt="Foto perfil">
        <?php endif; ?>
      </div>
      <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($usuarios['nombre']); ?></h1>
    </div>

    <!-- Información Personal -->
    <div class="border-t pt-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Información Personal</h2>
      <div class="grid md:grid-cols-2 gap-6 mb-8">
        <div class="space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="font-semibold text-gray-700">Nombre Completo</h2>
            <p class="text-gray-600"><?= htmlspecialchars($usuarios['nombre']) ?></p>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="font-semibold text-gray-700">Fecha de Nacimiento</h2>
            <p class="text-gray-600"><?= htmlspecialchars($usuarios['fecha_nacimiento']) ?></p>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="font-semibold text-gray-700">Correo Personal</h2>
            <p class="text-gray-600"><?= htmlspecialchars($usuarios['email']) ?></p>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="font-semibold text-gray-700">RUT</h2>
            <p class="text-gray-600"><?= htmlspecialchars($usuarios['rut']) ?></p>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="font-semibold text-gray-700">Región</h2>
            <p class="text-gray-600"><?= htmlspecialchars($usuarios['nombre_region']) ?></p>
          </div>
        </div>
        <div class="space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="font-semibold text-gray-700">Número de contacto</h2>
            <p class="text-gray-600"><?= htmlspecialchars($usuarios['numero_contacto']) ?></p>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="font-semibold text-gray-700">Género</h2>
            <p class="text-gray-600"><?= htmlspecialchars($usuarios['genero']) ?></p>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="font-semibold text-gray-700">Edad</h2>
            <p class="text-gray-600"><?= htmlspecialchars($usuarios['edad']) ?></p>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="font-semibold text-gray-700">Dirección</h2>
            <p class="text-gray-600"><?= htmlspecialchars($usuarios['direccion']) ?></p>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="font-semibold text-gray-700">Comuna</h2>
            <p class="text-gray-600"><?= htmlspecialchars($usuarios['nombre_comuna']) ?></p>
          </div>
        </div>
      </div>
      <!-- Documentación -->
      <div class="border-t pt-6 mt-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Documentación</h2>

        <?php if (empty($documentos)): ?>
          <p class="text-gray-600">No hay documentos subidos por este adoptante.</p>
        <?php else: ?>
          <ul class="space-y-4">
            <?php foreach ($documentos as $doc): ?>
              <li class="flex items-center justify-between bg-gray-50 p-4 rounded-lg shadow-sm">
                <div>
                  <p class="font-semibold text-gray-700">
                    Documento subido el <?= date("d/m/Y H:i", strtotime($doc['fecha_subida'])) ?>
                  </p>
                  <p class="text-sm text-gray-500 break-all"><?= htmlspecialchars($doc['ruta']) ?></p>
                </div>
                <div class="flex space-x-2">
                  <a href="<?= htmlspecialchars($doc['ruta']) ?>" target="_blank" class="px-4 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Ver</a>
                  <a href="<?= htmlspecialchars($doc['ruta']) ?>" download class="px-4 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">Descargar</a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

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
