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
                            <div class="card-header-luxury p-3 p-md-4 border-bottom border-light">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 w-100">
                                    <!-- Bloque del Título (ocupa todo el espacio disponible a la izquierda) -->
                                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                                        <div class="luxury-icon-box rounded-2 flex-shrink-0"><i class="fas fa-certificate"></i></div>
                                        <div>
                                            <span class="luxury-subtitle">Emisión y Entregas</span>
                                            <h2 class="card-title mb-0 fw-bold fs-4 text-dark-blue">Certificados Digitales</h2>
                                            <p class="text-muted extra-small mb-0">Emisión de certificados únicamente a participantes que asistieron en eventos finalizados.</p>
                                        </div>
                                    </div>

                                    <!-- Bloque de Botones (se mantendrá a la derecha estricta en PC y horizontal en móvil) -->
                                    <div class="d-flex flex-row flex-wrap align-items-center justify-content-start justify-content-lg-end gap-2 w-100 w-lg-auto">
                                        <button class="btn btn-success rounded-2 px-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2 flex-fill flex-lg-grow-0" id="btn_emitir_masivo">
                                            <i class="fas fa-certificate fs-7"></i>
                                            <span>Emitir Masivo</span>
                                        </button>

                                        <button class="btn btn-secondary opacity-75 rounded-2 px-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2 flex-fill flex-lg-grow-0" id="btn_enviar_masivo_disabled">
                                            <i class="fas fa-paper-plane fs-7"></i>
                                            <span>Enviar Masivo</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-3 p-md-4">
                                <div class="row g-3 mb-4">
                                    <div class="col-lg-6 col-md-12">
                                        <label for="filtro_evento" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">
                                            <i class="fas fa-calendar-check me-1"></i>Filtrar por Evento Finalizado
                                        </label>
                                        <select id="filtro_evento" class="form-select form-select-luxury rounded-2 border">
                                            <option value="">-- Todos los Eventos Finalizados --</option>
                                            <?php if (!empty($eventosDisponibles) && is_array($eventosDisponibles)): ?>
                                                <?php foreach ($eventosDisponibles as $ev): ?>
                                                    <option value="<?= (int)$ev['id'] ?>">
                                                        <?= htmlspecialchars($ev['titulo']) ?> (<?= htmlspecialchars($ev['fecha_evento']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- KPIs ajustados para apilarse en móvil -->
                                <div id="contenedor_resumen_kpis" class="row g-3 mb-4 d-none">
                                    <div class="col-12 col-sm-4">
                                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                                            <div class="me-2">
                                                <span class="text-muted extra-small text-uppercase fw-bold d-block">Aptos (Asistieron)</span>
                                                <h4 class="mb-0 fw-bold text-dark" id="kpi_total_asistentes">0</h4>
                                            </div>
                                            <div class="text-primary fs-3 opacity-50 flex-shrink-0"><i class="fas fa-user-check"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                                            <div class="me-2">
                                                <span class="text-muted extra-small text-uppercase fw-bold d-block">Emitidos</span>
                                                <h4 class="mb-0 fw-bold text-success" id="kpi_total_emitidos">0</h4>
                                            </div>
                                            <div class="text-success fs-3 opacity-50 flex-shrink-0"><i class="fas fa-check-circle"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                                            <div class="me-2">
                                                <span class="text-muted extra-small text-uppercase fw-bold d-block">Pendientes</span>
                                                <h4 class="mb-0 fw-bold text-warning" id="kpi_total_pendientes">0</h4>
                                            </div>
                                            <div class="text-warning fs-3 opacity-50 flex-shrink-0"><i class="fas fa-clock"></i></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pestañas con desplazamiento horizontal en móviles -->
                                <div class="w-100 text-start mb-4 overflow-x-auto">
                                    <ul class="nav nav-tabs-luxury gap-2 gap-md-3 flex-nowrap" id="pills-tab-certificados" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-filtro active fw-semibold d-flex align-items-center gap-2 text-nowrap" data-filtro="TODOS" type="button">
                                                <i class="fas fa-list fs-7"></i><span>Todos</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-filtro fw-semibold d-flex align-items-center gap-2 text-nowrap" data-filtro="EMITIDO" type="button">
                                                <i class="fas fa-certificate fs-7"></i><span>Emitidos</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-filtro fw-semibold d-flex align-items-center gap-2 text-nowrap" data-filtro="PENDIENTE" type="button">
                                                <i class="fas fa-hourglass-half fs-7"></i><span>Pendientes</span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                <div class="table-responsive">
                                    <table id="datatable_certificados" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 200px;">Participante</th>
                                                <th style="min-width: 120px;">Documento</th>
                                                <th style="min-width: 200px;">Evento</th>
                                                <th style="min-width: 130px;">Estado Emisión</th>
                                                <th style="min-width: 140px;">Fecha Emisión</th>
                                                <th class="text-center" style="width: 80px;">Acciones</th>
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
    <script src="<?php echo BASE_URL; ?>/views/certificados/certificado.js"></script>
</body>
</html>