?>
<!DOCTYPE html>
<html lang="en" data-layout="topnav" data-menu-color="brand">

<head>
	<title>Cotizador Ortomed</title>
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

					<div class="row">

						<div class="col-12">
							<div class="page-title-box">
								<h4 class="page-title">Registro de Usuarios</h4>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-6">
                            <form action="register_action.php" method="POST">
								<div class="mb-3">
									<label for="simpleinput" class="form-label">Nombre</label>
									<input type="text" id="simpleinput" class="form-control" name="nombre">
								</div>

								<div class="mb-3">
									<label for="example-email" class="form-label">Email</label>
									<input type="email" id="example-email" name="email" class="form-control" placeholder="Email">
								</div>

								<div class="mb-3">
									<label for="password" class="form-label">Password</label>
									<div class="input-group input-group-merge">
										<input type="password" id="password" name="password" class="form-control" placeholder="Enter your password">
										<div class="input-group-text" data-password="false">
											<span class="password-eye"></span>
										</div>
									</div>
								</div>

								<div class="mb-3">
									<label for="simpleinput" class="form-label">Ciudad</label>
									<input type="text" id="simpleinput" name="ciudad" class="form-control">
								</div>
						</div>

                        <div class="col-6">
                            <div class="mb-3">
								<label for="simpleinput" class="form-label">Apellido</label>
								<input type="text" id="simpleinput" name="apellido" class="form-control">
							</div>

							<div class="mb-3">
                            <label for="example-select" class="form-label">Nivel</label>
                            <select class="form-select" id="example-select" name="nivel">
                                <option value="L">Laboratorio</option>
                                <option value="T">Técnico</option>
                                <option value="F">Farmacia</option>
                                <option value="V">Visitador</option>
                                <option value="C">Call Center</option>
                                <option value="A">Administrador</option>
                            </select>
                        </div>

                            <div class="mb-3">
								<label for="simpleinput" class="form-label">Username</label>
								<input type="text" id="simpleinput" name="username" class="form-control">
							</div>

                            <div class="mb-3">
								<label for="example-select" class="form-label">País</label>
								<select class="form-select" id="example-select" name="pais">
									<option>Ecuador</option>
									<option>Chile</option>
									<option>Perú</option>
									<option>Colombia</option>
									<option>Call Center</option>
                                    <option>Administrador</option>
								</select>
                            </div>

                            <button type="submit" class="btn btn-primary">Registrar</button>
                            </form>
						</div>
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
