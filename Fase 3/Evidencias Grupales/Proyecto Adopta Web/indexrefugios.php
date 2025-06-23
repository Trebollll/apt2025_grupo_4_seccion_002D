<?php
session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION["usuario_tipo"]) || $_SESSION["usuario_tipo"] !== "Adoptante") {
    echo "<p style='color: red; text-align: center; margin-top: 50px;'>Acceso denegado. Esta página es solo para Adoptantes.</p>";
    exit();
}

include("conexion.php");
$conexion->set_charset("utf8mb4");

$regiones = [];
$sql_region = "SELECT id, nombre FROM region";
$resultado_region = $conexion->query($sql_region);
if ($resultado_region && $resultado_region->num_rows > 0) {
    while ($fila = $resultado_region->fetch_assoc()) {
        $regiones[] = $fila;
    }
}

// Capturar filtros desde GET
$filtros = [];
$condiciones = [];

if (!empty($_GET['nombre'])) {
    $nombre = $conexion->real_escape_string($_GET['nombre']);
    $condiciones[] = "u.nombre LIKE '%$nombre%'";
}
if (!empty($_GET['region_id'])) {
    $region_id = $conexion->real_escape_string($_GET['region_id']);
    $condiciones[] = "u.region_id LIKE '%$region_id%'";
}
if (!empty($_GET['comuna_id'])) {
    $comuna_id = $conexion->real_escape_string($_GET['comuna_id']);
    $condiciones[] = "u.comuna_id LIKE '%$comuna_id%'";
}

$condiciones[] = "u.tipo = 'Refugio'";
$where = "WHERE " . implode(" AND ", $condiciones);

// Obtener usuarios filtrados
$query = "SELECT u.id, u.foto, u.nombre, r.nombre AS region_nombre, c.nombre AS comuna_nombre
          FROM usuarios u
          LEFT JOIN region r ON u.region_id = r.id
          LEFT JOIN comuna c ON u.comuna_id = c.id
          $where";
$resultado = $conexion->query($query);

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Listado de Refugios</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          maxHeight: {
            '[1000px]': '1000px'
          }
        }
      }
    }
  </script>
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

  .card h3 {
    color: #1f2937;
  }

  .dark-mode .card h3 {
    color: #ffffff;
  }

  .card button {
    transition: all 0.3s ease;
  }

  .card button:hover {
    transform: translateY(-1px);
  }

  .dark-mode .lista button {
  background-color: #444 !important;
  color: #ffffff !important;
  }

  .dark-mode .lista button:hover {
    background-color: #666 !important;
  }
</style>
</head>
<body class="bg-gray-100 text-gray-800">
    <!-- Botones superiores -->
    <div class="fixed top-4 right-4 z-50 flex gap-4">
      <button id="modoToggle" class="bg-gray-700 text-white px-3 py-2 rounded shadow flex items-center justify-center w-10 h-10">
        <i id="modoIcono" data-lucide="moon" class="w-5 h-5"></i>
      </button>
      <button id="toggle-filtros" class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition flex items-center gap-2">
        <span>Filtros</span>
        <svg id="filtro-flecha" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform duration-300 transform rotate-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </button>
    </div>
    <!-- Botón menú -->
    <input type="checkbox" id="btn-menu">
    <div class="menu-toggle">
      <label for="btn-menu" class="cursor-pointer">
        <i data-lucide="menu" class="menu-icono w-6 h-6"></i>
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

  <header class="header-elegante text-center mt-6">
    <h1><i data-lucide="paw-print" class="inline-block w-6 h-6 mr-2 align-middle"></i>Listado de Refugios</h1>
  </header>

<div id="filtros-container" class="transition-all duration-500 ease-in-out overflow-hidden max-h-0 opacity-0 hidden max-w-5xl mx-auto mt-4 bg-white border border-gray-300 shadow-md rounded-lg p-6">
<form method="get" class="max-w-6xl mx-auto mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 px-4">
  
    <input type="text" name="nombre" placeholder="Nombre del Refugio" value="<?= $_GET['nombre'] ?? '' ?>" class="p-2 border rounded">
    <select name="region_id" id="region" class="p-2 border rounded">
      <option value="">Seleccione una región</option>
      <?php foreach ($regiones as $r): ?>
        <option value="<?= $r['id'] ?>" <?= (isset($_GET['region_id']) && $_GET['region_id'] == $r['id']) ? 'selected' : '' ?>>
          <?= $r['nombre'] ?>
        </option>
      <?php endforeach; ?>
    </select>

    <select name="comuna_id" id="comuna" class="p-2 border rounded">
      <option value="">Seleccione una comuna</option>
    </select>

    <div class="col-span-3"></div>
    <div class="col-span-3 flex justify-end">
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded mt-4">Buscar</button>
    </div>
  </form>
</div>

<section class="lista max-w-6xl mx-auto mt-8 px-4 grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php if ($resultado && $resultado->num_rows > 0): ?>
      <?php while ($usuarios = $resultado->fetch_assoc()): ?>
        <div class="card bg-white border border-gray-300 shadow-[0_4px_10px_rgba(0,0,0,0.1)] rounded-2xl p-5 flex justify-between transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
          <div class="mr-6 pl-4 flex flex-col items-center">
              <?php if (!empty($usuarios['foto']) && file_exists($usuarios['foto'])): ?>
                  <img src="<?= htmlspecialchars($usuarios['foto']) ?>" alt="Foto del refugio" class="w-32 h-32 object-cover rounded-xl shadow">
              <?php else: ?>
                  <img src="img/refugio_default.png" alt="" class="w-32 h-32 object-cover rounded-xl shadow opacity-50">
              <?php endif; ?>
            </div>  

          <div class="flex-1 flex flex-col justify-center">
            <h3 class="text-gray-800 dark:text-white font-bold text-lg mb-2"><?= htmlspecialchars($usuarios['nombre']) ?></h3>
            <div class="etiquetas flex flex-wrap gap-2">
              <span class="bg-gray-200 px-2 py-1 rounded text-sm"><?= htmlspecialchars($usuarios['region_nombre'] ?? '') ?></span>
              <span class="bg-gray-200 px-2 py-1 rounded text-sm"><?= htmlspecialchars($usuarios['comuna_nombre'] ?? '') ?></span>
            </div>
          </div>
          <div class="ml-2 self-center flex flex-col gap-2">
              <a href="perfilrefugio.php?id=<?= $usuarios['id'] ?>">
                <button class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition w-36">
                  Ver Perfil
                </button>
              </a>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-center text-gray-500">No hay refugios registrados.</p>
    <?php endif; ?>
  </section>

<script>
  const toggleFiltros = document.getElementById('toggle-filtros');
  const filtrosContainer = document.getElementById('filtros-container');
  const flecha = document.getElementById('filtro-flecha');

  let filtrosAbiertos = false;

  toggleFiltros.addEventListener('click', () => {
    filtrosAbiertos = !filtrosAbiertos;

    if (filtrosAbiertos) {
      // Mostrar antes de animar
      filtrosContainer.classList.remove('hidden');
      
      // Forzar reflow para que la transición funcione
      void filtrosContainer.offsetWidth;

      filtrosContainer.classList.remove('max-h-0', 'opacity-0', 'hidden');
      void filtrosContainer.offsetWidth;
      filtrosContainer.classList.add('max-h-screen', 'opacity-100');
      flecha.classList.add('rotate-180');
    } else {
      filtrosContainer.classList.remove('max-h-screen', 'opacity-100');
      filtrosContainer.classList.add('max-h-0', 'opacity-0');
      flecha.classList.remove('rotate-180');

      setTimeout(() => {
        filtrosContainer.classList.add('hidden');
      }, 500); // tiempo igual a duration-500
    }
  });

  // Si hay filtros activos desde el GET, desplegarlos automáticamente
  document.addEventListener("DOMContentLoaded", () => {
    const inputs = filtrosContainer.querySelectorAll('input, select');
    const tieneValores = Array.from(inputs).some(el => el.value && el.value !== '');

    if (tieneValores) {
      filtrosAbiertos = true;
      filtrosContainer.classList.remove('max-h-0', 'opacity-0');
      filtrosContainer.classList.add('max-h-[1000px]', 'opacity-100');
      flecha.classList.add('rotate-180');
    }
  });

  document.addEventListener("DOMContentLoaded", () => {
  lucide.createIcons(); // esto activa el ícono
});
</script>

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
            comunaSelect.innerHTML = '<option value="">Error al cargar</option>';
            console.error('Error al cargar comunas:', error);
        });
});

// Cargar comuna seleccionada si existe en GET
document.addEventListener("DOMContentLoaded", () => {
    const regionInput = document.getElementById("region");
    const comunaInput = document.getElementById("comuna");
    const comunaSeleccionada = "<?= $_GET['comuna_id'] ?? '' ?>";

    if (regionInput.value && comunaSeleccionada) {
        fetch('obtener_comunas.php?region_id=' + regionInput.value)
            .then(res => res.json())
            .then(data => {
                comunaInput.innerHTML = '<option value="">Seleccione una comuna</option>';
                data.forEach(c => {
                    const selected = c.id == comunaSeleccionada ? 'selected' : '';
                    comunaInput.innerHTML += `<option value="${c.id}" ${selected}>${c.nombre}</option>`;
                });
            });
    }
});
</script>

<script>
  const btnToggle = document.getElementById("modoToggle");
  const html = document.documentElement;

  // Función para actualizar el ícono
  function actualizarIconoModo() {
    const modoActual = html.classList.contains("dark-mode");
    const nuevoIcono = modoActual ? "sun" : "moon";

    // Reemplazar el contenido del botón con un nuevo ícono
    btnToggle.innerHTML = `<i id="modoIcono" data-lucide="${nuevoIcono}" class="w-5 h-5"></i>`;
    lucide.createIcons();
  }

  // Al cargar la página
  if (localStorage.getItem("modo") === "oscuro") {
    html.classList.add("dark-mode");
  }
  actualizarIconoModo(); // dibujar ícono correcto al inicio

  // Al hacer clic en el botón
  btnToggle.addEventListener("click", () => {
    html.classList.toggle("dark-mode");
    const modoActivo = html.classList.contains("dark-mode");
    localStorage.setItem("modo", modoActivo ? "oscuro" : "claro");
    actualizarIconoModo();
  });
</script>

</body>
</html>