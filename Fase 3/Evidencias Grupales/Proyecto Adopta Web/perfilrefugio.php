<?php
session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

include("conexion.php");
$conexion->set_charset("utf8mb4");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Refugio no encontrado.");
}

$id = intval($_GET['id']);
$resultado = $conexion->query("SELECT * FROM usuarios WHERE id = $id");

if ($resultado->num_rows === 0) {
    echo "<p class='text-center text-red-600 mt-10'>Refugio no encontrado.</p>";
    exit();
}

$usuarios = $resultado->fetch_assoc();

$usuario_tipo = $_SESSION["usuario_tipo"];
$usuario_id_logueado = intval($_SESSION["usuario_id"]);
$perfil_id = intval($usuarios["id"]);

if (
    $usuario_tipo === "Refugio" &&
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
            <h2 class="text-xl font-semibold text-gray-800">Solo puedes ver tu propio perfil de Refugio.</h2>
        </div>
    </body>
    </html>
    <?php
    exit();
}


// Obtener documentos del refugio
$documentos = [];
$consulta_docs = $conexion->prepare("SELECT id, ruta, fecha_subida FROM documentos WHERE usuario_id = ?");
$consulta_docs->bind_param("i", $id);
$consulta_docs->execute();
$resultado_docs = $consulta_docs->get_result();

while ($doc = $resultado_docs->fetch_assoc()) {
    $documentos[] = $doc;
}


// Validar que sea del tipo Refugio
if ($usuarios['tipo'] !== 'Refugio') {
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
            <h2 class="text-xl font-semibold text-gray-800">Este perfil no es de un refugio.</h2>
        </div>
    </body>
    </html>
    <?php
    exit();
}


$id = intval($_GET['id']);
$stmt = $conexion->prepare("
    SELECT usuarios.*, region.nombre AS nombre_region, comuna.nombre AS nombre_comuna
    FROM usuarios
    LEFT JOIN region ON usuarios.region_id = region.id
    LEFT JOIN comuna ON usuarios.comuna_id = comuna.id
    WHERE usuarios.id = ? AND usuarios.tipo = 'Refugio'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "<p class='text-center text-red-600'>Refugio no encontrado.</p>";
    exit();
}

$usuarios = $resultado->fetch_assoc();

// Obtener mascotas asociadas al refugio
$stmtMascotas = $conexion->prepare("SELECT * FROM mascotas WHERE refugio_id = ? AND estado = 'No Adoptado'");
$stmtMascotas->bind_param("i", $id);
$stmtMascotas->execute();
$result_mascotas = $stmtMascotas->get_result();

// Consultar solicitudes de adopción recibidas por las mascotas del refugio
$stmtSolicitudes = $conexion->prepare("
    SELECT s.*, m.nombre AS nombre_mascota, u.nombre AS nombre_adoptante
    FROM solicitudes s
    JOIN mascotas m ON s.mascota_id = m.id
    JOIN usuarios u ON s.adoptante_id = u.id
    WHERE s.refugio_id = ? AND s.estado = 'En espera'
    ORDER BY s.fecha_solicitud DESC
");
$stmtSolicitudes->bind_param("i", $id);
$stmtSolicitudes->execute();
$result_solicitudes = $stmtSolicitudes->get_result();

// Consultar mascotas adoptadas
$stmtAdoptadas = $conexion->prepare("
    SELECT m.id AS mascota_id, m.nombre AS nombre_mascota, u.id AS adoptante_id, u.nombre AS nombre_adoptante
    FROM solicitudes s
    JOIN mascotas m ON s.mascota_id = m.id
    JOIN usuarios u ON s.adoptante_id = u.id
    WHERE s.refugio_id = ? AND s.estado = 'Aceptado'
    ORDER BY s.fecha_solicitud DESC
");
$stmtAdoptadas->bind_param("i", $id);
$stmtAdoptadas->execute();
$result_adoptadas = $stmtAdoptadas->get_result();

?>

<html lang="es">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>
   Perfil de Refugio
  </title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
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

    .dark-mode a.bg-blue-600,
    .dark-mode a.bg-gray-600 {
        background-color: #444 !important;
        color: #fff !important;
        border-color: #555 !important;
        transition: background-color 0.3s ease;
    }

    .dark-mode a.bg-blue-600:hover,
    .dark-mode a.bg-gray-600:hover {
        background-color: #555 !important;
    }

    .dark-mode table.table {
        background-color: #2a2a2a !important; 
        color: #ffffff !important;
    }

    .dark-mode table.table th,
    .dark-mode table.table td {
        background-color: #2a2a2a !important;
        color: #ffffff !important;
        border-color: #555 !important;
    }

    .dark-mode table.table thead {
        background-color: #3a3a3a !important;
        color: #ffffff !important;
    }

    .dark-mode .table-hover tbody tr:hover {
        background-color: #3a3a3a !important;
    }

    .table-elegante {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        border-collapse: collapse;
        margin-top: 1rem;
        width: 100%;
    }

    /* Encabezado */
    .table-elegante thead {
        background: linear-gradient(to right, #4b6cb7, #182848); /* azul degradado */
        color: white;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* Celdas */
    .table-elegante th,
    .table-elegante td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border: 1px solid #e0e0e0;
    }

    .table-elegante tbody tr:hover {
        background-color: rgba(75, 108, 183, 0.1);
    }

    .dark-mode .table-elegante {
        background-color: #000 !important;
        color: #fff !important;
    }

    .dark-mode .table-elegante thead {
        background: #111111 !important;
        color: #fff !important;
    }

    .dark-mode .table-elegante th,
    .dark-mode .table-elegante td {
        border: 1px solid #444 !important;
    }

    .dark-mode .table-elegante tbody tr:hover {
        background-color: #1a1a1a !important;
    }

    .dark-mode .nav-tabs .nav-link {
        background-color: #2c2c2c !important;
        color: #ffffff !important;
        border-color: #444 #444 #2c2c2c !important;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .dark-mode .nav-tabs .nav-link.active {
        background-color: #1f1f1f !important;
        color: #ffffff !important;
        border-color: #666 #666 #1f1f1f !important;
    }

    .dark-mode .nav-tabs .nav-link:hover {
        background-color: #3a3a3a !important;
    }

    .ver-perfil-btn {
        background-color: #4b5563;
        color: #ffffff;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    .ver-perfil-btn:hover {
        background-color: #374151;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    }

    .dark-mode .ver-perfil-btn {
        background-color: #444 !important;
        color: #ffffff !important;
    }

    .dark-mode .ver-perfil-btn:hover {
        background-color: #555 !important;
    }

    .btn-eliminar {
        background-color: #dc2626;
        color: #ffffff;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        border: none;
    }

    .btn-eliminar:hover {
        background-color: #b91c1c;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    }

    .dark-mode .btn-eliminar {
        background-color: #dc2626 !important;
        color: #ffffff !important;
    }

    .dark-mode .btn-eliminar:hover {
        background-color: #b91c1c !important;
    }

    .documento-item {
        background-color: #f0f0f0;
    }

    .dark-mode .documento-item {
        background-color: #2a2a2a !important;
    }

    .dark-mode .documento-item p,
    .dark-mode .documento-item span {
        color: #ffffff !important;
    }

    .sub-card {
        border: 1px solid #e0e0e0;
        background-color: #f9f9f9;
    }

    .dark-mode .sub-card {
        background-color: #1f1f1f !important;
        border-color: #444 !important;
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
      <ul class="nav nav-tabs" id="profileTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#info" id="info-tab" role="tab">Información del Refugio</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#mascotas" id="workers-tab" role="tab">Mascotas</a>
        </li>

        <?php if ($usuario_tipo === 'Refugio'): ?>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#solicitudes" id="docs-tab" role="tab">Solicitudes de Adopción</a>
        </li>
        <?php endif; ?>

        <?php if ($usuario_tipo === 'Administrador' || $usuario_tipo === 'Refugio'): ?>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#adoptadas" role="tab">Mascotas Adoptadas</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#documentos" role="tab">Documentos</a>
        </li>
        <?php endif; ?>
    </ul>
      <div class="tab-content mt-3" id="profileTabContent">
       <div class="tab-pane fade show active" id="info" role="tabpanel">
         <div class="bg-white p-6 rounded-lg shadow sub-card">
            <h2 class="text-xl font-semibold mb-6 border-b pb-2">Información del Refugio</h2>

            <div class="flex flex-col md:flex-row gap-6">
                <!-- Imagen -->
                <div class="flex-shrink-0">
                    <?php if (!empty($usuarios['foto']) && file_exists($usuarios['foto'])): ?>
                        <img src="<?= htmlspecialchars($usuarios['foto']) ?>" alt="Foto del refugio" class="w-48 h-auto rounded-xl shadow">
                    <?php else: ?>
                        <img src="img/programa_default.png" alt="Sin imagen" class="w-48 h-auto rounded-xl shadow opacity-50">
                    <?php endif; ?>
                </div>

                <!-- Datos -->
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <div>
                        <p class="text-gray-500 text-sm">Nombre</p>
                        <p class="text-gray-800 font-semibold"><?= htmlspecialchars($usuarios['nombre']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">RUT</p>
                        <p class="text-gray-800 font-semibold"><?= htmlspecialchars($usuarios['rut']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Dirección</p>
                        <p class="text-gray-800 font-semibold"><?= htmlspecialchars($usuarios['direccion']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Correo</p>
                        <p class="text-gray-800 font-semibold"><?= htmlspecialchars($usuarios['email']) ?></p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-gray-500 text-sm">Número de Contacto</p>
                        <p class="text-gray-800 font-semibold"><?= nl2br(htmlspecialchars($usuarios['numero_contacto'])) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Representante</p>
                        <p class="text-gray-800 font-semibold"><?= htmlspecialchars($usuarios['representante']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Horario de Atención</p>
                        <p class="text-gray-800 font-semibold"><?= htmlspecialchars($usuarios['horario']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Especies</p>
                        <p class="text-gray-800 font-semibold"><?= htmlspecialchars($usuarios['especies']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Redes Sociales</p>
                        <p class="text-gray-800 font-semibold"><?= htmlspecialchars($usuarios['redes_sociales']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Descripción</p>
                        <p class="text-gray-800 font-semibold"><?= htmlspecialchars($usuarios['descripcion']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!empty($usuarios['mapa_url'])): ?>
            <div class="bg-white p-6 rounded-lg shadow mt-6 text-center sub-card">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2 inline-block">Ubicación en el Mapa</h2>

                <div class="flex justify-center">
                    <div class="w-full max-w-xl rounded-xl border border-gray-200 shadow-md overflow-hidden">
                        <?= $usuarios['mapa_url'] ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
       </div>
       <div class="tab-pane fade" id="mascotas" role="tabpanel">

        <!-- Lista de mascotas -->
        <div class="bg-white p-6 rounded-lg shadow sub-card">
            <h2 class="text-xl font-semibold mb-6 border-b pb-2">Mascotas del Refugio</h2>
            <table class="table table-hover align-middle table-elegante">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Especie</th>
                        <th>Raza</th>
                        <th>Edad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($mascota = $result_mascotas->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($mascota['nombre']) ?></td>
                            <td><?= htmlspecialchars($mascota['especie']) ?></td>
                            <td><?= htmlspecialchars($mascota['raza']) ?></td>
                            <td><?= htmlspecialchars($mascota['edad']) ?></td>
                            <td>
                                <a href="perfilmascota.php?id=<?= $mascota['id'] ?>" class="ver-perfil-btn w-100 text-center">Ver Perfil</a>
                                <?php if ($_SESSION["usuario_tipo"] === "Refugio"): ?>
                                    <form action="eliminar_mascota.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta mascota? Esta acción no se puede deshacer.');" style="display:inline;">
                                        <input type="hidden" name="mascota_id" value="<?= $mascota['id'] ?>">
                                        <input type="hidden" name="refugio_id" value="<?= $id ?>">
                                        <button type="submit" class="btn-eliminar w-100 text-center">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
       </div>
            <div class="tab-pane fade" id="solicitudes" role="tabpanel">
                <div class="bg-white p-6 rounded-lg shadow sub-card">
                    <h2 class="text-xl font-semibold mb-6 border-b pb-2">Solicitudes de Adopción Recibidas</h2>

                    <?php if ($result_solicitudes->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-elegante">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mascota</th>
                                        <th>Adoptante</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($solicitud = $result_solicitudes->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($solicitud['nombre_mascota']) ?></td>
                                            <td><?= htmlspecialchars($solicitud['nombre_adoptante']) ?></td>
                                            <td><?= htmlspecialchars($solicitud['estado']) ?></td>
                                            <td><?= date('d-m-Y H:i', strtotime($solicitud['fecha_solicitud'])) ?></td>
                                            <td class="text-center align-middle">
                                                <div class="mb-2 d-flex justify-content-center gap-2 flex-wrap">
                                                    <a href="perfilmascota.php?id=<?= $solicitud['mascota_id'] ?>" class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition w-32">Ver Mascota</a>
                                                    <a href="perfiladoptante.php?id=<?= $solicitud['adoptante_id'] ?>" class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition w-32">Ver Solicitante</a>
                                                </div>
                                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                                    <form action="procesar_solicitud_refugio.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="accion" value="aceptar">
                                                        <input type="hidden" name="solicitud_id" value="<?= $solicitud['id'] ?>">
                                                        <input type="hidden" name="mascota_id" value="<?= $solicitud['mascota_id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-success w-32">Aceptar</button>
                                                    </form>
                                                    <form action="procesar_solicitud_refugio.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="accion" value="rechazar">
                                                        <input type="hidden" name="solicitud_id" value="<?= $solicitud['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger w-32">Rechazar</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-gray-600">No hay solicitudes de adopción registradas.</p>
                    <?php endif; ?>
                </div>
            </div>
        <div class="tab-pane fade" id="adoptadas" role="tabpanel">
        <div class="bg-white p-6 rounded-lg shadow sub-card">
            <h2 class="text-xl font-semibold mb-6 border-b pb-2">Mascotas Adoptadas</h2>
                <?php if ($result_adoptadas->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-elegante">
                            <thead class="table-light">
                                <tr>
                                    <th>Mascota</th>
                                    <th>Adoptante</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result_adoptadas->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['nombre_mascota']) ?></td>
                                        <td><?= htmlspecialchars($row['nombre_adoptante']) ?></td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-2 align-items-center">
                                                <a href="perfiladoptante.php?id=<?= $row['adoptante_id'] ?>" class="ver-perfil-btn w-100 text-center">Ver Adoptante</a>
                                                <a href="seguimiento.php?id=<?= $row['mascota_id'] ?>" class="ver-perfil-btn w-100 text-center">Ver Seguimiento</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-600">No hay mascotas adoptadas aún.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="tab-pane fade" id="documentos" role="tabpanel">
            <div class="bg-white p-6 rounded-lg shadow sub-card">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2">Documentos del Refugio</h2>

                <?php if (empty($documentos)): ?>
                    <p class="text-gray-600">No hay documentos subidos por este refugio.</p>
                <?php else: ?>
                    <ul class="space-y-4">
                        <?php foreach ($documentos as $doc): ?>
                            <li class="flex items-center justify-between documento-item p-4 rounded-lg shadow-sm">
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
      </div>
      
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

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
