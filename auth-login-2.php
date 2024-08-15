<?php
// Iniciar la sesión solo si no está ya iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'layouts/config.php'; 
include 'layouts/main.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    // Validar si los campos no están vacíos
    if (empty($correo) || empty($password)) {
        die("Por favor, completa ambos campos.");
    }

    // Consulta para obtener la contraseña del usuario
    $sql = "SELECT id, correo, contrasena, nombre, apellido FROM usuarios WHERE correo = :correo AND estado = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['correo' => $correo]);
    $user = $stmt->fetch();

    // Verificar si el usuario existe y la contraseña es correcta
    if ($user && password_verify($password, $user['contrasena'])) {
        // Iniciar sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['correo'] = $user['correo'];
        $_SESSION['nombre'] = $user['nombre'];  
        $_SESSION['apellido'] = $user['apellido'];  
         // Redirigir a la página de inicio
        header("Location: index.php");
        exit(); // Es importante terminar el script después de redirigir
        
    } else {
        echo "Nombre de usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Log In | Ortomed</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>
<body class="authentication-bg pb-0">
    <div class="auth-fluid">
        <div class="auth-fluid-form-box">
            <div class="card-body d-flex flex-column h-100 gap-3">
                <div class="auth-brand text-center text-lg-start">
                    <a href="index.php" class="logo-dark">
                        <span><img src="assets/images/logo_escollanos_fblanco.png" alt="dark logo" height="60"></span>
                    </a>
                    <a href="index.php" class="logo-light">
                        <span><img src="assets/images/logo_vertical.png" alt="logo" height="22"></span>
                    </a>
                </div>
                <div class="my-auto">
                    <h4 class="mt-0">Iniciar Sesión</h4>
                    <p class="text-muted mb-4">Ingrese su usuario y contraseña</p>
                    <form action="auth-login-2.php" method="post">
                        <div class="mb-3">
                            <label for="emailaddress" class="form-label">Email</label>
                            <input class="form-control" type="email" id="emailaddress" name="correo" required placeholder="Enter your email">
                        </div>
                        <div class="mb-3">
                            <a href="auth-recoverpw-2.php" class="text-muted float-end"><small>Olvidaste tu contraseña</small></a>
                            <label for="password" class="form-label">Password</label>
                            <input class="form-control" type="password" id="password" name="password" required placeholder="Enter your password">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="checkbox-signin">
                                <label class="form-check-label" for="checkbox-signin">Recordar mi cuenta</label>
                            </div>
                        </div>
                        <div class="d-grid mb-0 text-center">
                            <button class="btn btn-primary" type="submit"><i class="ri-login-box-line"></i> Log In </button>
                        </div>
                    </form>
                </div>
                <footer class="footer footer-alt">
                    <p class="text-muted">Escollanos 2024</p>
                </footer>
            </div>
        </div>
        <div class="auth-fluid-right text-center">
            <div class="auth-user-testimonial">
                <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <h2 class="mb-3">I love the color!</h2>
                            <p class="lead"><i class="ri-double-quotes-l"></i> Everything you need is in this template. Love the overall look and feel. Not too flashy, and still very professional and smart.</p>
                            <p>- Admin User</p>
                        </div>
                        <div class="carousel-item">
                            <h2 class="mb-3">Flexibility !</h2>
                            <p class="lead"><i class="ri-double-quotes-l"></i> Pretty nice theme, hoping you guys could add more features to this. Keep up the good work.</p>
                            <p>- Admin User</p>
                        </div>
                        <div class="carousel-item">
                            <h2 class="mb-3">Feature Availability!</h2>
                            <p class="lead"><i class="ri-double-quotes-l"></i> This is a great product, helped us a lot and very quick to work with and implement.</p>
                            <p>- Admin User</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'layouts/footer-scripts.php'; ?>
    <script src="assets/js/app.min.js"></script>
</body>
</html>
