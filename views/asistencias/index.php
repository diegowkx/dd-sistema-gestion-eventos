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
                                        <div class="luxury-icon-box rounded-2"><i class="fas fa-user-check"></i></div>
                                        <div>
                                            <span class="luxury-subtitle">Gestión de Eventos</span>
                                            <h2 class="card-title mb-0 fw-bold fs-4 text-dark-blue">Control de Asistencias</h2>
                                            <p class="text-muted extra-small mb-0">Listado de participantes inscritos y registro de asistencia en eventos aprobados.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <!-- BARRA DE FILTRO DE EVENTO Y CHECK-IN RÁPIDO -->
                                <div class="row g-3 mb-4 align-items-end">
                                    <div class="col-lg-6 col-md-12">
                                        <label for="filtro_evento" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">
                                            <i class="fas fa-calendar-alt me-1"></i>Seleccionar Evento
                                        </label>
                                        <select id="filtro_evento" class="form-select form-select-luxury rounded-2 border">
                                            <option value="">-- Todos los Eventos Aprobados --</option>
                                            <?php if (!empty($eventosDisponibles) && is_array($eventosDisponibles)): ?>
                                                <?php foreach ($eventosDisponibles as $ev): ?>
                                                    <option value="<?= (int)$ev['id'] ?>">
                                                        <?= htmlspecialchars($ev['titulo']) ?> (<?= htmlspecialchars($ev['fecha_evento']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <label for="input_checkin_doc" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">
                                            <i class="fas fa-id-card me-1"></i>Check-in Rápido por Documento / DNI
                                        </label>
                                        <div class="input-group">
                                            <input type="text" id="input_checkin_doc" class="form-control form-control-luxury rounded-start-2 border" placeholder="Ingrese o escanee el Documento / DNI...">
                                            <button class="btn btn-luxury-primary rounded-end-2 px-4 fw-semibold d-inline-flex align-items-center gap-2" type="button" id="btn_checkin_rapido">
                                                <i class="fas fa-check-circle fs-7"></i><span>Marcar</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- TARJETAS DE RESUMEN KPI -->
                                <div id="contenedor_resumen_kpis" class="row g-3 mb-4 d-none">
                                    <div class="col-6 col-md-3">
                                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                            <div>
                                                <span class="text-muted extra-small text-uppercase fw-bold d-block">Inscritos</span>
                                                <h4 class="mb-0 fw-bold text-dark" id="kpi_total_inscritos">0</h4>
                                            </div>
                                            <div class="text-primary fs-3 opacity-50"><i class="fas fa-users"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                            <div>
                                                <span class="text-muted extra-small text-uppercase fw-bold d-block">Asistieron</span>
                                                <h4 class="mb-0 fw-bold text-success" id="kpi_total_asistieron">0</h4>
                                            </div>
                                            <div class="text-success fs-3 opacity-50"><i class="fas fa-user-check"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                            <div>
                                                <span class="text-muted extra-small text-uppercase fw-bold d-block">Pendientes</span>
                                                <h4 class="mb-0 fw-bold text-warning" id="kpi_total_pendientes">0</h4>
                                            </div>
                                            <div class="text-warning fs-3 opacity-50"><i class="fas fa-user-clock"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                            <div>
                                                <span class="text-muted extra-small text-uppercase fw-bold d-block">Certificados</span>
                                                <h4 class="mb-0 fw-bold text-info" id="kpi_total_certificados">0</h4>
                                            </div>
                                            <div class="text-info fs-3 opacity-50"><i class="fas fa-certificate"></i></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PESTAÑAS DE FILTRO POR ESTADO DE ASISTENCIA -->
                                <div class="w-100 text-start mb-4">
                                    <ul class="nav nav-tabs-luxury gap-3" id="pills-tab-asistencias" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-filtro active fw-semibold d-flex align-items-center gap-2" data-filtro="TODOS" type="button">
                                                <i class="fas fa-list fs-7"></i><span>Todos</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-filtro fw-semibold d-flex align-items-center gap-2" data-filtro="ASISTIO" type="button">
                                                <i class="fas fa-check-circle fs-7"></i><span>Asistieron</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-filtro fw-semibold d-flex align-items-center gap-2" data-filtro="PENDIENTE" type="button">
                                                <i class="fas fa-hourglass-half fs-7"></i><span>Pendientes</span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                <!-- TABLA DE ASISTENCIAS -->
                                <div class="table-responsive">
                                    <table id="datatable_asistencias" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 200px;">Participante</th>
                                                <th style="min-width: 120px;">Documento</th>
                                                <th style="min-width: 110px;">Teléfono</th>
                                                <th style="min-width: 140px;">Asistencia</th>
                                                <th style="min-width: 120px;">Certificado</th>
                                                <th style="min-width: 130px;">Fecha Registro</th>
                                                <th class="text-center" style="width: 100px;">Acciones</th>
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
    <script src="<?php echo BASE_URL; ?>/views/asistencias/asistencia.js"></script>
</body>
</html>
