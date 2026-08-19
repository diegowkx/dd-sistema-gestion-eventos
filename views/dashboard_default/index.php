<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../layouts/head.php'; ?>
</head>
<body class="font-primary-family">
    <?php $datosGenerales = $datosGenerales ?? ['eventos_totales' => 0, 'eventos_publicados' => 0, 'eventos_pendientes' => 0, 'eventos_rechazados' => 0]; ?>
    <div id="main-wrapper">
        <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>
        <div class="content-body">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="text-bold mb-1">Eventos publicados</h1>
                        <p class="text-muted mb-0">Resumen general del sistema.</p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-6 col-lg-3"><div class="card card-custom border-0 shadow-sm rounded-4 h-100"><div class="card-body"><span class="extra-small text-muted text-uppercase fw-bold">Totales</span><h2 class="fw-bold mb-0 mt-2"><?php echo $datosGenerales['eventos_totales']; ?></h2></div></div></div>
                    <div class="col-6 col-lg-3"><div class="card card-custom border-0 shadow-sm rounded-4 h-100"><div class="card-body"><span class="extra-small text-muted text-uppercase fw-bold">Publicados</span><h2 class="fw-bold text-success mb-0 mt-2"><?php echo $datosGenerales['eventos_publicados']; ?></h2></div></div></div>
                    <div class="col-6 col-lg-3"><div class="card card-custom border-0 shadow-sm rounded-4 h-100"><div class="card-body"><span class="extra-small text-muted text-uppercase fw-bold">Pendientes</span><h2 class="fw-bold text-warning mb-0 mt-2"><?php echo $datosGenerales['eventos_pendientes']; ?></h2></div></div></div>
                    <div class="col-6 col-lg-3"><div class="card card-custom border-0 shadow-sm rounded-4 h-100"><div class="card-body"><span class="extra-small text-muted text-uppercase fw-bold">Rechazados</span><h2 class="fw-bold text-danger mb-0 mt-2"><?php echo $datosGenerales['eventos_rechazados']; ?></h2></div></div></div>
                </div>
            </div>
        </div>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
    <?php include __DIR__ . '/../layouts/scripts.php'; ?>
</body>
</html>
