<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../layouts/head.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../layouts/preloader.php'; ?>
    <div id="main-wrapper">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>
        <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

        <div class="content-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card card-luxury border-0 shadow-sm rounded-3 bg-white">
                            <div class="card-header-luxury p-4 border-bottom border-light">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 w-100">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="luxury-icon-box rounded-2"><i class="fas fa-calendar-check"></i></div>
                                        <div>
                                            <span class="luxury-subtitle">Catálogo de Eventos</span>
                                            <h2 class="card-title mb-0 fw-bold fs-4 text-dark-blue">Supervisión</h2>
                                            <p class="text-muted extra-small mb-0">Revisa, aprueba o rechaza los eventos enviados a publicación.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <div class="alert alert-danger border-0 rounded-3 mb-4 d-flex align-items-start gap-3" role="alert">
                                    <i class="fas fa-exclamation-triangle mt-1"></i>
                                    <div class="small"><strong>Cuidado:</strong> la eliminación definitiva en la pestaña "Papelera de Eventos" borra el registro y sus imágenes de forma permanente. No se puede deshacer.</div>
                                </div>

                                <div class="w-100 text-start mb-4">
                                    <ul class="nav nav-tabs-luxury gap-3" id="pills-tab-supervision" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-filtro active fw-semibold d-flex align-items-center gap-2" data-filtro="PENDIENTE" type="button">
                                                <i class="fas fa-hourglass-half fs-7"></i><span>Pendientes</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-filtro fw-semibold d-flex align-items-center gap-2" data-filtro="APROBADO" type="button">
                                                <i class="fas fa-check-circle fs-7"></i><span>Aprobados</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-filtro fw-semibold d-flex align-items-center gap-2" data-filtro="RECHAZADO" type="button">
                                                <i class="fas fa-times-circle fs-7"></i><span>Rechazados</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-filtro fw-semibold d-flex align-items-center gap-2" data-filtro="ELIMINADOS" type="button">
                                                <i class="fas fa-trash-alt fs-7"></i><span>Papelera de Eventos</span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                <div class="table-responsive">
                                    <table id="datatable_supervision" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th>Título</th>
                                                <th>Tema / Dirección</th>
                                                <th>Registrado por</th>
                                                <th>Publicación</th>
                                                <th>Supervisor</th>
                                                <th>Observaciones</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
    <?php include __DIR__ . '/../layouts/scripts.php'; ?>
    <?php include __DIR__ . '/form_modal.php'; ?>
    <script>
        window.BASE_URL = window.BASE_URL || '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>/views/supervision/supervision.js"></script>
</body>
</html>