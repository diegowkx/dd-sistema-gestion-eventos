<div class="modal fade modal-luxury" id="modalCertificado" tabindex="-1" aria-labelledby="modalCertificadoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 bg-white">
            <div class="modal-header border-bottom border-light p-4 bg-white">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="luxury-icon-box rounded-2"><i class="fas fa-file-pdf"></i></div>
                        <div>
                            <span class="luxury-subtitle">Previsualización de Documento</span>
                            <h4 class="modal-title fw-bold fs-5 mb-0 text-dark-blue" id="modalCertificadoLabel">Vista Previa del Certificado</h4>
                        </div>
                    </div>
                    <button type="button" class="btn-close opacity-50 opacity-100-hover" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-0 bg-dark" style="height: 75vh;">
                <iframe id="pdf_preview_frame" style="width: 100%; height: 100%; border: none;" src="about:blank"></iframe>
            </div>

            <div class="modal-footer border-top border-light p-3 bg-white justify-content-end">
                <button type="button" class="btn btn-secondary rounded-2 px-4 py-2 fw-semibold" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>