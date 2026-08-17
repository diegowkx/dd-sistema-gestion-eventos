<!-- Modal Flotante de Perfil -->
<div class="modal fade modal-creative font-secondary-family" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-5 overflow-hidden bg-white">
            
            <div class="modal-header border-0 p-4 bg-gradient-primary text-white position-relative overflow-hidden">
                <div class="position-relative z-1 d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="modal-icon-badge rounded-circle bg-white-10 p-3 d-flex align-items-center justify-content-center backdrop-blur">
                            <i class="fas fa-user-cog fs-4 text-white"></i>
                        </div>
                        <div>
                            <span class="badge bg-20 rounded-pill px-3 py-1 mb-1 extra-small text-uppercase tracking-wider"style="color:black">Configuración de Cuenta</span>
                            <h4 class="modal-title fw-bold fs-5 mb-0" id="profileModalLabel" style="color:black">
                                MI PERFIL
                            </h4>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white opacity-75 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Círculos decorativos heredados de los componentes -->
                <div class="header-bg-circle-1"></div>
                <div class="header-bg-circle-2"></div>
            </div>

            <form id="profileForm" autocomplete="off">
                <div class="modal-body p-4 p-md-5 bg-slate-50">
                    <div class="row g-4">
                        
                        <!-- Campos de Solo Lectura -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" id="prof_documento_id" class="form-control form-control-creative bg-slate-100 fw-bold rounded-4 border-0 shadow-sm ps-4 text-muted" readonly disabled placeholder="N° Documento">
                                <label for="prof_documento_id" class="ps-4 text-muted fw-medium">N° Documento</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" id="prof_username" class="form-control form-control-creative bg-slate-100 fw-bold rounded-4 border-0 shadow-sm ps-4 text-muted" readonly disabled placeholder="Usuario">
                                <label for="prof_username" class="ps-4 text-muted fw-medium">Usuario</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" id="prof_nombres" class="form-control form-control-creative bg-slate-100 fw-bold rounded-4 border-0 shadow-sm ps-4 text-muted" readonly disabled placeholder="Nombres">
                                <label for="prof_nombres" class="ps-4 text-muted fw-medium">Nombres</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" id="prof_apellidos" class="form-control form-control-creative bg-slate-100 fw-bold rounded-4 border-0 shadow-sm ps-4 text-muted" readonly disabled placeholder="Apellidos">
                                <label for="prof_apellidos" class="ps-4 text-muted fw-medium">Apellidos</label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="text" id="prof_rol_name" class="form-control form-control-creative bg-slate-100 fw-bold rounded-4 border-0 shadow-sm ps-4 text-muted" readonly disabled placeholder="Rol">
                                <label for="prof_rol_name" class="ps-4 text-muted fw-medium">Rol Asignado</label>
                            </div>
                        </div>

                        <div class="col-12"><hr class="my-1 border-slate-100"></div>

                        <!-- Campos Editables -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" id="prof_correo" name="correo" class="form-control form-control-creative rounded-4 border-0 shadow-sm ps-4" required placeholder="correo@ejemplo.com">
                                <label for="prof_correo" class="ps-4 text-muted fw-medium">Correo Electrónico <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" id="prof_telefono" name="telefono" class="form-control form-control-creative rounded-4 border-0 shadow-sm ps-4" maxlength="15" inputmode="numeric" placeholder="Teléfono" autocomplete="tel">
                                <label for="prof_telefono" class="ps-4 text-muted fw-medium">Teléfono / Celular</label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-floating position-relative">
                                <input type="password" id="prof_password" name="password" class="form-control form-control-creative rounded-4 border-0 shadow-sm ps-4 pe-5" placeholder="Dejar en blanco para mantener la actual" autocomplete="new-password">
                                <label for="prof_password" class="ps-4 text-muted fw-medium">Nueva Contraseña</label>
                                <button class="btn position-absolute top-50 end-0 translate-middle-y me-3 border-0 bg-transparent text-secondary z-3 shadow-none" type="button" id="toggleProfPassword" style="height: auto;">
                                    <i class="fas fa-eye fs-5" id="toggleProfIcon"></i>
                                </button>
                            </div>
                            
                            <!-- Medidor de fortaleza -->
                            <div class="progress mt-3 rounded-pill bg-slate-200" style="height: 6px;">
                                <div id="prof-password-strength-bar" class="progress-bar rounded-pill transition-all" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <small id="prof-password-strength-text" class="form-text mt-2 d-block text-muted fw-medium extra-small">
                                Si se deja en blanco, se conservará la contraseña actual.
                            </small>
                        </div>

                    </div>
                </div>

                <!-- Footer Flotante con botones del sistema -->
                <div class="modal-footer border-0 p-4 bg-white d-flex justify-content-between align-items-center shadow-sm">
                    <button type="button" class="btn btn-light-creative rounded-pill px-4 py-2-5 text-slate-600 fw-semibold" data-bs-dismiss="modal">
                        <i class="fas fa-xmark me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-gradient-primary rounded-pill px-5 py-2-5 text-white fw-bold shadow-glow">
                        <i class="fas fa-check-circle me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>