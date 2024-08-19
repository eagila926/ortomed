<?php
// Iniciar la sesión solo si no está ya iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    // Si no ha iniciado sesión, redirigir al formulario de login
    header("Location: auth-login-2.php");
    exit(); // Termina el script después de redirigir
}
?>
<!DOCTYPE html>
<html lang="en" data-layout="topnav" data-menu-color="brand">

<head>
    <title>Formulas No Establecidas</title>
    <?php include 'layouts/title-meta.php'; ?>

    <!-- Plugin css -->
    <link rel="stylesheet" href="assets/vendor/daterangepicker/daterangepicker.css">
    <link href="assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />

    <?php include 'layouts/head-css.php'; ?>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <?php include 'layouts/topbar.php'; ?>
        <?php include 'layouts/horizontal-nav.php'; ?>


        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="row" style="margin-top: 10px;">
                            <div class="col-sm-12">
                                <div class="card card-body">
                                    <h3 class="card-title">Ingrese los activos</h3>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <form>
                                                <div class="mb-12">
                                                    <label for="simpleinput" class="form-label">Activo:</label>
                                                    <input type="text" class="form-control" id="formula" name="formula"
                                                    placeholder="Ingrese el nombre del activo" autocomplete="off">
                                                </div>
                                            

                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-6">
                                                    <label for="example-number" class="form-label">Cantidad:</label>
                                                    <input type="number" class="form-control" id="cant" name="cant" required>
                                                </div>

                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label for="unidad" class="form-label">Unidad:</label>
                                                <select class="form-select" id="example-select" name="unidad">
                                                    <option value=""></option>
                                                    <option value="g">g</option>
                                                    <option value="mg">mg</option>
                                                    <option value="mcg">mcg</option>
                                                    <option value="UI">UI</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                    </form>
                                        
                                    </div>
                                    

                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div>  

                        <div class="row" style="margin-top: 10px;">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Activos Seleccionados</h4>
                                    <table id="state-saving-datatable" class="table activate-select dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Activo</th>
                                                <th>Cantidad</th>
                                                <th>Unidad</th>
                                                <th>Eliminar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>

                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div> <!-- end row-->                             

                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div>  
                
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