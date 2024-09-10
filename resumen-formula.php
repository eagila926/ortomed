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
            var tabla = document.getElementById("tablaFormula").getElementsByTagName('tbody')[0];

            // Limpiar la tabla antes de volver a mostrar los activos
            tabla.innerHTML = "";

            // Recorrer los activos y añadir filas a la tabla
            activos.forEach(function(activo, index) {
                var nuevaFila = tabla.insertRow();

                var celda1 = nuevaFila.insertCell(0);
                var celda2 = nuevaFila.insertCell(1);  // Columna para Cod. Odoo
                var celda3 = nuevaFila.insertCell(2);  // Columna para Activo
                var celda4 = nuevaFila.insertCell(3);  // Columna para Cantidad
                var celda5 = nuevaFila.insertCell(4);  // Columna para Unidad

                celda1.innerHTML = index + 1;
                celda2.innerHTML = activo.codOdoo;
                celda3.innerHTML = activo.activo;
                celda4.innerHTML = activo.cant;
                celda5.innerHTML = activo.unidad;
            });
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
