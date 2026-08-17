<div class="modal fade modal-luxury" id="modalEvento" tabindex="-1" aria-labelledby="modalEventoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 bg-white">
            <div class="modal-header border-bottom border-light p-4 bg-white">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="luxury-icon-box rounded-2"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <span class="luxury-subtitle">Catálogo de Eventos</span>
                            <h4 class="modal-title fw-bold fs-5 mb-0 text-dark-blue" id="modalEventoLabel"><span id="lblTituloEvento">NUEVO EVENTO</span></h4>
                        </div>
                    </div>
                    <button type="button" class="btn-close opacity-50 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="px-4 pt-3 bg-body-tertiary border-bottom border-light">
                <ul class="nav nav-tabs-luxury gap-3" id="pills-tab-form-evento" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link tab-form-evento active fw-semibold d-flex align-items-center gap-2" data-target="#panelDatosEvento" type="button">
                            <i class="fas fa-file-alt fs-7"></i><span>Datos</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link tab-form-evento fw-semibold d-flex align-items-center gap-2" data-target="#panelImagenesEvento" type="button" id="tabImagenesEvento">
                            <i class="fas fa-images fs-7"></i><span>Imágenes</span>
                        </button>
                    </li>
                </ul>
            </div>

            <form id="formEvento" autocomplete="off">
                <div class="modal-body p-4 p-md-4 bg-body-tertiary" style="max-height: 65vh; overflow-y: auto;">
                    <input type="hidden" id="id" name="id" value="0">
                    <input type="hidden" id="es_edicion" name="es_edicion" value="0">

                    <div id="avisoEdicionEvento" class="alert alert-warning border-0 rounded-3 small d-none mb-4" role="alert">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-exclamation-triangle mt-1"></i>
                            <div><strong>Modificación de evento</strong><br>Si no cuentas con permiso para aprobar eventos, este evento (incluyendo cambios de imágenes) pasará a estado <strong>PENDIENTE</strong> para una nueva revisión.</div>
                        </div>
                    </div>

                    <div id="avisoObservacionesRechazo" class="alert alert-danger border-0 rounded-3 small d-none mb-4" role="alert">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-times-circle mt-1"></i>
                            <div><strong>Este evento fue rechazado</strong><br>Motivo: <span id="textoObservacionesRechazo"></span></div>
                        </div>
                    </div>

                    <!-- PANEL DATOS -->
                    <div id="panelDatosEvento" class="panel-form-evento">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="titulo" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Título <span class="text-danger">*</span></label>
                                <input type="text" maxlength="150" class="form-control form-control-luxury rounded-2 border" id="titulo" name="titulo" placeholder="Ej: Conferencia de Tecnología 2026" required>
                            </div>
                            <div class="col-md-4">
                                <label for="tema" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Tema <span class="text-danger">*</span></label>
                                <input type="text" maxlength="100" class="form-control form-control-luxury rounded-2 border" id="tema" name="tema" placeholder="Ej: Innovación Digital" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_evento" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Fecha y Hora del Evento <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control form-control-luxury rounded-2 border" id="fecha_evento" name="fecha_evento" required>
                            </div>
                            <div class="col-md-3">
                                <label for="cupos" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Cupos <span class="text-danger">*</span></label>
                                <input type="number" min="0" step="1" class="form-control form-control-luxury rounded-2 border" id="cupos" name="cupos" value="0" required>
                            </div>
                            <div class="col-md-3">
                                <label for="precio" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Precio <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control form-control-luxury rounded-2 border" id="precio" name="precio" value="0.00" required>
                            </div>
                            <div class="col-md-12">
                                <label for="direccion" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Dirección / Lugar <span class="text-danger">*</span></label>
                                <textarea rows="2" maxlength="200" class="form-control form-control-luxury rounded-2 border" id="direccion" name="direccion" placeholder="Dirección completa del evento" required></textarea>
                            </div>
                            <div class="col-md-12">
                                <label for="descripcion" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Descripción</label>
                                <textarea rows="3" class="form-control form-control-luxury rounded-2 border" id="descripcion" name="descripcion" placeholder="Descripción u observaciones del evento"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL IMÁGENES -->
                    <div id="panelImagenesEvento" class="panel-form-evento d-none">
                        <div id="avisoGuardarPrimero" class="alert alert-info border-0 rounded-3 small d-flex align-items-start gap-2">
                            <i class="fas fa-info-circle mt-1"></i>
                            <div>Guarda primero los datos del evento para poder gestionar sus imágenes.</div>
                        </div>

                        <div id="bloqueImagenes" class="d-none">
                            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                <span class="small text-muted"><span id="contadorImagenes">0</span> / 5 imágenes activas</span>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="file" id="inputImagenNueva" accept=".jpg,.jpeg,.png,.webp" class="form-control form-control-sm" style="max-width: 220px;">
                                    <button type="button" class="btn btn-sm btn-luxury-primary rounded-2 px-3" id="btnSubirImagen">
                                        <i class="fas fa-upload fs-7 me-1"></i>Subir
                                    </button>
                                </div>
                            </div>
                            <p class="extra-small text-muted mb-2"><i class="fas fa-arrows-alt me-1"></i>Arrastra las imágenes para reordenarlas.</p>
                            <div id="gridImagenesActivas" class="row g-3 mb-4"></div>

                            <div class="border-top border-light pt-3">
                                <h6 class="fw-bold text-uppercase extra-small text-muted mb-3"><i class="fas fa-trash-alt me-1"></i>Papelera de imágenes</h6>
                                <div id="gridImagenesPapelera" class="row g-3"></div>
                                <p id="papeleraVacia" class="text-muted extra-small mb-0">La papelera está vacía.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top border-light p-3 px-4 bg-white d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-2 px-4 py-2 text-uppercase fs-7 fw-semibold" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-luxury-primary rounded-2 px-4 py-2 text-uppercase fs-7 fw-bold" id="btnGuardarEvento" form="formEvento">Guardar Evento</button>
                </div>
            </form>
        </div>
    </div>
</div>