<div class="header font-primary-family">
    
    <div class="header-content d-flex align-items-center justify-content-between px-4">
        <!-- Contenedor exclusivo para la decoración con overflow hidden -->
        <div class="header-decorations"></div>
        
        <!-- Botón para alternar / colapsar Sidebar -->
        <div class="header-left d-flex align-items-center">
            <button type="button" id="nav_toggle" class="btn btn-link text-dark p-0 me-3 shadow-none border-0">
                <i class="fas fa-bars fs-4"></i>
            </button>
        </div>

        <!-- Perfil / Dropdown de Usuario -->
        <div class="header-right">
            <div class="dropdown">
                <button class="btn btn-link text-dark p-0 dropdown-toggle d-flex align-items-center text-decoration-none shadow-none border-0" 
                        type="button" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle fs-3 text-secondary me-2"></i>
                    <span class="fw-bold d-none d-md-inline text-slate-700">
                        <?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 rounded-4 p-2" aria-labelledby="dropdownUser" style="min-width: 200px;">
                    <li>
                        <a class="dropdown-item py-2 px-3 rounded-3 d-flex align-items-center fw-semibold text-slate-700 transition-all" href="javascript:void(0)" id="btn-abrir-perfil">
                            <i class="fas fa-user me-2 text-primary-system fs-6"></i> Mi Perfil
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-2 border-slate-100"></li>
                    <li>
                        <a class="dropdown-item py-2 px-3 rounded-3 d-flex align-items-center fw-semibold text-danger transition-all hover-danger" href="?c=auth&a=logout">
                            <i class="fas fa-sign-out-alt me-2 fs-6"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>

<!-- Profile Modal -->
<?php include_once __DIR__ . '/../profile/profile_modal.php'; ?>