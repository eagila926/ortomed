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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>


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
                    <button onclick="llenarTablaExportable()">Actualizar Tabla Exportable</button>
                    <button onclick="exportarTablaAExcel()">Exportar a Excel</button>
                  
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Valores para importar en Odoo</h4>
                                    <table id="tablaExportable" class="table activate-select dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Líneas de LdM/Componente/Id. de la BD</th>
                                                <th>Líneas de LdM/Cantidad</th>
                                                <th>Líneas de LdM/Unidad de medida del producto/ID</th>
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
                var codPastillero=0;
                var frasco = 0;

                // Agregar condicional para seleccionar entre 'CAPSULA GELATINA 00' y 'CAPSULA GELATINA 0'
                if (codInven == 1078) {
                    agregarFilaFija(codInven, 'CAPSULA GELATINA 00', totCapsulas, 'und', '', '');
                } else if (codInven == 1077) {
                    agregarFilaFija(codInven, 'CAPSULA GELATINA 0', totCapsulas, 'und', '', '');
                }

                if(totCapsulas == 30){
                    codPastillero = 1219;
                    frasco = 1;
                    agregarFilaFija(codPastillero, 'PASTILLERO PEQUEÑO', frasco, 'und', '', '');
                }else if(totCapsulas == 60){
                    codPastillero = 1219;
                    frasco = 1;
                    agregarFilaFija(codPastillero, 'PASTILLERO PEQUEÑO', frasco, 'und', '', '');
                }else if(totCapsulas == 90){
                    codPastillero = 12191;
                    frasco = 1;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }else if(totCapsulas == 120){
                    codPastillero = 12191;
                    frasco = 1;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }else if(totCapsulas == 150){
                    codPastillero = 12191;
                    frasco = 1;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }else if(totCapsulas == 180){
                    codPastillero = 12191;
                    frasco = 2;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }else if(totCapsulas == 210){
                    codPastillero = 12191;
                    frasco = 2;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }else if(totCapsulas == 240){
                    codPastillero = 12191;
                    frasco = 3;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }else if(totCapsulas == 270){
                    codPastillero = 12191;
                    frasco = 3;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }else if(totCapsulas == 300){
                    codPastillero = 12191;
                    frasco = 3;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }else if(totCapsulas == 330){
                    codPastillero = 12191;
                    frasco = 3;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }else if(totCapsulas == 360){
                    codPastillero = 12191;
                    frasco = 3;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }else if(totCapsulas == 390){
                    codPastillero = 12191;
                    frasco = 3;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }else if(totCapsulas == 420){
                    codPastillero = 12191;
                    frasco = 3;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }else if(totCapsulas == 450){
                    codPastillero = 12191;
                    frasco = 3;
                    agregarFilaFija(codPastillero, 'PASTILLERO GRANDE', frasco, 'und', '', '');
                }

            }

            function agregarFilaFija(codOdoo, activo, cantidad, unidad, factor, densidad) {
                // Recuperar capXcap desde localStorage
                var diasTratamiento = localStorage.getItem('diasTratamiento');
                var cDiaria = localStorage.getItem('cDiaria');
                var masaFinal = cantidad * densidad;
                var volumen = (masaFinal / densidad).toFixed(4);

                var tablaCalculos = document.getElementById("tablaCalculos").getElementsByTagName('tbody')[0];
                var nuevaFila = tablaCalculos.insertRow();

                if (codOdoo == 1101) {
                    nuevaFila.insertCell(0).innerHTML = "";
                    nuevaFila.insertCell(1).innerHTML = codOdoo;
                    nuevaFila.insertCell(2).innerHTML = activo;
                    nuevaFila.insertCell(3).innerHTML = cantidad; // Usar capXcap
                    nuevaFila.insertCell(4).innerHTML = unidad;
                    nuevaFila.insertCell(5).innerHTML = cantidad;
                    nuevaFila.insertCell(6).innerHTML = factor;
                    nuevaFila.insertCell(7).innerHTML = densidad;
                    nuevaFila.insertCell(8).innerHTML = cantidad;
                    nuevaFila.insertCell(9).innerHTML = volumen;
                    nuevaFila.insertCell(10).innerHTML = (cantidad * diasTratamiento * cDiaria).toFixed(4);

                } else if (codOdoo == 1219 || codOdoo == 12191) {
                    nuevaFila.insertCell(0).innerHTML = "";
                    nuevaFila.insertCell(1).innerHTML = codOdoo;

                    // Columna "activo" con opciones
                    var selectActivo = document.createElement('select');
                    selectActivo.innerHTML = `
                        <option value="pastillero_pequeño" ${activo === 'pastillero_pequeño' ? 'selected' : ''}>PASTILLERO PEQUEÑO</option>
                        <option value="pastillero_grande" ${activo === 'pastillero_grande' ? 'selected' : ''}>PASTILLERO GRANDE</option>
                    `;
                    selectActivo.addEventListener('change', function() {
                        // Cambiar codOdoo basado en la selección
                        if (this.value === 'pastillero_pequeño') {
                            nuevaFila.cells[1].innerHTML = 1219; // Actualizar codOdoo
                        } else if (this.value === 'pastillero_grande') {
                            nuevaFila.cells[1].innerHTML = 12191; // Actualizar codOdoo
                        }
                    });
                    nuevaFila.insertCell(2).appendChild(selectActivo);

                    // Columna "cantidad" con opciones de 1 a 3
                    nuevaFila.insertCell(3).innerHTML = `
                        <select>
                            <option value="1" ${cantidad == 1 ? 'selected' : ''}>1</option>
                            <option value="2" ${cantidad == 2 ? 'selected' : ''}>2</option>
                            <option value="3" ${cantidad == 3 ? 'selected' : ''}>3</option>
                        </select>
                    `;

                    // Insertar las demás celdas
                    nuevaFila.insertCell(4).innerHTML = unidad;
                    nuevaFila.insertCell(5).innerHTML = '';
                    nuevaFila.insertCell(6).innerHTML = '';
                    nuevaFila.insertCell(7).innerHTML = '';
                    nuevaFila.insertCell(8).innerHTML = '';
                    nuevaFila.insertCell(9).innerHTML = '';
                    nuevaFila.insertCell(10).innerHTML = cantidad;
                } else {
                    nuevaFila.insertCell(0).innerHTML = "";
                    nuevaFila.insertCell(1).innerHTML = codOdoo;
                    nuevaFila.insertCell(2).innerHTML = activo;
                    nuevaFila.insertCell(3).innerHTML = cantidad; // Usar capXcap
                    nuevaFila.insertCell(4).innerHTML = unidad;
                    nuevaFila.insertCell(5).innerHTML = '';
                    nuevaFila.insertCell(6).innerHTML = '';
                    nuevaFila.insertCell(7).innerHTML = '';
                    nuevaFila.insertCell(8).innerHTML = '';
                    nuevaFila.insertCell(9).innerHTML = '';
                    nuevaFila.insertCell(10).innerHTML = cantidad;
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
            function llenarTablaExportable() {
                // Obtener las tablas
                var tablaCalculos = document.getElementById("tablaCalculos").getElementsByTagName('tbody')[0];
                var tablaExportable = document.getElementById("tablaExportable").getElementsByTagName('tbody')[0];

                // Limpiar el contenido previo de la tablaExportable
                tablaExportable.innerHTML = "";

                // Recorrer cada fila de tablaCalculos
                for (var i = 0; i < tablaCalculos.rows.length; i++) {
                    var filaCalculos = tablaCalculos.rows[i];

                    // Obtener los valores de las columnas necesarias
                    var columna2TablaCalculos = filaCalculos.cells[1].innerText;  // Segunda columna de tablaCalculos
                    var columna10TablaCalculos = filaCalculos.cells[10].innerText; // Décima columna de tablaCalculos
                    var columna5TablaCalculos = filaCalculos.cells[4].innerText;  // Quinta columna de tablaCalculos

                    // Realizar la validación para la tercera columna
                    var valorUnidadMedida;
                    if (["g", "mg", "mcg", "UI"].includes(columna5TablaCalculos)) {
                        valorUnidadMedida = "uom.product_uom_gram";
                    } else if (columna5TablaCalculos === "und") {
                        valorUnidadMedida = "uom.product_uom_unit";
                    } else {
                        valorUnidadMedida = ""; // Si no coincide, dejar vacío o asignar un valor por defecto
                    }

                    // Crear una nueva fila para tablaExportable
                    var nuevaFila = tablaExportable.insertRow();

                    // Insertar los valores en las columnas de tablaExportable
                    nuevaFila.insertCell(0).innerText = columna2TablaCalculos;   // Segunda columna de tablaCalculos
                    nuevaFila.insertCell(1).innerText = columna10TablaCalculos;  // Décima columna de tablaCalculos
                    nuevaFila.insertCell(2).innerText = valorUnidadMedida;       // Validación de la quinta columna de tablaCalculos
                }
            }
            function exportarTablaAExcel() {
                // Obtener la tabla
                var tablaExportable = document.getElementById("tablaExportable");

                // Crear una nueva hoja de trabajo de Excel (worksheet) a partir de la tabla HTML
                var wb = XLSX.utils.table_to_book(tablaExportable, {sheet: "Hoja1"});

                // Generar el archivo Excel y descargarlo
                XLSX.writeFile(wb, "tablaExportable.xlsx");
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
