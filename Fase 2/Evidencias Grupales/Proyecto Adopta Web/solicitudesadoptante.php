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
    SELECT s.*, m.nombre AS nombre_mascota, u.nombre AS nombre_refugio
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <script src="https://unpkg.com/lucide@latest">
  </script>
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
 <body class="bg-gray-50 min-h-screen">
  <!-- Botón menú -->
  <input id="btn-menu" type="checkbox"/>
  <div class="menu-toggle">
   <label class="cursor-pointer" for="btn-menu">
    <i class="w-6 h-6 text-blue-700" data-lucide="menu">
    </i>
   </label>
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
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mascota</th>
                            <th>Refugio</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($solicitud = $result_solicitudes->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($solicitud['nombre_mascota']) ?></td>
                                <td><?= htmlspecialchars($solicitud['nombre_refugio']) ?></td>
                                <td><?= htmlspecialchars($solicitud['estado']) ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($solicitud['fecha_solicitud'])) ?></td>
                                <td class="text-center">
                                    <a href="perfilmascota.php?id=<?= $solicitud['mascota_id'] ?>" class="btn btn-sm btn-outline-primary">Ver Mascota</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
    lucide.createIcons();
   </script>
   <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
 </body>
</html>
