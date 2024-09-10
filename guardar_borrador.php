<?php
include 'layouts/config.php';

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
    
    // Obtener las iniciales del nombre y apellido del usuario
    $iniciales = strtoupper($nombre[0] . $apellido[0]);

    // Generar el código: iniciales + mes actual + . + número aleatorio
    $mes = date('m');
    $numeroAleatorio = rand(1, 9999);
    $codigo = $iniciales . $mes . '.' . $numeroAleatorio;

    // Insertar en la tabla formula_borrador
    $query = "INSERT INTO borrador_formulas (cot_formu, id_user, activo, cantidad, unidad) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    
    // Ejecutar la consulta con los parámetros
    if ($stmt->execute([$codigo, $user_id, $activo, $cant, $unidad])) {
        echo "Guardado correctamente";
    } else {
        echo "Error: " . $stmt->errorInfo()[2];
    }
    
    // Cerrar la conexión
    $stmt->closeCursor();
    $pdo = null;
}
?>
