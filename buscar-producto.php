<?php

include 'layouts/config.php';

if (!empty($_POST['producto'])) {
    $suggest = "";
    $search = $_POST['producto'];
    
    // Consulta SQL para buscar productos por descripción usando LIKE
    $sql = "SELECT cod_odoo, nombre, minimo, maximo FROM activos WHERE nombre LIKE :search ORDER BY nombre ASC";
    
    // Prepara la consulta
    $sth = $pdo->prepare($sql); 
    
    // Bind de parámetros
    $sth->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    
    // Ejecuta la consulta
    $sth->execute();
    
    // Recorre los resultados y genera sugerencias
    while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
        $cod_odoo = $result['cod_odoo'];
        $nombre = $result['nombre'];
        $minimo = $result['minimo'];
        $maximo = $result['maximo'];
        
        // Añadir los datos minimo y maximo a cada sugerencia como atributos data-minimo y data-maximo
        $suggest .= '<div class="suggest-element" data-cod_odoo="' . $cod_odoo . '" data-minimo="' . $minimo . '" data-maximo="' . $maximo . '"><a>' . htmlspecialchars($nombre) . '</a></div>';
    }

    echo $suggest;
} else {
    echo "";
}

?>
