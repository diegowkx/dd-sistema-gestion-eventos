<div class="modal fade modal-luxury" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 bg-white">

            <!-- Header Limpio con Detalle Dorado/Bronce -->
            <div class="modal-header border-bottom border-light p-4 bg-white">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="luxury-icon-box rounded-2">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <span class="luxury-subtitle">Gestión de Personal</span>
                            <h4 class="modal-title fw-bold fs-5 mb-0 text-dark-blue" id="modalUsuarioLabel">
                                <span id="lblTituloUsuario">NUEVO USUARIO</span>
                            </h4>
                        </div>
                    </div>
                    <button type="button" class="btn-close opacity-50 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <form id="formUsuario" autocomplete="off">
                <div class="modal-body p-4 p-md-4 bg-body-tertiary">
                    <input type="hidden" id="es_edicion" name="es_edicion" value="0">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="documento_id" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Documento (DNI/CE) <span class="text-danger">*</span></label>
                            <input type="text" maxlength="15" class="form-control form-control-luxury rounded-2 border" id="documento_id" name="documento_id" placeholder="Ingrese documento" required>
                        </div>

                        <div class="col-md-6">
                            <label for="rol_name" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Rol <span class="text-danger">*</span></label>
                            <select class="form-select form-control-luxury rounded-2 border" id="rol_name" name="rol_name" required>
                                <option value="">-- Seleccionar --</option>
                                <?php if (!empty($rolesDisponibles)): foreach ($rolesDisponibles as $rol): ?>
                                    <option value="<?php echo htmlspecialchars($rol['nombre']); ?>"><?php echo htmlspecialchars($rol['nombre']); ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="username" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-luxury rounded-2 border" id="username" name="username" placeholder="Nombre de usuario" autocomplete="username">
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2" id="reqPasswordLabel">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" maxlength="70" class="form-control form-control-luxury rounded-2 border" id="password" name="password" placeholder="••••••••" autocomplete="new-password">
                        </div>

                        <div class="col-md-6">
                            <label for="nombres" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Nombres <span class="text-danger">*</span></label>
                            <input type="text" maxlength="100" class="form-control form-control-luxury rounded-2 border" id="nombres" name="nombres" placeholder="Nombres" required>
                        </div>

                        <div class="col-md-6">
                            <label for="apellidos" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" maxlength="100" class="form-control form-control-luxury rounded-2 border" id="apellidos" name="apellidos" placeholder="Apellidos" required>
                        </div>

                        <div class="col-md-12">
                            <label for="correo" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" maxlength="100" class="form-control form-control-luxury rounded-2 border" id="correo" name="correo" placeholder="correo@ejemplo.com" required>
                        </div>
                    </div>
                </div>

                <!-- Footer Elegante -->
                <div class="modal-footer border-top border-light p-3 px-4 bg-white d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-2 px-4 py-2 text-uppercase fs-7 fw-semibold" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-luxury-primary rounded-2 px-4 py-2 text-uppercase fs-7 fw-bold" id="btnGuardarUsuario" form="formUsuario">
                        Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>