
<?php
session_start();
if (isset($_SESSION["usuario_id"])) {
    header("Location: index.php");
    exit();
}

include("conexion.php");

$conexion->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'conexion.php';

    $rut = trim($_POST['rut']);
    $correo = trim($_POST['email']);
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    if ($nueva_contrasena !== $confirmar_contrasena) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE rut = ? AND email = ?");
        $stmt->bind_param("ss", $rut, $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            $usuario_id = $usuario['id'];

            $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
            $update = $conexion->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
            $update->bind_param("si", $hash, $usuario_id);

            if ($update->execute()) {
                // ✅ Solo si se actualiza correctamente, redirige
                header("Location: login.php");
                exit();
            } else {
                $error = "Error al actualizar la contraseña.";
            }
        } else {
            $error = "RUT o Correo Institucional incorrectos.";
        }
    }
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adopta Web - Restablecer Contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="./assets/img/login/logo-adopta-web.png" alt="Logo" class="w-32 h-32 mx-auto mb-6 object-cover rounded-full border-4 border-white shadow-lg">
            <h1 class="text-2xl font-bold text-gray-800">Restablecer Contraseña</h1>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-sm mb-4 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($mensaje)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded text-sm mb-4 text-center">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="bg-white rounded-lg shadow-xl p-8 space-y-6">
            <div class="space-y-2">
                <label for="rut" class="block text-sm font-medium text-gray-700">RUT</label>
                <input type="text" id="rut" name="rut" placeholder="Ingrese su RUT (con puntos y guión)" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" pattern="^\d{1,2}\.\d{3}\.\d{3}[-][0-9kK]{1}$">
                <p class="hidden text-red-600 text-sm">Formato de RUT inválido</p>
            </div>

            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                <input type="email" id="email" name="email" placeholder="usuario@gmail.com" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                <p class="hidden text-red-600 text-sm">Correo Electrónico inválido</p>
            </div>

            <div class="space-y-2">
                <label for="nueva_contrasena" class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                <div class="relative">
                    <input type="password" id="nueva_contrasena" name="nueva_contrasena" required minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" onclick="togglePassword('nueva_contrasena')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <div class="h-2 bg-gray-200 rounded-full mt-2">
                    <div class="h-full bg-green-500 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <div class="space-y-2">
                <label for="confirmar_contrasena" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                <div class="relative">
                    <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700" onclick="togglePassword('confirmar_contrasena')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <p class="hidden text-red-600 text-sm">Las contraseñas no coinciden</p>
            </div>

            <div class="space-y-4">
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span>Restablecer Contraseña</span>
                </button>
                <a href="login.php" class="block text-center text-sm text-blue-600 hover:text-blue-800 transition-colors">Volver al Inicio de Sesión</a>
            </div>
        </form>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
            } else {
                input.type = "password";
            }
        }
    </script>

</body>
</html>