<?php
session_start();

if (isset($_SESSION["usuario_id"]) && isset($_SESSION["usuario_tipo"])) {
    $tipo = $_SESSION["usuario_tipo"];

    if ($tipo === "Adoptante") {
        header("Location: index.php");
    } elseif ($tipo === "Refugio") {
        header("Location: perfilrefugio.php?id=" . $_SESSION["usuario_id"]);
    } elseif ($tipo === "Administrador") {
        header("Location: indexadmin.php");
    } else {
        header("Location: perfil.php?id=" . $_SESSION["usuario_id"]);
    }
    exit();
}

include("conexion.php");
$conexion->set_charset("utf8mb4");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $contrasena = $_POST["contrasena"];

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($contrasena, $usuario["contrasena"])) {
            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["nombre"] = $usuario["nombre_completo"];
            $_SESSION["usuario_tipo"] = trim($usuario["tipo"]); // limpiar espacios

            $tipo = $_SESSION["usuario_tipo"]; // ya está limpio

            if ($tipo === "Adoptante") {
                header("Location: index.php");
            } elseif ($tipo === "Refugio") {
                header("Location: perfilrefugio.php?id=" . $usuario["id"]);
            } elseif ($tipo === "Administrador") {
                header("Location: indexadmin.php");
            } else {
                $error = "Tipo de usuario no válido.";
                session_destroy();
            }
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adopta Web - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./assets/css/login.css">
</head>
<body>
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center login-bg">
        <div class="card login-card shadow-lg">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <img src="./assets/img/login/logo-adopta-web.png" alt="Adopta Web Logo" class="login-logo mb-3 rounded-circle shadow-sm" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #0d6efd;">
                    <h2 class="fw-bold">Adopta Web</h2>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-danger text-center"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-4 position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" name="email" placeholder="Correo Electrónico" required>
                        </div>
                        <div class="invalid-feedback">Por favor ingrese un correo electrónico válido</div>
                    </div>
                    <div class="mb-4 position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" name="contrasena" placeholder="Contraseña" required minlength="8">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">Iniciar Sesión</button>
                    <a href="registroadoptante.php" class="btn btn-primary w-100 mb-3 text-decoration-underline">Regístrate como Adoptante</a>
                    <a href="registrorefugio.php" class="btn btn-primary w-100 mb-3 text-decoration-underline">Regístrate como Refugio</a>
                    <div class="text-center">
                        <a href="restablecercontrasena.php" class="text-decoration-underline forgot-password">Olvidé mi contraseña</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.querySelector('input[name="contrasena"]');
            const icon = this.querySelector('i');
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                passwordInput.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        });
    </script>
</body>
</html>
