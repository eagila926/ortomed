
<?php
include 'layouts/config.php';

// Recibir datos del formulario
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encriptar contraseña
$ciudad = $_POST['ciudad'];
$username = $_POST['username'];
$nivel = $_POST['nivel'];
$pais = $_POST['pais'];
$estado = 1; // Estado por defecto activo

try {
    // Preparar la consulta
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, correo, contrasena, ciudad, username, nivel, pais, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Ejecutar la consulta
    if ($stmt->execute([$nombre, $apellido, $email, $password, $ciudad, $username, $nivel, $pais, $estado])) {
        echo "<script>alert('Nuevo registro creado con éxito'); window.location.href='registrar-user.php';</script>";
        
    } else {
        echo "Error: No se pudo crear el registro";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>