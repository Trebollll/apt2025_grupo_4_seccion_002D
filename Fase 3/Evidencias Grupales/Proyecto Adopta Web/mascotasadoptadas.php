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
    echo "<p style='color:red;text-align:center;'>No tienes permiso para ver las mascotas adoptadas de este perfil.</p>";
    exit();
}

if ($tipo_sesion === "Refugio") {
    echo "<p style='color:red;text-align:center;'>Solo los Adoptantes y Administradores pueden acceder a esta página.</p>";
    exit();
}

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

if ($usuarios['tipo'] !== 'Adoptante') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <title>Perfil no disponible</title>
      <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center h-screen">
      <div class="bg-white shadow-lg rounded-lg p-8 max-w-lg text-center border border-red-300">
        <div class="text-red-600 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414 1.414a9 9 0 11-1.414-1.414L18.364 5.636z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01" />
          </svg>
        </div>
        <h2 class="text-xl font-semibold text-gray-800">Este usuario no es un adoptante</h2>
        <p class="text-gray-600 mt-2">Solo se permite visualizar perfiles de tipo "Adoptante".</p>
      </div>
    </body>
    </html>
    <?php
    exit();
}

$adoptante_id = $usuarios['id'];
$sql_mascotas = "
    SELECT m.*, s.fecha_solicitud, r.nombre AS nombre_refugio
    FROM mascotas m
    INNER JOIN solicitudes s ON m.id = s.mascota_id
    INNER JOIN usuarios r ON s.refugio_id = r.id
    WHERE s.adoptante_id = $adoptante_id
      AND s.estado = 'Aceptado'
      AND m.estado = 'Adoptado'
";
$resultado_mascotas = $conexion->query($sql_mascotas);


?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mascotas Adoptadas</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
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
  <div class="max-w-3xl mx-auto bg-white border border-gray-300 shadow-[0_4px_10px_rgba(0,0,0,0.1)] rounded-2xl p-8 transition-all duration-300">
      <h2 class="text-2xl font-bold text-gray-800 mb-4">Mascotas Adoptadas</h2>

      <?php if ($resultado_mascotas && $resultado_mascotas->num_rows > 0): ?>
        <div class="space-y-6">
          <?php while ($mascotas = $resultado_mascotas->fetch_assoc()): ?>
            <div class="mascota-card bg-white border border-gray-300 shadow-[0_4px_10px_rgba(0,0,0,0.1)] rounded-2xl p-5 flex flex-col md:flex-row justify-between transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
              <!-- Imagen de la mascota -->
              <div class="w-32 h-32 flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                <?php if (!empty($mascotas['foto']) && file_exists($mascotas['foto'])): ?>
                  <img src="<?= htmlspecialchars($mascotas['foto']) ?>" alt="Foto de <?= htmlspecialchars($mascotas['nombre']) ?>" class="w-full h-full object-cover rounded-full border-2 border-blue-200 shadow">
                <?php else: ?>
                  <img src="https://ui-avatars.com/api/?name=<?= urlencode($mascotas['nombre']) ?>" alt="Foto de <?= htmlspecialchars($mascotas['nombre']) ?>" class="w-full h-full object-cover rounded-full border-2 border-blue-200 shadow">
                <?php endif; ?>
              </div>

              <!-- Información de la mascota -->
              <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
              <div>
                  <p class="text-gray-500 text-sm">Nombre</p>
                  <p class="text-gray-800 font-semibold"><?= htmlspecialchars($mascotas['nombre']) ?></p>
              </div>
              <div>
                  <p class="text-gray-500 text-sm">Especie</p>
                  <p class="text-gray-800 font-semibold"><?= htmlspecialchars($mascotas['especie']) ?></p>
              </div>
              <div>
                  <p class="text-gray-500 text-sm">Raza</p>
                  <p class="text-gray-800 font-semibold"><?= htmlspecialchars($mascotas['raza']) ?></p>
              </div>
              <div>
                  <p class="text-gray-500 text-sm">Sexo</p>
                  <p class="text-gray-800 font-semibold"><?= htmlspecialchars($mascotas['sexo']) ?></p>
              </div>
              <div>
                  <p class="text-gray-500 text-sm">Fecha de Adopción</p>
                  <p class="text-gray-800 font-semibold"><?= date('d-m-Y H:i', strtotime($mascotas['fecha_solicitud'])) ?></p>
              </div>
              <div>
                  <p class="text-gray-500 text-sm">Refugio de Origen</p>
                  <p class="text-gray-800 font-semibold"><?= htmlspecialchars($mascotas['nombre_refugio']) ?></p>
              </div>
            </div>

              <!-- Botones -->
              <div class="flex flex-col md:flex-col gap-2 mt-4 md:mt-0 md:ml-6 text-center">
                <a href="perfilmascota.php?id=<?= $mascotas['id'] ?>" class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition-all duration-300 w-36">Ver Perfil</a>
                <a href="seguimiento.php?id=<?= $mascotas['id'] ?>" class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition-all duration-300 w-36">Seguimiento</a>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <p class="text-gray-600">Este adoptante aún no ha adoptado ninguna mascota.</p>
      <?php endif; ?>
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

</body>
</html>
