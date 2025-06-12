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
                    <details class="bg-gray-50 rounded-lg shadow p-4">
                      <summary class="cursor-pointer text-lg font-semibold text-blue-700 hover:underline mb-2">
                        <?= htmlspecialchars($seg['titulo']) ?>
                      </summary>
                      <div class="mt-2 space-y-2">
                        <p class="text-sm text-gray-500"><strong>Fecha:</strong> <?= date('d-m-Y H:i', strtotime($seg['fecha'])) ?></p>
                        <p class="text-gray-700"><?= nl2br(htmlspecialchars($seg['descripcion'])) ?></p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="foto">Foto (opcional)</label>
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
      lucide.createIcons();
    </script>
</body>
</html>
