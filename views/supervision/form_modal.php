<div class="modal fade modal-luxury" id="modalRevision" tabindex="-1" aria-labelledby="modalRevisionLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 bg-white">
            <div class="modal-header border-bottom border-light p-4 bg-white">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="luxury-icon-box rounded-2"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <span class="luxury-subtitle">Catálogo de Eventos</span>
                            <h4 class="modal-title fw-bold fs-5 mb-0 text-dark-blue" id="modalRevisionLabel"><span id="lblTituloRevision">REVISAR EVENTO</span></h4>
                        </div>
                    </div>
                    <button type="button" class="btn-close opacity-50 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-4 p-md-4 bg-body-tertiary" style="max-height: 65vh; overflow-y: auto;" id="cuerpoRevision">
                <input type="hidden" id="revisionEventoId" value="0">

                <div id="badgeEstadoRevision" class="mb-3"></div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <span class="d-block text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Dirección / Ubicación</span>
                        <span id="revLugar" class="d-block"></span>
                    </div>
                    <div class="col-md-3">
                        <span class="d-block text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Tema / Categoría</span>
                        <span id="revCategoriaTipo" class="d-block"></span>
                    </div>
                    <div class="col-md-3">
                        <span class="d-block text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Organizador</span>
                        <span id="revOrganizador" class="d-block"></span>
                    </div>
                    <div class="col-md-3">
                        <span class="d-block text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Precio Entrada</span>
                        <span id="revPrecio" class="d-block fw-bold"></span>
                    </div>
                    <div class="col-md-3">
                        <span class="d-block text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Fecha y Hora</span>
                        <span id="revFechaHora" class="d-block"></span>
                    </div>
                    <div class="col-md-3">
                        <span class="d-block text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Cupos / Aforo</span>
                        <span id="revAforo" class="d-block"></span>
                    </div>
                    <div class="col-md-3">
                        <span class="d-block text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Registrado por</span>
                        <span id="revRegistrador" class="d-block"></span>
                    </div>
                    <div class="col-md-3">
                        <span class="d-block text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Supervisor</span>
                        <span id="revSupervisor" class="d-block"></span>
                    </div>
                    <div class="col-md-12">
                        <span class="d-block text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Descripción</span>
                        <span id="revDescripcion" class="d-block"></span>
                    </div>
                    <div class="col-md-12 d-none" id="bloqueObservacionesPrevias">
                        <span class="d-block text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Observaciones registradas</span>
                        <span id="revObservaciones" class="d-block text-danger"></span>
                    </div>
                </div>

                <div class="border-top border-light pt-3 mb-3">
                    <h6 class="fw-bold text-uppercase extra-small text-muted mb-3"><i class="fas fa-images me-1"></i>Imágenes activas</h6>
                    <div id="gridRevisionActivas" class="row g-3 mb-4"></div>

                    <h6 class="fw-bold text-uppercase extra-small text-muted mb-3"><i class="fas fa-trash-alt me-1"></i>Papelera de imágenes</h6>
                    <p class="extra-small text-muted">Si apruebas el evento, las imágenes que sigan aquí se eliminarán definitivamente.</p>
                    <div id="gridRevisionPapelera" class="row g-3"></div>
                    <p id="revisionPapeleraVacia" class="text-muted extra-small mb-0">La papelera está vacía.</p>
                </div>

                <div id="bloqueRechazo" class="border-top border-light pt-3 d-none">
                    <label for="motivoRechazo" class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-2">Motivo del rechazo <span class="text-danger">*</span></label>
                    <textarea id="motivoRechazo" rows="3" maxlength="1000" class="form-control form-control-luxury rounded-2 border" placeholder="Explica por qué se rechaza este evento..."></textarea>
                </div>
            </div>

            <div class="modal-footer border-top border-light p-3 px-4 bg-white d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary rounded-2 px-4 py-2 text-uppercase fs-7 fw-semibold" data-bs-dismiss="modal">Cerrar</button>
                <div class="d-flex gap-2" id="accionesRevisionPendiente">
                    <button type="button" class="btn btn-danger rounded-2 px-4 py-2 text-uppercase fs-7 fw-bold" id="btnRechazarEvento">Rechazar</button>
                    <button type="button" class="btn btn-outline-warning rounded-2 px-4 py-2 text-uppercase fs-7 fw-bold" id="btnVolverPendiente">Volver a Pendiente</button>
                    <button type="button" class="btn btn-success rounded-2 px-4 py-2 text-uppercase fs-7 fw-bold" id="btnAprobarEvento">Aprobar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-luxury" id="modalEliminarDefinitivo" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 bg-white">
            <div class="modal-body p-4 text-center">
                <i class="fas fa-exclamation-triangle text-danger fs-1 mb-3"></i>
                <h5 class="fw-bold mb-2">¿Eliminar definitivamente?</h5>
                <p class="text-muted mb-0">Esta acción borrará el evento <strong id="tituloEliminarDefinitivo"></strong> y todas sus imágenes de forma <strong>permanente</strong>. No se puede deshacer.</p>
            </div>
            <div class="modal-footer border-top border-light p-3 px-4 bg-white d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary rounded-2 px-4 py-2 text-uppercase fs-7 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger rounded-2 px-4 py-2 text-uppercase fs-7 fw-bold" id="btnConfirmarEliminarDefinitivo">Sí, eliminar definitivamente</button>
            </div>
        </div>
    </div>
</div>