<?php
session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION["usuario_tipo"]) || $_SESSION["usuario_tipo"] !== "Administrador") {
    echo "<p style='color: red; text-align: center; margin-top: 50px;'>Acceso denegado. Esta página solo pueden utilizarla los Administradores.</p>";
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
$condiciones[] = "u.tipo IN ('Adoptante', 'Refugio')";

if (!empty($_GET['tipo'])) {
    $tipo = $conexion->real_escape_string($_GET['tipo']);
    $condiciones[] = "u.tipo = '$tipo'";
}
if (!empty($_GET['nombre'])) {
    $nombre = $conexion->real_escape_string($_GET['nombre']);
    $condiciones[] = "u.nombre LIKE '%$nombre%'";
}
if (!empty($_GET['rut'])) {
    $rut = $conexion->real_escape_string($_GET['rut']);
    $condiciones[] = "u.rut LIKE '%$rut%'";
}
if (!empty($_GET['region_id'])) {
    $region_id = $conexion->real_escape_string($_GET['region_id']);
    $condiciones[] = "u.region_id = '$region_id'";
}
if (!empty($_GET['comuna_id'])) {
    $comuna_id = $conexion->real_escape_string($_GET['comuna_id']);
    $condiciones[] = "u.comuna_id = '$comuna_id'";
}

$where = count($condiciones) > 0 ? "WHERE " . implode(" AND ", $condiciones) : "";

$query = "SELECT u.*, r.nombre AS region_nombre, c.nombre AS comuna_nombre
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
  <title>Listado de Mascotas</title>
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

  <header class="text-center mt-6">
    <h1 class="text-2xl text-blue-700">Listado de Mascotas</h1>
  </header>

<div class="flex justify-center mt-6">
  <button id="toggle-filtros" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">
    Mostrar filtros ▼
  </button>
</div>
<div id="filtros-container" class="hidden max-w-5xl mx-auto mt-4 bg-white border border-gray-300 shadow-md rounded-lg p-6" style="display: none;">
<form method="get" class="max-w-6xl mx-auto mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 px-4">
  
    <!-- Filtro por Tipo de Usuario -->
    <select name="tipo" class="p-2 border rounded">
      <option value="">-- Tipo de Usuario --</option>
      <option value="Adoptante" <?= @$_GET['tipo'] == 'Adoptante' ? 'selected' : '' ?>>Adoptante</option>
      <option value="Refugio" <?= @$_GET['tipo'] == 'Refugio' ? 'selected' : '' ?>>Refugio</option>
    </select>

    <!-- Filtro por Nombre -->
    <input type="text" name="nombre" placeholder="Nombre del Usuario" value="<?= $_GET['nombre'] ?? '' ?>" class="p-2 border rounded">

    <!-- Filtro por RUT -->
    <input type="text" name="rut" placeholder="RUT del Usuario" value="<?= $_GET['rut'] ?? '' ?>" class="p-2 border rounded">

    
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

<section class="usuario-lista max-w-3xl mx-auto mt-8 px-4">
  <?php if ($resultado && $resultado->num_rows > 0): ?>
    <?php while ($usuario = $resultado->fetch_assoc()): ?>
      <div class="card max-w-4xl bg-white border border-gray-300 shadow-md rounded-lg p-4 pr-6 flex justify-between mb-4 mx-auto">
        <div class="flex items-center">
          <div class="mr-6 pl-4 flex flex-col items-center">
            <?php if (!empty($usuario['foto']) && file_exists($usuario['foto'])): ?>
              <img src="<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto del usuario" class="w-32 h-32 object-cover rounded-xl shadow">
            <?php else: ?>
              <img src="img/mascota_default.png" alt="" class="w-32 h-32 object-cover rounded-xl shadow opacity-50">
            <?php endif; ?>
          </div>

          <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
            <div>
              <p class="text-gray-500 text-sm">
                <?= $usuario['tipo'] === 'Adoptante' ? 'Nombre del adoptante' : 'Nombre del refugio' ?>
              </p>
              <p class="text-gray-800 font-semibold"><?= htmlspecialchars($usuario['nombre']) ?></p>
            </div>
            <div>
              <p class="text-gray-500 text-sm">RUT</p>
              <p class="text-gray-800 font-semibold"><?= htmlspecialchars($usuario['rut']) ?></p>
            </div>
            <div>
              <p class="text-gray-500 text-sm">Tipo de Usuario</p>
              <p class="text-gray-800 font-semibold"><?= htmlspecialchars($usuario['tipo']) ?></p>
            </div>
            <div class="break-all max-w-xs">
              <p class="text-gray-500 text-sm">Correo</p>
              <p class="text-gray-800 font-semibold break-words whitespace-normal"><?= htmlspecialchars($usuario['email']) ?></p>
            </div>
            <div class="col-span-2 mt-2 flex flex-wrap gap-2">
              <span class="bg-gray-200 px-2 py-1 rounded text-sm"><?= htmlspecialchars($usuario['region_nombre'] ?? '') ?></span>
              <span class="bg-gray-200 px-2 py-1 rounded text-sm"><?= htmlspecialchars($usuario['comuna_nombre'] ?? '') ?></span>
            </div>
          </div>
        </div>

        <div class="ml-2 self-center flex flex-col gap-2">
          <?php if ($usuario['tipo'] === 'Adoptante'): ?>
            <a href="perfiladoptante.php?id=<?= $usuario['id'] ?>">
              <button class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition w-36">Ver Perfil</button>
            </a>
          <?php elseif ($usuario['tipo'] === 'Refugio'): ?>
            <a href="perfilrefugio.php?id=<?= $usuario['id'] ?>">
              <button class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition w-36">Ver Perfil</button>
            </a>
          <?php endif; ?>
          <form method="post" action="eliminar_usuario.php" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta cuenta? Esta acción no se puede deshacer.');">
            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded shadow hover:bg-red-700 transition w-36">Eliminar Cuenta</button>
          </form>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="text-center text-gray-500">No hay usuarios registrados.</p>
  <?php endif; ?>
</section>


<script>
  lucide.createIcons();

  const toggleFiltros = document.getElementById('toggle-filtros');
  const filtrosContainer = document.getElementById('filtros-container');
  if (toggleFiltros && filtrosContainer) {
    toggleFiltros.addEventListener('click', () => {
      filtrosContainer.classList.toggle('hidden');
      toggleFiltros.textContent = filtrosContainer.classList.contains('hidden') ? 'Mostrar filtros ▼' : 'Ocultar filtros ▲';
    });

    // Mostrar filtros automáticamente si hay algún campo con valor
    const hasActiveFilters = filtrosContainer.querySelectorAll('input:not([type="hidden"]):not([type="submit"]), select')
      .length > 0 && Array.from(filtrosContainer.querySelectorAll('input, select')).some(el => el.value && el.value !== '');
    if (hasActiveFilters) {
      filtrosContainer.classList.remove('hidden');
      toggleFiltros.textContent = 'Ocultar filtros ▲';
    }
  }
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
  document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggle-filtros");
    const filtrosContainer = document.getElementById("filtros-container");

    if (toggleBtn && filtrosContainer) {
      // Sincronizar texto inicial del botón
      toggleBtn.textContent = filtrosContainer.style.display === "none"
        ? "Mostrar filtros ▼"
        : "Ocultar filtros ▲";

      // Alternar visibilidad al hacer clic
      toggleBtn.addEventListener("click", () => {
        const isHidden = filtrosContainer.style.display === "none";
        filtrosContainer.style.display = isHidden ? "block" : "none";
        toggleBtn.textContent = isHidden
          ? "Ocultar filtros ▲"
          : "Mostrar filtros ▼";
      });
    }
  });
</script>

</body>
</html>