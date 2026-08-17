<div class="modal fade modal-luxury" id="modalRol" tabindex="-1" aria-labelledby="modalRolLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 bg-white">

            <!-- Header Limpio con Detalle Dorado/Bronce -->
            <div class="modal-header border-bottom border-light p-4 bg-white">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="luxury-icon-box rounded-2">
                            <i class="fas fa-key"></i>
                        </div>
                        <div>
                            <span class="luxury-subtitle">Configuración de Seguridad</span>
                            <h4 class="modal-title fw-bold fs-5 mb-0 text-dark-blue" id="modalRolLabel">
                                <span id="lblTituloRol">CONFIGURAR ROL</span>
                            </h4>
                        </div>
                    </div>
                    <button type="button" class="btn-close opacity-50 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <form id="formRol" autocomplete="off">
                <div class="modal-body p-4 p-md-4 bg-body-tertiary">
                    <input type="hidden" id="rol_id_hidden" name="id" value="">
                    <input type="hidden" id="es_edicion" name="es_edicion" value="0">

                    <!-- Input Elegante -->
                    <div class="mb-4">
                        <label for="nombre_rol" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">
                            Nombre del Rol <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-luxury fw-semibold text-uppercase rounded-2 border" 
                               id="nombre_rol" 
                               name="nombre" 
                               placeholder="EJ. DIRECTOR INMOBILIARIO" 
                               maxlength="50" 
                               required>
                    </div>

                    <!-- Panel de Selección de Permisos -->
                    <div class="permisos-wrapper rounded-3 p-4 bg-white border border-light">
                        <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 mb-3 border-bottom border-light gap-2">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark-blue text-uppercase fs-7 tracking-wide">
                                    Módulos & Permisos Asignados
                                </h6>
                                <p class="text-muted extra-small mb-0">Habilite las áreas de trabajo autorizadas para este perfil.</p>
                            </div>
                            <span class="badge badge-luxury-gold px-3 py-2 fw-semibold" id="lblCantPermisos">
                                0 seleccionados
                            </span>
                        </div>

                        <div class="row g-2 overflow-auto custom-scrollbar pe-1" id="rowPermisos" style="max-height: 310px;">
                            <?php if (!empty($permisosDisponibles)): ?>
                                <?php foreach ($permisosDisponibles as $permiso): ?>
                                    <div class="col-md-6">
                                        <label class="permiso-card-luxury w-100 d-flex align-items-center justify-content-between p-3 rounded-2 cursor-pointer transition-all" for="permiso_<?php echo htmlspecialchars($permiso['nombre']); ?>">
                                            <div class="d-flex align-items-center gap-3 pe-2">
                                                <div class="permiso-icon-wrapper rounded-2 d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-layer-group fs-6"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-uppercase text-dark-blue fs-7">
                                                        <?php echo htmlspecialchars($permiso['nombre']); ?>
                                                    </span>
                                                    <?php if (!empty($permiso['descripcion'])): ?>
                                                        <small class="text-muted extra-small d-block text-truncate-2 mt-0-5">
                                                            <?php echo htmlspecialchars($permiso['descripcion']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="form-check m-0 p-0 me-1">
                                                <input class="form-check-input chk-permiso chk-permiso-luxury rounded-1" 
                                                       type="checkbox" 
                                                       name="permisos[]" 
                                                       value="<?php echo htmlspecialchars($permiso['nombre']); ?>" 
                                                       id="permiso_<?php echo htmlspecialchars($permiso['nombre']); ?>">
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center text-muted py-5">
                                    <i class="fas fa-folder-open fs-3 mb-2 opacity-30"></i>
                                    <p class="small mb-0">No hay módulos registrados en el sistema.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Footer Elegante -->
                <div class="modal-footer border-top border-light p-3 px-4 bg-white d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-2 px-4 py-2 text-uppercase fs-7 fw-semibold" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-luxury-primary rounded-2 px-4 py-2 text-uppercase fs-7 fw-bold" id="btnGuardarRol" form="formRol">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>