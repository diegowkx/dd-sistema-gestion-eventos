$(document).ready(function () {

    let filtroActual = 'TODOS';

    const modalCertificadoElement = document.getElementById('modalCertificado');
    const modalCertificado = modalCertificadoElement ? new bootstrap.Modal(modalCertificadoElement) : null;
    const selectFiltroEvento = document.getElementById('filtro_evento');
    
    // Botones de la Cabecera (Header)
    const btnEmitirMasivo = document.getElementById('btn_emitir_masivo');
    const btnEnviarMasivoDisabled = document.getElementById('btn_enviar_masivo_disabled');

    // DATATABLE DE CERTIFICADOS
    const tabla = $('#datatable_certificados').DataTable({
        serverSide: false,
        bDestroy: true,
        scrollX: true,
        scrollY: '55vh',
        scrollCollapse: true,

        ajax: {
            url: '?c=certificados&a=listar',
            data: function (d) {
                const evId = selectFiltroEvento ? selectFiltroEvento.value : '';
                if (evId) d.evento_id = evId;
                d.filtro = filtroActual;
            },
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) {
                    actualizarKpis(null);
                    return [];
                }
                actualizarKpis(json.resumen);
                return json.data;
            }
        },

        columns: [
            {
                data: 'nombres',
                render: function (data, type, row) {
                    const nombreCompleto = `${row.nombres || ''} ${row.apellidos || ''}`.trim();
                    return `
                        <span class="fw-bold text-dark d-block" title="${escapeAttribute(nombreCompleto)}">${escapeHtml(truncar(nombreCompleto, 35))}</span>
                        <span class="text-muted extra-small" title="${escapeAttribute(row.correo || '')}">${escapeHtml(truncar(row.correo || '', 30))}</span>
                    `;
                }
            },
            {
                data: 'documento_id',
                render: function (data) {
                    return `<span class="fw-semibold text-secondary font-monospace">${escapeHtml(data || '—')}</span>`;
                }
            },
            {
                data: 'evento_titulo',
                render: function (data) {
                    return `<span class="small text-dark fw-semibold">${escapeHtml(truncar(data || '—', 30))}</span>`;
                }
            },
            {
                data: 'certificado_emitido',
                render: function (data) {
                    const emitido = Number(data) === 1;
                    return emitido
                        ? `<span class="badge bg-success px-3 py-1-5"><i class="fas fa-check-circle me-1"></i>EMITIDO</span>`
                        : `<span class="badge bg-warning text-dark px-3 py-1-5"><i class="fas fa-clock me-1"></i>PENDIENTE</span>`;
                }
            },
            {
                data: 'fecha_emision_certificado',
                render: function (data) {
                    return data ? `<span class="small text-muted">${escapeHtml(data)}</span>` : '<span class="text-muted">—</span>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    const id = row.id;
                    const btnStyle = 'width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 0; border: none; margin: 0 2px;';
                    
                    // Ícono Previsualizar (Ojo)
                    const svgVer = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>`;
                    
                    // Ícono Enviar Email Individual (Sobre/Avión)
                    const svgEnviar = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>`;

                    return `
                        <div class="d-flex justify-content-center align-items-center">
                            <button type="button" 
                                    class="btn btn-ver-pdf rounded-circle shadow-sm me-1" 
                                    data-id="${id}" 
                                    style="${btnStyle} background-color: #0097a7;"
                                    title="Previsualizar Certificado PDF">
                                ${svgVer}
                            </button>

                            <button type="button" 
                                    class="btn btn-enviar-disabled rounded-circle shadow-sm" 
                                    data-id="${id}"
                                    style="${btnStyle} background-color: #6c757d; opacity: 0.6; cursor: not-allowed;"
                                    title="Envío individual inhabilitado - Ver README.md">
                                ${svgEnviar}
                            </button>
                        </div>
                    `;
                }
            }
        ],

        language: {
            processing: "Cargando certificados...",
            search: "Buscar participante:",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron certificados",
            emptyTable: "No hay participantes calificados en el evento seleccionado",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros"
        }
    });

    $(window).on('resize', function () {
        tabla.columns.adjust();
    });

    if (selectFiltroEvento) {
        selectFiltroEvento.addEventListener('change', function () {
            tabla.ajax.reload();
        });
    }

    document.querySelectorAll('#pills-tab-certificados .tab-filtro').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-certificados .tab-filtro').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filtroActual = this.getAttribute('data-filtro') || 'TODOS';
            tabla.ajax.reload();
        });
    });

    // ==========================================
    // 1. PREVISUALIZAR PDF EN MODAL
    // ==========================================
    document.addEventListener('click', function (e) {
        const btnVer = e.target.closest('.btn-ver-pdf');
        if (btnVer) {
            const id = btnVer.getAttribute('data-id');
            
            Swal.fire({
                title: 'Generando Previsualización',
                text: 'Procesando el PDF con Dompdf...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`?c=certificados&a=previsualizarPdf&id=${encodeURIComponent(id)}`)
                .then(res => res.json())
                .then(data => {
                    Swal.close();
                    if (data.success && data.pdf_data) {
                        const frame = document.getElementById('pdf_preview_frame');
                        if (frame) frame.src = data.pdf_data;
                        if (modalCertificado) modalCertificado.show();
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo generar el documento.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error de conexión con el servidor.', 'error'));
        }
    });

    // ==========================================
    // 2. EMITIR MASIVO (SÓLO CAMBIA ESTADO EN BD)
    // ==========================================
    if (btnEmitirMasivo) {
        btnEmitirMasivo.addEventListener('click', function () {
            const eventoId = selectFiltroEvento ? selectFiltroEvento.value : '';
            if (!eventoId) {
                Swal.fire('Atención', 'Seleccione un evento finalizado para emitir sus certificados.', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Emitir Certificados Masivamente?',
                text: 'Se actualizará el estado a EMITIDO y la fecha de emisión en la BD para todos los participantes asistentes.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Emitir Masivamente'
            }).then(result => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('evento_id', eventoId);

                Swal.fire({
                    title: 'Procesando Emisión',
                    text: 'Actualizando registros en la base de datos...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                // Apunta a la acción 'emitirMasivo' del controlador
                fetch('?c=certificados&a=emitirMasivo', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            tabla.ajax.reload(null, false);
                            Swal.fire('Completado', data.message, 'success');
                        } else {
                            Swal.fire('Atención', data.message, 'warning');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error al procesar la solicitud.', 'error'));
            });
        });
    }

    // ==========================================
    // 3. ENVIAR MASIVO POR CORREO (DESHABILITADO)
    // ==========================================
    if (btnEnviarMasivoDisabled) {
        btnEnviarMasivoDisabled.addEventListener('click', function () {
            mostrarAlertaConfiguracion('El envío masivo por correo electrónico');
        });
    }

    // ==========================================
    // 4. ENVIAR INDIVIDUAL POR CORREO (DESHABILITADO)
    // ==========================================
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-enviar-disabled')) {
            mostrarAlertaConfiguracion('El envío individual por correo electrónico');
        }
    });

    // Helper de alerta para funciones inhabilitadas
    function mostrarAlertaConfiguracion(tipoAccion) {
        Swal.fire({
            title: 'Configuración Pendiente',
            text: `${tipoAccion} está inhabilitado. Complete las configuraciones indicadas en el archivo README.md para habilitar la integración SMTP con certificados@evenrity.com.`,
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
    }

    function actualizarKpis(resumen) {
        const contenedor = document.getElementById('contenedor_resumen_kpis');
        if (!contenedor) return;
        if (!resumen) {
            contenedor.classList.add('d-none');
            return;
        }

        contenedor.classList.remove('d-none');
        document.getElementById('kpi_total_asistentes').innerText = Number(resumen.total_asistentes || 0);
        document.getElementById('kpi_total_emitidos').innerText = Number(resumen.total_emitidos || 0);
        document.getElementById('kpi_total_pendientes').innerText = Number(resumen.total_pendientes || 0);
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value;
        return div.innerHTML;
    }

    function truncar(texto, maxLength) {
        if (!texto) return '';
        return texto.length > maxLength ? texto.substring(0, maxLength) + '…' : texto;
    }

    function escapeAttribute(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
});