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
                            
                            <!-- ENCABEZADO FORMAL Y FINO -->
                            <div class="card-header-luxury p-4 border-bottom border-light">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 w-100">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="luxury-icon-box rounded-2">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <span class="luxury-subtitle">Módulo de Seguridad</span>
                                            <h2 class="card-title mb-0 fw-bold fs-4 text-dark-blue">Gestión de Usuarios</h2>
                                            <p class="text-muted extra-small mb-0">Administra el personal del sistema y sus perfiles de acceso.</p>
                                        </div>
                                    </div>
                                    
                                    <button type="button" class="btn btn-luxury-primary rounded-2 px-4 py-2-5 fw-semibold d-inline-flex align-items-center gap-2 btn-nuevo-usuario">
                                        <i class="fas fa-plus fs-7"></i>
                                        <span>Nuevo Usuario</span>
                                    </button>
                                </div>
                            </div>

                            <!-- PESTAÑAS DE NAVEGACIÓN Y TABLA -->
                            <div class="card-body p-4">
                                <div class="w-100 text-start mb-4">
                                    <ul class="nav nav-tabs-luxury gap-3" id="pills-tab-usuarios" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-estado active fw-semibold d-flex align-items-center gap-2" data-estado="ACTIVO" type="button">
                                                <i class="fas fa-user-check fs-7"></i>
                                                <span>Usuarios Activos</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link tab-estado fw-semibold d-flex align-items-center gap-2" data-estado="INACTIVO" type="button">
                                                <i class="fas fa-user-slash fs-7"></i>
                                                <span>Usuarios Inactivos</span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                <div class="table-responsive">
                                    <table id="datatable_usuarios" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th>Documento</th>
                                                <th>Nombres y Apellidos</th>
                                                <th>Usuario</th>
                                                <th>Rol</th>
                                                <th>Correo</th>
                                                <th>Estado</th>
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
    
    <script src="<?php echo BASE_URL; ?>/views/usuarios/usuario.js"></script>
</body>
</html>