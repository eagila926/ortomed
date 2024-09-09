<?php
// Iniciar la sesión si no está ya iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: auth-login-2.php");
    exit();
}

// Ahora puedes acceder a los datos de la sesión
$user_id = $_SESSION['user_id'];
$nombre = $_SESSION['nombre'];
$apellido = $_SESSION['apellido'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $activo = $_POST['activo'];
    $codOdoo = $_POST['codOdoo'];
    $cant = $_POST['cant'];
    $unidad = $_POST['unidad'];
    $idUsuario = $_POST['user_id'];
    
    // Obtener las iniciales del nombre y apellido del usuario
    $nombreUsuario = $nombre;  // Suponiendo que tienes el nombre en la sesión
    $apellidoUsuario = $apellido;  // Suponiendo que tienes el apellido en la sesión
    $iniciales = strtoupper($nombreUsuario[0] . $apellidoUsuario[0]);

    // Generar el código: iniciales + mes actual + . + número aleatorio
    $mes = date('m');
    $numeroAleatorio = rand(1, 9999);
    $codigo = $iniciales . $mes . '.' . $numeroAleatorio;

    // Insertar en la tabla formula_borrador
    $query = "INSERT INTO borrador_formulas (cot_formu, id_user, activo, cantidad, unidad) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query); 
    
    $stmt->bind_param("sisss", $codigo, $idUsuario, $activo, $cant, $unidad);

    if ($stmt->execute()) {
        echo "Guardado correctamente";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
