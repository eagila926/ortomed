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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>

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
                                                    <input type="text" class="form-control" id="activo" name="activo"
                                                    onkeyup="buscarActivo(this.value)"
                                                    placeholder="Ingrese el nombre del activo" autocomplete="off">
                                                </div>
                                                <div id="resultados-activos" style="float:left;"></div>                                           

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
                                                <select class="form-select" id="unidad" name="unidad">
                                                    <option value=""></option>
                                                    <option value="g">g</option>
                                                    <option value="mg">mg</option>
                                                    <option value="mcg">mcg</option>
                                                    <option value="UI">UI</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <button type="button" class="btn btn-primary" onclick="agregarFila()">Añadir</button>
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
                                    <table id="tablaFormula" class="table activate-select dt-responsive nowrap w-100">
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
    <script>
        function buscarActivo(valor) {
            $.ajax({
                type: "POST",
                url: "buscar-producto.php",
                data: { producto: valor },  // Pasar el valor ingresado
                success: function (data) {
                    if (data == "") {
                        $('#resultados-activos').fadeOut(500);
                    } else {
                        $('#resultados-activos').fadeIn(500).html(data);
                        $('.suggest-element').click(function () {
                            var nombreSeleccionado = $(this).data('cod_odoo');
                            $('#activo').val($(this).text());
                            $('#resultados-activos').fadeOut(100);
                        });
                    }
                }
            });
        }


        function seleccionarActivo(nombre) {
            console.log("Seleccionado activo:", nombre); // Verifica si seleccionas correctamente
            document.getElementById("activo").value = nombre;
            document.getElementById("resultados-activos").innerHTML = ""; // Limpiar los resultados después de seleccionar
        }

        function agregarFila() {
            var activo = document.getElementById("activo").value;
            var cant = document.getElementById("cant").value;
            var unidad = document.getElementById("unidad").value;
            
            if (activo && cant && unidad) {
                var tabla = document.getElementById("tablaFormula").getElementsByTagName('tbody')[0];
                var nuevaFila = tabla.insertRow();

                var celda1 = nuevaFila.insertCell(0);
                var celda2 = nuevaFila.insertCell(1);
                var celda3 = nuevaFila.insertCell(2);
                var celda4 = nuevaFila.insertCell(3);
                var celda5 = nuevaFila.insertCell(4);

                celda1.innerHTML = tabla.rows.length;
                celda2.innerHTML = activo;
                celda3.innerHTML = cant;
                celda4.innerHTML = unidad;
                celda5.innerHTML = '<button class="btn btn-danger" onclick="eliminarFila(this)">X</button>';

                // Limpiar los campos después de añadir la fila
                document.getElementById("activo").value = "";
                document.getElementById("cant").value = "";
                document.getElementById("unidad").value = "";
            } else {
                alert("Por favor, complete todos los campos.");
            }
        }

        function eliminarFila(boton) {
            var fila = boton.parentNode.parentNode;
            fila.parentNode.removeChild(fila);
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