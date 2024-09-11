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

// Verificar que se haya enviado el código del activo
if (isset($_GET['codOdoo'])) {
    $codOdoo = $_GET['codOdoo'];

    // Consultar los valores de 'factor' y 'densidad' de la tabla 'activos'
    $sql = "SELECT factor, densidad, valor_costo, valor_venta FROM activos WHERE cod_odoo = :codOdoo";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':codOdoo', $codOdoo, PDO::PARAM_STR);
    $stmt->execute();

    // Obtener el resultado
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Enviar los datos como JSON
        echo json_encode([
            'factor' => $result['factor'],
            'densidad' => $result['densidad'],
            'valor_costo' => $result['valor_costo'],
            'valor_venta' => $result['valor_venta']
        ]);
    } else {
        // Si no se encuentra el activo, enviar respuesta vacía
        echo json_encode([
            'factor' => 0,
            'densidad' => 0,
            'valor_costo' => 0,
            'valor_venta' => 0
        ]);
    }
} else {
    // Si no se envía el parámetro codOdoo
    echo json_encode([
        'error' => 'No se envió el código del activo.'
    ]);
}
?>
