<div class="modal fade modal-luxury" id="modalAsistencia" tabindex="-1" aria-labelledby="modalAsistenciaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 bg-white">
            <div class="modal-header border-bottom border-light p-4 bg-white">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="luxury-icon-box rounded-2"><i class="fas fa-user-check"></i></div>
                        <div>
                            <span class="luxury-subtitle">Control de Asistencias</span>
                            <h4 class="modal-title fw-bold fs-5 mb-0 text-dark-blue" id="modalAsistenciaLabel">Detalle del Participante</h4>
                        </div>
                    </div>
                    <button type="button" class="btn-close opacity-50 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <form id="formAsistencia" autocomplete="off">
                <div class="modal-body p-4 bg-body-tertiary">
                    <input type="hidden" id="asistencia_id" name="id" value="0">
                    <input type="hidden" id="asistencia_estado_inicial" value="0">
                    <input type="hidden" id="asistencia_cert_emitido" value="0">

                    <!-- AVISO DE CERTIFICADO EMITIDO -->
                    <div id="avisoCertificadoBloqueado" class="alert alert-info border-0 rounded-3 small d-none mb-3" role="alert">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-certificate mt-1 fs-6"></i>
                            <div>
                                <strong>Certificado Emitido</strong><br>
                                Este participante ya cuenta con un certificado emitido. La asistencia queda confirmada y no puede ser revertida.
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA DE DETALLE DEL PARTICIPANTE -->
                    <div class="card p-4 border rounded-3 bg-white shadow-sm mb-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Documento / DNI</label>
                                <div class="form-control bg-light border-0 fw-semibold text-dark" id="modal_txt_documento">—</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Nombres y Apellidos</label>
                                <div class="form-control bg-light border-0 fw-semibold text-dark" id="modal_txt_nombre">—</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Correo Electrónico</label>
                                <div class="form-control bg-light border-0 text-dark" id="modal_txt_correo">—</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Teléfono / Celular</label>
                                <div class="form-control bg-light border-0 text-dark" id="modal_txt_telefono">—</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">Fecha de Registro</label>
                                <div class="form-control bg-light border-0 text-muted small" id="modal_txt_created_at">—</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-uppercase text-muted fw-bold extra-small tracking-wider mb-1">ID Evento</label>
                                <div class="form-control bg-light border-0 text-muted small" id="modal_txt_evento_id">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- INFO AUDITORÍA DE MARCADO -->
                    <div id="bloqueInfoMarcado" class="card p-3 border rounded-3 bg-light-subtle shadow-sm mb-3 d-none">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-success fs-4"><i class="fas fa-check-circle"></i></div>
                            <div class="small">
                                <span class="d-block fw-bold text-dark">Asistencia registrada previamente</span>
                                <span class="text-muted">Fecha: <strong id="modal_txt_fecha_asistencia">—</strong> | Operador: <strong id="modal_txt_operador">—</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- INTERRUPTOR / CHECK DE ASISTENCIA -->
                    <div class="card p-4 border rounded-3 bg-white shadow-sm">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <label class="form-check-label fw-bold text-dark fs-6 d-block mb-1" for="modal_check_asistencia">
                                    Marcar Asistencia
                                </label>
                                <span class="text-muted extra-small" id="lbl_estado_check">
                                    Activa la casilla para registrar la asistencia del participante en el evento.
                                </span>
                            </div>
                            <div class="form-check form-switch m-0 p-0">
                                <input class="form-check-input ms-3 fs-3" type="checkbox" role="switch" id="modal_check_asistencia" style="cursor: pointer;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top border-light p-3 bg-white justify-content-between">
                    <button type="button" class="btn btn-light rounded-2 px-4 py-2 fw-semibold" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-luxury-primary rounded-2 px-4 py-2-5 fw-semibold d-inline-flex align-items-center gap-2" id="btnGuardarAsistencia">
                        <i class="fas fa-save fs-7"></i><span>Confirmar Asistencia</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
