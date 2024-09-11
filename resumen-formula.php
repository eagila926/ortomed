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

?>
<!DOCTYPE html>
<html lang="en" data-layout="topnav" data-menu-color="brand">

<head>
    <title>Resumen Fórmula</title>
    <?php include 'layouts/title-meta.php'; ?>

    <!-- Plugin css -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>

    <?php include 'layouts/head-css.php'; ?>
</head>

<body>
    <script>
        var user_id = <?php echo json_encode($user_id); ?>;
        console.log("User ID:", user_id);
    </script>
    <!-- Begin page -->
    <div class="wrapper">

        <?php include 'layouts/topbar.php'; ?>
        <?php include 'layouts/horizontal-nav.php'; ?>


        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-12">
                            <h3 id="diasTratamiento">Días Tratamiento: </h3>
                            <h3 id="capsulasDiarias">Cápsulas Diarias: </h3>
                        </div>

                    </div>

                    <div class="row" style="margin-top: 10px;">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Activos Seleccionados</h4>
                                    <table id="tablaFormula" class="table activate-select dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Cod. Odoo</th>
                                                <th>Activo</th>
                                                <th>Cantidad</th>
                                                <th>Unidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>

                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div> <!-- end row-->   
                    
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Valores detallados por activo</h4>
                                    <table id="tablaCalculos" class="table activate-select dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Cod. Odoo</th>
                                                <th>Activo</th>
                                                <th>Cantidad</th>
                                                <th>Unidad</th>
                                                <th>Cantidad en Gramos</th>
                                                <th>Factor</th>
                                                <th>Densidad</th>
                                                <th>Masa Final g</th>
                                                <th>Volumen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>

                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div> <!-- end row-->   
                
                </div>
                <!-- container -->

            </div>
            <!-- content -->

            <?php include 'layouts/footer.php'; ?>

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->
    <script>
        window.onload = function() {
            mostrarDiasTratamiento();
            mostrarActivos();
        };

        // Función para mostrar los días de tratamiento desde localStorage
        function mostrarDiasTratamiento() {
            var diasTratamiento = localStorage.getItem('diasTratamiento');

            if (diasTratamiento) {
                document.getElementById('diasTratamiento').innerHTML = 'Días Tratamiento: ' + diasTratamiento;
            } else {
                document.getElementById('diasTratamiento').innerHTML = 'Días Tratamiento: No disponible';
            }
        }

        // Función para mostrar los activos almacenados en localStorage
        function mostrarActivos() {
            var activos = JSON.parse(localStorage.getItem('activos')) || [];
            var tablaFormula = document.getElementById("tablaFormula").getElementsByTagName('tbody')[0];
            var tablaCalculos = document.getElementById("tablaCalculos").getElementsByTagName('tbody')[0];

            // Limpiar las tablas antes de volver a mostrar los activos
            tablaFormula.innerHTML = "";
            tablaCalculos.innerHTML = "";

            // Recorrer los activos y añadir filas a ambas tablas
            activos.forEach(function(activo, index) {
                // Añadir a tablaFormula
                var nuevaFilaFormula = tablaFormula.insertRow();
                nuevaFilaFormula.insertCell(0).innerHTML = index + 1;
                nuevaFilaFormula.insertCell(1).innerHTML = activo.codOdoo;
                nuevaFilaFormula.insertCell(2).innerHTML = activo.activo;
                nuevaFilaFormula.insertCell(3).innerHTML = activo.cant;
                nuevaFilaFormula.insertCell(4).innerHTML = activo.unidad;

                // Hacer una solicitud AJAX para obtener factor y densidad desde la base de datos
                $.ajax({
                    url: 'datos-activos.php',  // Archivo PHP que consulta los datos
                    type: 'GET',
                    data: { codOdoo: activo.codOdoo },
                    dataType: 'json',
                    success: function(data) {
                        // Lógica para convertir cantidad a gramos según la unidad
                        let cantidadEnGramos;
                        switch (activo.unidad) {
                            case 'g':
                                cantidadEnGramos = parseFloat(activo.cant);
                                break;
                            case 'mg':
                                cantidadEnGramos = parseFloat(activo.cant) / 1000;
                                break;
                            case 'mcg':
                                cantidadEnGramos = parseFloat(activo.cant) / 1000000;
                                break;
                            case 'UI':
                                cantidadEnGramos = parseFloat(activo.cant) / 2.9;
                                break;
                            default:
                                cantidadEnGramos = 0;
                        }

                        // Calcular Masa Final y Volumen
                        let factor = data.factor;
                        let densidad = data.densidad;

                        let masaFinal = cantidadEnGramos * factor;
                        let volumen = masaFinal / densidad;

                        // Redondear a 4 decimales
                        cantidadEnGramos = cantidadEnGramos.toFixed(4);
                        masaFinal = masaFinal.toFixed(4);
                        volumen = volumen.toFixed(4);

                        // Añadir a tablaCalculos
                        var nuevaFilaCalculos = tablaCalculos.insertRow();
                        nuevaFilaCalculos.insertCell(0).innerHTML = index + 1;
                        nuevaFilaCalculos.insertCell(1).innerHTML = activo.codOdoo;
                        nuevaFilaCalculos.insertCell(2).innerHTML = activo.activo;
                        nuevaFilaCalculos.insertCell(3).innerHTML = activo.cant;
                        nuevaFilaCalculos.insertCell(4).innerHTML = activo.unidad;
                        nuevaFilaCalculos.insertCell(5).innerHTML = cantidadEnGramos;
                        nuevaFilaCalculos.insertCell(6).innerHTML = factor;
                        nuevaFilaCalculos.insertCell(7).innerHTML = densidad;
                        nuevaFilaCalculos.insertCell(8).innerHTML = masaFinal;  // Masa final en gramos
                        nuevaFilaCalculos.insertCell(9).innerHTML = volumen;  // Volumen
                    },
                    error: function(xhr, status, error) {
                        console.log("Error al obtener los datos del activo: " + error);
                    }
                });
            });
        }

        // Simulaciones de consulta a la base de datos (implementa esto en PHP)
        function obtenerFactorDesdeBD(codOdoo) {
            // Aquí deberías hacer la consulta real a la base de datos
            return 1.2; // Valor simulado
        }

        function obtenerDensidadDesdeBD(codOdoo) {
            // Aquí deberías hacer la consulta real a la base de datos
            return 0.8; // Valor simulado
        }

    </script>

    <?php include 'layouts/right-sidebar.php'; ?>

    <?php include 'layouts/footer-scripts.php'; ?>

    <!-- Daterangepicker js -->
    <script src="assets/vendor/daterangepicker/moment.min.js"></script>
    <script src="assets/vendor/daterangepicker/daterangepicker.js"></script>

    <!-- Apex Charts js -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Vector Map js -->
    <script src="assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>

    <!-- Dashboard App js -->
    <script src="assets/js/pages/demo.dashboard.js"></script>

    <!-- App js -->
    <script src="assets/js/app.min.js"></script>

</body>

</html>
