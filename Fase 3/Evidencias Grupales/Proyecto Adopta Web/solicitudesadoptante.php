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
    echo "<p style='color:red;text-align:center;'>No tienes permiso para ver las solicitudes de adopción de este perfil.</p>";
    exit();
}

if ($tipo_sesion === "Refugio") {
    echo "<p style='color:red;text-align:center;'>Solo los Adoptantes y Administradores pueden acceder a esta página.</p>";
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Usuario no encontrado.");
}

$id = intval($_GET['id']);
$resultado = $conexion->query("SELECT * FROM usuarios WHERE id = $id");

if ($resultado->num_rows === 0) {
    echo "<p class='text-center text-red-600 mt-10'>Usuario no encontrado.</p>";
    exit();
}

$usuarios = $resultado->fetch_assoc();

// Consultar las solicitudes del adoptante actual
$stmtSolicitudes = $conexion->prepare("
    SELECT s.*, m.nombre AS nombre_mascota, u.nombre AS nombre_refugio, m.foto AS foto_mascota
    FROM solicitudes s
    JOIN mascotas m ON s.mascota_id = m.id
    JOIN usuarios u ON s.refugio_id = u.id
    WHERE s.adoptante_id = ?
    ORDER BY s.fecha_solicitud DESC
");
$stmtSolicitudes->bind_param("i", $id);
$stmtSolicitudes->execute();
$result_solicitudes = $stmtSolicitudes->get_result();

?>

<html lang="es">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>
   Estado de Solicitudes
  </title>
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

    .dark-mode .card {
        background-color: #2c2c2c !important;
        border-color: #555 !important;
    }

    .dark-mode .mascota-card {
        background-color: #1f1f1f !important;
        border-color: #444 !important;
    }

    .dark-mode .mascota-card a.bg-gray-600:hover {
        background-color: #666 !important;
        transition: background-color 0.3s ease;
    }

    .dark-mode p.text-gray-600 {
        color: #ffffff !important;
    }
    </style>
 </head>
 <body class="bg-gray-50 min-h-screen">
  <!-- Botón menú -->
  <input id="btn-menu" type="checkbox"/>
  <div class="menu-toggle">
   <label class="cursor-pointer" for="btn-menu">
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
    <label class="close-menu" for="btn-menu">✕</label>
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
     <div class="card-body">
      <div class="mb-6 text-center">
       <h1 class="text-3xl font-bold mb-2">Adopta Web - <?= htmlspecialchars($usuarios['nombre']) ?></h1>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-6 border-b pb-2">Mis Solicitudes de Adopción</h2>
        <?php if ($result_solicitudes->num_rows > 0): ?>
            <div class="space-y-6">
                <?php while ($solicitud = $result_solicitudes->fetch_assoc()): ?>
                    <div class="mascota-card bg-gray-50 dark:bg-[#1f1f1f] border border-gray-200 dark:border-[#444] rounded-xl shadow transition duration-300 flex flex-col md:flex-row items-center p-4">
                    
                    <!-- Imagen -->
                    <div class="w-28 h-28 mb-4 md:mb-0 md:mr-6 flex-shrink-0">
                        <?php
                        $id_mascota = $solicitud['mascota_id'];
                        $foto_resultado = $conexion->query("SELECT foto, nombre FROM mascotas WHERE id = $id_mascota LIMIT 1");
                        $foto_data = $foto_resultado->fetch_assoc();
                        $foto = $foto_data && !empty($foto_data['foto']) && file_exists($foto_data['foto']) ? $foto_data['foto'] : "https://ui-avatars.com/api/?name=" . urlencode($foto_data['nombre'] ?? 'Mascota');
                        ?>
                        <img src="<?= htmlspecialchars($foto) ?>" alt="Foto de la mascota" class="w-full h-full object-cover rounded-full border-2 border-blue-200 shadow">
                    </div>

                    <!-- Info -->
                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-center sm:text-left">
                        <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Mascota</p>
                        <p class="text-gray-800 dark:text-white font-semibold"><?= htmlspecialchars($solicitud['nombre_mascota']) ?></p>
                        </div>
                        <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Refugio</p>
                        <p class="text-gray-800 dark:text-white font-semibold"><?= htmlspecialchars($solicitud['nombre_refugio']) ?></p>
                        </div>
                        <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Estado</p>
                        <p class="text-gray-800 dark:text-white font-semibold"><?= htmlspecialchars($solicitud['estado']) ?></p>
                        </div>
                        <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Fecha</p>
                        <p class="text-gray-800 dark:text-white font-semibold"><?= date('d-m-Y H:i', strtotime($solicitud['fecha_solicitud'])) ?></p>
                        </div>
                    </div>

                    <!-- Botón -->
                    <div class="mt-4 md:mt-0 md:ml-6">
                        <a href="perfilmascota.php?id=<?= $solicitud['mascota_id'] ?>" class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">Ver Mascota</a>
                    </div>
                    </div>
                <?php endwhile; ?>
                </div>
        <?php else: ?>
            <p class="text-center text-gray-600">No has realizado solicitudes de adopción aún.</p>
        <?php endif; ?>
    </div>
    </div>
    </div>
    </div>
    </div>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const btnToggle = document.getElementById("modoToggle");
  const html = document.documentElement;

  function actualizarIconoModo() {
    const modoActual = html.classList.contains("dark-mode");
    const nuevoIcono = modoActual ? "sun" : "moon";
    btnToggle.innerHTML = `<i id="modoIcono" data-lucide="${nuevoIcono}" class="w-5 h-5"></i>`;
    lucide.createIcons();
  }

  // Activar modo oscuro si estaba guardado
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

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

 </body>
</html>
