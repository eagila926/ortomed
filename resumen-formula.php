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
                                                <th>Total Formula</th>
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

            // Variable para almacenar el total de volumen
            var volumenTotal = 0;
            var capXcap=0;

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
                var diasTratamiento = localStorage.getItem('diasTratamiento');

                // Limpiar las tablas antes de volver a mostrar los activos
                tablaFormula.innerHTML = "";
                tablaCalculos.innerHTML = "";
                volumenTotal = 0;

                // Recorrer los activos y añadir filas a ambas tablas
                activos.forEach(function(activo, index) {
                    // Añadir a tablaFormula
                    var nuevaFilaFormula = tablaFormula.insertRow();
                    nuevaFilaFormula.insertCell(0).innerHTML = index + 1;
                    nuevaFilaFormula.insertCell(1).innerHTML = activo.codOdoo;
                    nuevaFilaFormula.insertCell(2).innerHTML = activo.activo;
                    nuevaFilaFormula.insertCell(3).innerHTML = activo.cant;
                    nuevaFilaFormula.insertCell(4).innerHTML = activo.unidad;

                    // Solicitud AJAX para obtener factor y densidad
                    $.ajax({
                        url: 'datos-activos.php',
                        type: 'GET',
                        data: { codOdoo: activo.codOdoo },
                        dataType: 'json',
                        success: function(data) {
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
                                    cantidadEnGramos = parseFloat(activo.cant) * 0.00067;
                                    break;
                                default:
                                    cantidadEnGramos = 0;
                            }

                            let factor = data.factor;
                            let densidad = data.densidad;

                            let masaFinal = cantidadEnGramos * factor;
                            let volumen = masaFinal / densidad;
                            let totFormula = masaFinal * diasTratamiento;

                            // Redondear a 4 decimales
                            cantidadEnGramos = cantidadEnGramos.toFixed(4);
                            masaFinal = masaFinal.toFixed(4);
                            volumen = volumen.toFixed(4);
                            totFormula = totFormula.toFixed(4);

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
                            nuevaFilaCalculos.insertCell(8).innerHTML = masaFinal;
                            nuevaFilaCalculos.insertCell(9).innerHTML = volumen;
                            nuevaFilaCalculos.insertCell(10).innerHTML = totFormula;

                            // Sumar el volumen al total
                            volumenTotal += parseFloat(volumen);
                            console.log("Volumen Total: " + volumenTotal);

                            // Llamar a la función para calcular cDiaria y cantidadCap
                            calcularValores(volumenTotal);
                        },
                        error: function(xhr, status, error) {
                            console.log("Error al obtener los datos del activo: " + error);
                        }
                    });
                });

                var capXcap = localStorage.getItem('capXcap');
                let cantidadExipiente = (capXcap * 0.4138).toFixed(4);
                agregarFilaFija(1101, 'ESTERATO DE MAGNECIO', cantidadExipiente, 'g',1,0.4138);

                var cDiaria = localStorage.getItem('cDiaria');
                var codInven = localStorage.getItem('codInven');
                var totCapsulas = localStorage.getItem('diasTratamiento') * cDiaria;

                // Agregar condicional para seleccionar entre 'CAPSULA GELATINA 00' y 'CAPSULA GELATINA 0'
                if (codInven == 1078) {
                    agregarFilaFija(codInven, 'CAPSULA GELATINA 00', totCapsulas, 'und', 0, 0);
                } else if (codInven == 1077) {
                    agregarFilaFija(codInven, 'CAPSULA GELATINA 0', totCapsulas, 'und', '', '');
                }

                //Agregar el número de frascos dependiendo la cantidad total de capsulas

            }

            function agregarFilaFija(codOdoo, activo, cantidad, unidad, factor, densidad) {
                // Recuperar capXcap desde localStorag
                
                var diasTratamiento = localStorage.getItem('diasTratamiento');
                var cDiaria = localStorage.getItem('cDiaria');
                var masaFinal = cantidad*densidad;
                var volumen = (masaFinal / densidad).toFixed(4);


                var tablaCalculos = document.getElementById("tablaCalculos").getElementsByTagName('tbody')[0];
                var nuevaFila = tablaCalculos.insertRow();
                nuevaFila.insertCell(0).innerHTML = "";
                nuevaFila.insertCell(1).innerHTML = codOdoo;
                nuevaFila.insertCell(2).innerHTML = activo;
                nuevaFila.insertCell(3).innerHTML = cantidad; // Usar capXcap
                nuevaFila.insertCell(4).innerHTML = unidad;
                if(codOdoo==1101){
                    nuevaFila.insertCell(5).innerHTML = cantidad;
                nuevaFila.insertCell(6).innerHTML = factor;
                nuevaFila.insertCell(7).innerHTML = densidad;
                nuevaFila.insertCell(8).innerHTML = cantidad;
                nuevaFila.insertCell(9).innerHTML = volumen;
                nuevaFila.insertCell(10).innerHTML = (cantidad*diasTratamiento*cDiaria).toFixed(4);

                }else{
                    nuevaFila.insertCell(5).innerHTML = '';
                    nuevaFila.insertCell(6).innerHTML = '';
                    nuevaFila.insertCell(7).innerHTML = '';
                    nuevaFila.insertCell(8).innerHTML = '';
                    nuevaFila.insertCell(9).innerHTML = '';
                    nuevaFila.insertCell(10).innerHTML = '';
                }
                
            }

            // Función para calcular los valores según el volumen total
            function calcularValores(vTotal) {
                var cDiaria = 1;
                var codInven = 0;
                var capacidadCap = 0;
                var codExipiente = 1101;
                var diasTratamiento = localStorage.getItem('diasTratamiento') || 1;

                if (vTotal <= 0.68) {
                    cDiaria = 1;
                    codInven = 1077;
                    capacidadCap = 0.68;
                } else if (vTotal >= 0.69 && vTotal <= 0.95) {
                    cDiaria = 1;
                    codInven = 1078;
                    capacidadCap = 0.95;
                } else if (vTotal >= 0.96 && vTotal <= 1.36) {
                    cDiaria = 2;
                    codInven = 1077;
                    capacidadCap = 0.68;
                } else if (vTotal >= 1.37 && vTotal <= 1.90) {
                    cDiaria = 2;
                    codInven = 1078;
                    capacidadCap = 0.95;
                } else if (vTotal >= 1.91 && vTotal <= 2.04) {
                    cDiaria = 3;
                    codInven = 1077;
                    capacidadCap = 0.68;
                } else if (vTotal >= 2.05 && vTotal <= 2.85) {
                    cDiaria = 3;
                    codInven = 1078;
                    capacidadCap = 0.95;
                } else if (vTotal >= 2.86 && vTotal <= 3.8) {
                    cDiaria = 4;
                    codInven = 1078;
                    capacidadCap = 0.95;
                } else if (vTotal >= 3.81 && vTotal <= 4.75) {
                    cDiaria = 5;
                    codInven = 1078;
                    capacidadCap = 0.95;
                } else if (vTotal >= 4.76 && vTotal <= 5.7) {
                    cDiaria = 6;
                    codInven = 1078;
                    capacidadCap = 0.95;
                } else if (vTotal >= 5.71 && vTotal <= 6.65) {
                    cDiaria = 7;
                    codInven = 1078;
                    capacidadCap = 0.95;
                } else if (vTotal >= 6.66 && vTotal <= 7.6) {
                    cDiaria = 8;
                    codInven = 1078;
                    capacidadCap = 0.95;
                } else if (vTotal >= 7.7 && vTotal <= 8.55) {
                    cDiaria = 9;
                    codInven = 1078;
                    capacidadCap = 0.95;
                } else if (vTotal >= 8.56 && vTotal <= 9.5) {
                    cDiaria = 10;
                    codInven = 1078;
                    capacidadCap = 0.95;
                }else if (vTotal >= 9.51 && vTotal <= 10.45) {
                    cDiaria = 11;
                    codInven = 1078;
                    capacidadCap = 0.95;
                }
                else if (vTotal >= 10.46 && vTotal <= 11.40) {
                    cDiaria = 12;
                    codInven = 1078;
                    capacidadCap = 0.95;
                }
                else if (vTotal >= 11.41 && vTotal <= 12.35) {
                    cDiaria = 13;
                    codInven = 1078;
                    capacidadCap = 0.95;
                }
                else if (vTotal >= 12.36 && vTotal <= 13.30) {
                    cDiaria = 14;
                    codInven = 1078;
                    capacidadCap = 0.95;
                }
                else if (vTotal >= 13.31 && vTotal <= 14.25) {
                    cDiaria = 15;
                    codInven = 1078;
                    capacidadCap = 0.95;
                }

                localStorage.setItem('codInven', codInven);
                console.log('Codigo de la capsula',codInven);
                localStorage.setItem('cDiaria', cDiaria);
                console.log('Capsulas Diarias',cDiaria);
                var cantidadCap = cDiaria * diasTratamiento;
                document.getElementById('capsulasDiarias').innerHTML = 'Cápsulas Diarias: ' + cDiaria;

                var vtnCapsulas = vTotal / cDiaria; // volumen total por número de cápsulas para saber qué cantidad de excipiente usar
                capXcap = ((capacidadCap - vtnCapsulas)).toFixed(4); // capacidad por cápsula del Esterato

                // Guardar capXcap en localStorage
                localStorage.setItem('capXcap', capXcap);
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
