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
    <title>Formulas No Establecidas</title>
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
                                        <div class="col-lg-12">
                                            <label for="text" id="minMaxLabel" class="form-label">Mínimo: ; Máximo: </label>
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
                                            <button type="button" class="btn btn-primary" onclick="agregarFila(); guardarBorrador();">Añadir</button>
                                        </div>                                        
                                    </form>
                                        
                                    </div>
                                    

                                </div> <!-- end card-->
                                <div class="row" style="margin-top: 10px;">
                                    <div class="col-lg-11"></div>
                                    <div class="col-lg-1">
                                            <button type="button" class="btn btn-success">Cotizar</button>
                                    </div>
                                </div>
                                
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
                                                <th>Cod. Odoo</th>
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
                data: { producto: valor },
                success: function (data) {
                    if (data == "") {
                        $('#resultados-activos').fadeOut(500);
                    } else {
                        $('#resultados-activos').fadeIn(500).html(data);
                        $('.suggest-element').click(function () {
                            var codOdoo = $(this).data('cod_odoo');  // Capturar cod_odoo
                            var nombreActivo = $(this).text();  // Capturar el nombre del activo
                            var minimo = $(this).data('minimo');  // Capturar el valor mínimo
                            var maximo = $(this).data('maximo');  // Capturar el valor máximo

                            // Almacenar el cod_odoo en el input como un data attribute
                            $('#activo').val(nombreActivo).data('cod_odoo', codOdoo);

                            // Mostrar el valor mínimo y máximo en el label
                            $('#minMaxLabel').text('Mínimo: ' + minimo + ' ; Máximo: ' + maximo);

                            console.log("Código Odoo seleccionado:", codOdoo);
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
            var codOdoo = $('#activo').data('cod_odoo');  // Recuperar cod_odoo del input
            var cant = document.getElementById("cant").value;
            var unidad = document.getElementById("unidad").value;

            // Validar que todos los campos estén llenos
            if (activo && cant && unidad && codOdoo) {
                var tabla = document.getElementById("tablaFormula").getElementsByTagName('tbody')[0];
                var filas = tabla.getElementsByTagName('tr');

                // Verificar si el activo o cod_odoo ya está en la tabla
                for (var i = 0; i < filas.length; i++) {
                    var codOdooTabla = filas[i].cells[1].innerText;  // Asumimos que el Cod. Odoo está en la segunda columna
                    var activoTabla = filas[i].cells[2].innerText;    // Asumimos que el Activo está en la tercera columna

                    // Compara si ya existe el activo o el cod_odoo
                    if (codOdoo === codOdooTabla || activo === activoTabla) {
                        alert("El activo ya ha sido ingresado.");
                        return;  // No agregar la fila
                    }
                }

                // Si no existe, agregar la nueva fila
                var nuevaFila = tabla.insertRow();

                var celda1 = nuevaFila.insertCell(0);
                var celda2 = nuevaFila.insertCell(1);  // Columna para Cod. Odoo
                var celda3 = nuevaFila.insertCell(2);  // Columna para Activo
                var celda4 = nuevaFila.insertCell(3);  // Columna para Cantidad
                var celda5 = nuevaFila.insertCell(4);  // Columna para Unidad
                var celda6 = nuevaFila.insertCell(5);  // Columna para botón Eliminar

                celda1.innerHTML = tabla.rows.length;
                celda2.innerHTML = codOdoo;  // Mostrar el código Odoo en la columna
                celda3.innerHTML = activo;
                celda4.innerHTML = cant;
                celda5.innerHTML = unidad;
                celda6.innerHTML = '<button class="btn btn-danger" onclick="eliminarFila(this)">X</button>';

                // Llamar a la función guardarBorrador con los valores capturados
                guardarBorrador(activo, codOdoo, cant, unidad);

                // Limpiar los campos después de añadir la fila
                document.getElementById("activo").value = "";
                document.getElementById("cant").value = "";
                document.getElementById("unidad").value = "";
                $('#activo').data('cod_odoo', '');  // Limpiar el data attribute de cod_odoo
            } else {
                alert("Por favor, complete todos los campos.");
            }
        }


        function guardarBorrador(activo, codOdoo, cant, unidad) {
            if (activo && cant && unidad && codOdoo) {
                $.ajax({
                    type: "POST",
                    url: "guardar_borrador.php",
                    data: {
                        activo: activo,
                        codOdoo: codOdoo,
                        cant: cant,
                        unidad: unidad,
                        idUsuario: user_id
                    },
                    success: function(response) {
                        console.log("Datos guardados temporalmente: " + response);
                    },
                    error: function() {
                        alert("Hubo un error al guardar los datos.");
                    }
                });
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