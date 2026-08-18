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
                                        <div class="luxury-icon-box rounded-2"><i class="fas fa-calendar-alt"></i></div>
                                        <div>
                                            <span class="luxury-subtitle">Catálogo de Eventos</span>
                                            <h2 class="card-title mb-0 fw-bold fs-4 text-dark-blue">Eventos</h2>
                                            <p class="text-muted extra-small mb-0">Administra los eventos del sistema. La aprobación y el rechazo se gestionan en el módulo de Supervisión.</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-luxury-primary rounded-2 px-4 py-2-5 fw-semibold d-inline-flex align-items-center gap-2 btn-nuevo-evento">
                                        <i class="fas fa-plus fs-7"></i><span>Nuevo Evento</span>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <div class="alert alert-warning border-0 rounded-3 mb-4 d-flex align-items-start gap-3" role="alert">
                                    <i class="fas fa-info-circle mt-1"></i>
                                    <div class="small"><strong>Importante:</strong> si no cuentas con permiso para aprobar eventos, cualquier modificación (incluyendo cambios en las imágenes) enviará el evento a revisión (PENDIENTE).</div>
                                </div>

                                <div class="w-100 text-start mb-4">
                                    <ul class="nav nav-tabs-luxury gap-3" id="pills-tab-eventos" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-estado active fw-semibold d-flex align-items-center gap-2" data-estado="ACTIVO" type="button">
                                                <i class="fas fa-check-circle fs-7"></i><span>Activos</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-estado fw-semibold d-flex align-items-center gap-2" data-estado="INACTIVO" type="button">
                                                <i class="fas fa-times-circle fs-7"></i><span>Inactivos</span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                <div class="table-responsive">
                                    <table id="datatable_eventos" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 180px;">Título / Tema</th>
                                                <th style="min-width: 130px;">Fecha</th>
                                                <th style="width: 80px;">Cupos</th>
                                                <th style="width: 90px;">Precio</th>
                                                <th style="width: 100px;">Publicación</th>
                                                <th style="min-width: 130px;">Observaciones</th>
                                                <th style="width: 110px;">Registrado por</th>
                                                <th style="width: 110px;">Supervisor</th>
                                                <th class="text-center" style="width: 90px;">Acciones</th>
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

    <script src="<?php echo BASE_URL; ?>/views/eventos/evento.js"></script>
</body>
</html>