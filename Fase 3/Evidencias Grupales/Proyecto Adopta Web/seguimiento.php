<?php
session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

include("conexion.php");
$conexion->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mascota_id'])) {
    // PROCESAR FORMULARIO
    $mascota_id = intval($_POST['mascota_id']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);
    $fecha = date("Y-m-d H:i:s");
    $titulo = $conexion->real_escape_string($_POST['titulo']);
    $ruta_foto = null;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $directorio = "uploads/seguimientos/";
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $extension = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
        $nombre_archivo = uniqid("seg_", true) . "." . strtolower($extension);
        $ruta_destino = $directorio . $nombre_archivo;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $ruta_destino)) {
            $ruta_foto = $ruta_destino;
        }
    }

    $stmt = $conexion->prepare("INSERT INTO seguimiento_mascotas (mascota_id, titulo, descripcion, fecha, foto) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $mascota_id, $titulo, $descripcion, $fecha, $ruta_foto);
    $stmt->execute();

    header("Location: seguimiento.php?id=$mascota_id");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p class='text-center text-red-600'>Mascota no especificada.</p>";
    exit();
}

$mascota_id = intval($_GET['id']);

// Obtener información de la mascota
$sql_mascota = "SELECT m.*, u.nombre AS nombre_adoptante FROM mascotas m 
                LEFT JOIN usuarios u ON m.adoptante_id = u.id 
                WHERE m.id = ?";
$stmt = $conexion->prepare($sql_mascota);
$stmt->bind_param("i", $mascota_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='text-center text-red-600'>Mascota no encontrada.</p>";
    exit();
}

$mascota = $result->fetch_assoc();

$usuario_id = $_SESSION["usuario_id"];
$usuario_tipo = $_SESSION["usuario_tipo"];

$es_adoptante_valido = $usuario_tipo === 'Adoptante' && $usuario_id == $mascota['adoptante_id'];
$es_refugio_valido = $usuario_tipo === 'Refugio' && $usuario_id == $mascota['refugio_id'];
$es_admin = $usuario_tipo === 'Administrador';

if (!($es_admin || $es_adoptante_valido || $es_refugio_valido)) {
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
            <h2 class="text-xl font-semibold text-gray-800">No tienes permisos para ver el seguimiento de esta mascota.</h2>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Obtener seguimiento
$sql_seg = "SELECT * FROM seguimiento_mascotas WHERE mascota_id = ? ORDER BY fecha DESC";
$stmt = $conexion->prepare($sql_seg);
$stmt->bind_param("i", $mascota_id);
$stmt->execute();
$result_seguimiento = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguimiento de Mascota</title>
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

  .dark-mode #toggle-filtros {
    background-color: #444 !important;
    color: #ffffff !important;
  }

  .dark-mode #toggle-filtros:hover {
    background-color: #555 !important;
  }

  .dark-mode .mascota-lista span.bg-gray-200 {
    background-color: #3a3a3a !important;
    color: #ffffff !important;
  }

  .dark-mode form button[type="submit"] {
    background-color: #444 !important;
    color: #ffffff !important;
  }

  .dark-mode form button[type="submit"]:hover {
    background-color: #555 !important;
  }

  /* Transiciones suaves para modo nocturno y menú */
  body,
  .bg-white,
  .text-gray-800,
  .text-gray-500,
  .bg-gray-200,
  .bg-gray-600,
  .border-gray-300,
  .side-menu,
  .side-menu a,
  #toggle-filtros,
  form button[type="submit"],
  .mascota-lista span.bg-gray-200,
  .menu-container {
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, opacity 0.3s ease, transform 0.3s ease;
  }

  .dark-mode,
  .dark-mode body {
    background-color: #1a1a1a !important;
    color: #e0e0e0 !important;
  }

  .dark-mode select {
    background-color: #2c2c2c;
    color: #ffffff;
    border-color: #555;
  }

  .dark-mode select option {
    background-color: #2c2c2c;
    color: #ffffff;
  }

  .dark-mode input[type="text"] {
    background-color: #2c2c2c;
    color: #ffffff;
    border-color: #555;
  }

  .dark-mode .mascota-lista button:hover {
    background-color: #666 !important;
  }

  .header-elegante h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 600;
    position: relative;
    display: inline-block;
    padding-bottom: 0.5rem;
    color: #000000;
    transition: color 0.3s ease;
  }

  .dark-mode .header-elegante h1 {
    color: #ffffff;
  }

  .header-elegante h1::after {
    content: "";
    display: block;
    width: 60%;
    height: 3px;
    background-color: #000;
    margin: 0.5rem auto 0;
    transition: background-color 0.3s ease;
  }

  .dark-mode .header-elegante h1::after {
    background-color: #ffffff;
  }

  .menu-icono {
    color: #000000;
    transition: color 0.3s ease;
  }

  .dark-mode .menu-icono {
    color: #ffffff;
  }

  /* Texto vacío en modo nocturno */
  .dark-mode p.text-gray-600 {
    color: #ffffff !important;
  }

  /* Botones personalizados del seguimiento (modo nocturno) */
  .dark-mode .bg-blue-600 {
    background-color: #005a9e !important;
    color: #ffffff !important;
  }

  .dark-mode .bg-blue-600:hover {
    background-color: #007acc !important;
  }

  /* Botón seleccionar archivo (modo nocturno) */
  .dark-mode label[for="foto"] {
    background-color: #005a9e !important;
    color: #ffffff !important;
  }

  .dark-mode label[for="foto"]:hover {
    background-color: #007acc !important;
  }

  /* Modal en modo nocturno */
  .dark-mode #modalContenido {
    background-color: #2c2c2c !important;
    color: #ffffff !important;
  }

  .dark-mode #modalContenido input,
  .dark-mode #modalContenido textarea {
    background-color: #1a1a1a !important;
    color: #ffffff !important;
    border-color: #555 !important;
  }

  .dark-mode #modalContenido label {
    color: #ffffff !important;
  }

  .dark-mode #modalContenido span#nombre-archivo {
    color: #e0e0e0 !important;
  }

  .dark-mode #modalContenido button[type="submit"] {
    background-color: #005a9e !important;
    color: #ffffff !important;
  }

  .dark-mode #modalContenido button[type="submit"]:hover {
    background-color: #007acc !important;
  }

  /* Seguimientos en modo normal */
  details {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ddd;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  }

  details summary {
    color: #1a56db;
    font-weight: 600;
    cursor: pointer;
  }

  details p {
    color: #444;
  }

  details strong {
    color: #222;
  }

  details img {
    border: 1px solid #ccc;
  }

  /* Seguimientos en modo nocturno */
  .dark-mode details {
    background-color: #1f1f1f !important;
    color: #ffffff !important;
    border: 1px solid #444 !important;
  }

  .dark-mode details summary {
    color: #aad4ff !important;
  }

  .dark-mode details p {
    color: #e0e0e0 !important;
  }

  .dark-mode details strong {
    color: #ffffff !important;
  }

  .dark-mode details img {
    border: 1px solid #555 !important;
  }

  /* Estilos base del contenedor animado */
  details .contenido-seguimiento {
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    transition: max-height 0.4s ease, opacity 0.4s ease;
  }

  /* Cuando está abierto */
  details[open] .contenido-seguimiento {
    max-height: 1000px;
    opacity: 1;
  }

  .max-h-0 {
    max-height: 0 !important;
  }
  .max-h-\[500px\] {
    max-height: 500px !important;
  }
  .opacity-0 {
    opacity: 0 !important;
  }
  .opacity-100 {
    opacity: 1 !important;
  }
  .rotate-180 {
    transform: rotate(180deg) !important;
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
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Seguimiento de <?= htmlspecialchars($mascota['nombre']) ?></h1>

            <div class="mb-8 grid md:grid-cols-3 gap-4">
                <div><strong>Especie:</strong> <?= htmlspecialchars($mascota['especie']) ?></div>
                <div><strong>Raza:</strong> <?= htmlspecialchars($mascota['raza']) ?></div>
                <div><strong>Adoptante:</strong> <?= htmlspecialchars($mascota['nombre_adoptante']) ?></div>
            </div>

            <h2 class="text-xl font-semibold mb-4 border-b pb-2">Historial de Seguimiento</h2>

              <?php if ($result_seguimiento->num_rows > 0): ?>
                <div class="space-y-4">
                  <?php while ($seg = $result_seguimiento->fetch_assoc()): ?>
                    <details class="detalle-item bg-gray-50 dark:bg-[#242424] rounded-lg shadow p-4 overflow-hidden transition-all duration-300">
                      <summary class="cursor-pointer text-lg font-semibold text-blue-700 dark:text-white hover:underline mb-2 flex justify-between items-center">
                        <?= htmlspecialchars($seg['titulo']) ?>
                        <svg class="flecha ml-2 w-4 h-4 transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                      </summary>
                      <div class="contenido-seguimiento mt-2 space-y-2 opacity-0 max-h-0 transition-all duration-500 ease-in-out">
                        <p class="text-sm text-gray-500 dark:text-gray-300"><strong>Fecha:</strong> <?= date('d-m-Y H:i', strtotime($seg['fecha'])) ?></p>
                        <p class="text-gray-700 dark:text-white"><?= nl2br(htmlspecialchars($seg['descripcion'])) ?></p>
                        <?php if (!empty($seg['foto']) && file_exists($seg['foto'])): ?>
                          <img src="<?= htmlspecialchars($seg['foto']) ?>" alt="Foto de seguimiento" class="max-w-xs mt-2 rounded shadow">
                        <?php endif; ?>
                      </div>
                    </details>
                  <?php endwhile; ?>
                </div>
              <?php else: ?>
                <p class="text-gray-600">No hay registros de seguimiento para esta mascota.</p>
              <?php endif; ?>
          <?php if ($_SESSION['usuario_tipo'] === 'Adoptante' && $_SESSION['usuario_id'] == $mascota['adoptante_id']): ?>
            <!-- Botón para abrir modal -->
            <div class="mt-10 border-t pt-6">
              <button onclick="abrirModal()" type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded mb-4">
                Agregar Seguimiento
              </button>
            </div>

            <!-- Modal -->
            <div id="modalSeguimiento" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
              <div id="modalContenido" class="bg-white rounded-lg shadow-lg max-w-xl w-full p-6 relative transform scale-95 opacity-0 transition-all duration-200">
                <!-- Botón cerrar -->
                <button onclick="cerrarModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800 text-xl">&times;</button>

                <h2 class="text-xl font-semibold mb-4">Agregar Seguimiento</h2>
                <form action="seguimiento.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                  <input type="hidden" name="mascota_id" value="<?= $mascota_id ?>">

                  <div>
                    <label for="titulo" class="block text-sm font-medium text-gray-700">Título del Seguimiento</label>
                    <input type="text" name="titulo" id="titulo" required
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                  </div>

                  <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea name="descripcion" id="descripcion" rows="4" required
                      class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"></textarea>
                  </div>

                  <div>
                    <div class="flex items-center space-x-4">
                      <label for="foto" class="cursor-pointer inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">
                        Seleccionar Archivo
                      </label>
                      <span id="nombre-archivo" class="text-gray-600 text-sm">Ningún archivo seleccionado</span>
                    </div>
                    <input type="file" name="foto" id="foto" accept="image/*" class="hidden">
                  </div>

                  <div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                      Guardar Seguimiento
                    </button>
                  </div>
                </form>
              </div>
            </div>
          <?php endif; ?>
          </div>
      </div>
    <script>
      document.getElementById('foto').addEventListener('change', function () {
        const nombre = this.files.length > 0 ? this.files[0].name : "Ningún archivo seleccionado";
        document.getElementById('nombre-archivo').textContent = nombre;
      });
    </script>
    <script>
      function abrirModal() {
        const modal = document.getElementById("modalSeguimiento");
        const contenido = document.getElementById("modalContenido");

        modal.classList.remove("hidden");

        // Agregar transición después de un tick
        setTimeout(() => {
          contenido.classList.remove("scale-95", "opacity-0");
          contenido.classList.add("scale-100", "opacity-100");
        }, 10);
      }

      function cerrarModal() {
        const modal = document.getElementById("modalSeguimiento");
        const contenido = document.getElementById("modalContenido");

        contenido.classList.remove("scale-100", "opacity-100");
        contenido.classList.add("scale-95", "opacity-0");

        // Esperar la transición antes de ocultar
        setTimeout(() => {
          modal.classList.add("hidden");
        }, 200);
      }

      document.getElementById('foto').addEventListener('change', function () {
        const nombre = this.files.length > 0 ? this.files[0].name : "Ningún archivo seleccionado";
        document.getElementById('nombre-archivo').textContent = nombre;
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
document.querySelectorAll("details").forEach((detalle) => {
  const contenido = detalle.querySelector(".contenido-seguimiento");
  const flecha = detalle.querySelector(".flecha");

  detalle.addEventListener("toggle", () => {
    if (detalle.open) {
      contenido.classList.remove("opacity-0", "max-h-0");
      contenido.classList.add("opacity-100", "max-h-[500px]");
      if (flecha) flecha.classList.add("rotate-180");
    } else {
      contenido.classList.remove("opacity-100", "max-h-[500px]");
      contenido.classList.add("opacity-0", "max-h-0");
      if (flecha) flecha.classList.remove("rotate-180");
    }
  });
});
</script>

<script>
  lucide.createIcons();
</script>
</body>
</html>
