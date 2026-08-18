$(document).ready(function () {

    let filtroActual = 'TODOS';

    const modalAsistenciaElement = document.getElementById('modalAsistencia');
    const modalAsistencia = modalAsistenciaElement
        ? new bootstrap.Modal(modalAsistenciaElement)
        : null;

    const selectFiltroEvento = document.getElementById('filtro_evento');
    const inputCheckinDoc = document.getElementById('input_checkin_doc');
    const btnCheckinRapido = document.getElementById('btn_checkin_rapido');
    const btnGuardarAsistencia = document.getElementById('btnGuardarAsistencia');
    const checkAsistenciaModal = document.getElementById('modal_check_asistencia');

    // ==========================================
    // DATATABLE DE ASISTENCIAS
    // ==========================================
    const tabla = $('#datatable_asistencias').DataTable({
        serverSide: false,
        bDestroy: true,
        scrollX: true,
        scrollY: '55vh',
        scrollCollapse: true,

        ajax: {
            url: '?c=asistencias&a=listar',
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
                    const nombreCorto = truncar(nombreCompleto, 35);
                    const correoCorto = truncar(row.correo || '', 30);
                    return `
                        <span class="fw-bold text-dark d-block" title="${escapeAttribute(nombreCompleto)}">${escapeHtml(nombreCorto)}</span>
                        <span class="text-muted extra-small" title="${escapeAttribute(row.correo || '')}">${escapeHtml(correoCorto)}</span>
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
                data: 'telefono',
                render: function (data) {
                    return data ? escapeHtml(data) : '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'asistencia',
                render: function (data, type, row) {
                    const asistio = Number(data) === 1;
                    if (asistio) {
                        const fecha = row.fecha_asistencia ? escapeHtml(row.fecha_asistencia) : '';
                        const operador = row.operador_marcado_username ? `por ${escapeHtml(row.operador_marcado_username)}` : '';
                        return `
                            <div>
                                <span class="badge bg-success px-3 py-1-5"><i class="fas fa-check-circle me-1"></i>ASISTIÓ</span>
                                ${fecha ? `<span class="text-muted extra-small d-block mt-1">${fecha}</span>` : ''}
                                ${operador ? `<span class="text-muted extra-small d-block font-italic">${operador}</span>` : ''}
                            </div>
                        `;
                    }
                    return `<span class="badge bg-warning text-dark px-3 py-1-5"><i class="fas fa-clock me-1"></i>PENDIENTE</span>`;
                }
            },
            {
                data: 'certificado_emitido',
                render: function (data, type, row) {
                    const emitido = Number(data) === 1;
                    if (emitido) {
                        const fechaCert = row.fecha_emision_certificado ? escapeHtml(row.fecha_emision_certificado) : '';
                        return `
                            <div>
                                <span class="badge bg-info text-dark px-3 py-1-5"><i class="fas fa-certificate me-1"></i>EMITIDO</span>
                                ${fechaCert ? `<span class="text-muted extra-small d-block mt-1">${fechaCert}</span>` : ''}
                            </div>
                        `;
                    }
                    return `<span class="badge bg-light text-muted border px-2-5 py-1">NO EMITIDO</span>`;
                }
            },
            {
                data: 'created_at',
                render: function (data) {
                    if (!data) return '<span class="text-muted">—</span>';
                    return `<span class="small text-muted">${escapeHtml(data)}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    const rowData = row || data || {};
                    const id = rowData.id;
                    const nombreCompleto = `${rowData.nombres || ''} ${rowData.apellidos || ''}`.trim();
                    const asistio = Number(rowData.asistencia) === 1;
                    const certEmitido = Number(rowData.certificado_emitido) === 1;

                    // Estilo base unificado para garantir alineación exacta en todos los botones
                    const btnStyle = 'width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; padding: 0; border: none;';

                    // SVGs vectoriales integrados con tamaño estricto
                    const svgCheck = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;
                    const svgUndo = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>`;
                    const svgLock = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`;
                    const svgVer = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>`;

                    let botones = '';

                    if (!asistio) {
                        botones += `
                            <button type="button" 
                                    class="btn btn-success btn-marcar-rapido rounded-circle shadow-sm" 
                                    data-id="${id}" 
                                    data-nombre="${escapeAttribute(nombreCompleto)}" 
                                    style="${btnStyle}"
                                    title="Marcar Asistencia">
                                ${svgCheck}
                            </button>
                        `;
                    } else if (!certEmitido) {
                        botones += `
                            <button type="button" 
                                    class="btn btn-danger btn-desmarcar-rapido rounded-circle shadow-sm" 
                                    data-id="${id}" 
                                    data-nombre="${escapeAttribute(nombreCompleto)}" 
                                    style="${btnStyle}"
                                    title="Desmarcar Asistencia">
                                ${svgUndo}
                            </button>
                        `;
                    } else {
                        botones += `
                            <button type="button" 
                                    class="btn btn-secondary btn-bloqueado rounded-circle shadow-sm" 
                                    data-nombre="${escapeAttribute(nombreCompleto)}" 
                                    style="${btnStyle} opacity: 0.8;" 
                                    title="Certificado ya emitido">
                                ${svgLock}
                            </button>
                        `;
                    }

                    // Botón Ver Detalle en Cian Intenso (#0097a7)
                    botones += `
                        <button type="button" 
                                class="btn btn-ver-detalle rounded-circle shadow-sm" 
                                data-id="${id}" 
                                style="${btnStyle} background-color: #0097a7;"
                                title="Ver Detalle y Asistencia">
                            ${svgVer}
                        </button>
                    `;

                    return `<div class="d-flex align-items-center justify-content-center gap-2">${botones}</div>`;
                }
            }
        ],

        language: {
            processing: "Procesando participantes...",
            search: "Buscar participante:",
            lengthMenu: "Mostrar _MENU_ participantes",
            zeroRecords: "No se encontraron inscripciones registradas",
            emptyTable: "No hay participantes inscritos para los criterios seleccionados",
            loadingRecords: "Cargando datos...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ participantes",
            infoEmpty: "Mostrando 0 a 0 de 0 participantes",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    $(window).on('resize', function () {
        tabla.columns.adjust();
    });

    // ==========================================
    // CAMBIO DE SELECTOR DE EVENTO
    // ==========================================
    if (selectFiltroEvento) {
        selectFiltroEvento.addEventListener('change', function () {
            tabla.ajax.reload();
        });
    }

    // ==========================================
    // PESTAÑAS DE FILTRO (TODOS / ASISTIO / PENDIENTE)
    // ==========================================
    document.querySelectorAll('#pills-tab-asistencias .tab-filtro').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-asistencias .tab-filtro').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filtroActual = this.getAttribute('data-filtro') || 'TODOS';
            tabla.ajax.reload();
        });
    });

    // ==========================================
    // CHECK-IN RÁPIDO POR DOCUMENTO / DNI
    // ==========================================
    function procesarCheckinRapido() {
        const doc = inputCheckinDoc ? inputCheckinDoc.value.trim() : '';
        const eventoId = selectFiltroEvento ? selectFiltroEvento.value : '';

        if (!eventoId) {
            Swal.fire('Atención', 'Debe seleccionar un evento primero para realizar el check-in.', 'warning');
            return;
        }

        if (!doc) {
            Swal.fire('Atención', 'Ingrese o escanee el número de Documento / DNI.', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('evento_id', eventoId);
        formData.append('documento_id', doc);

        fetch('?c=asistencias&a=marcarPorDocumento', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Asistencia Confirmada!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    if (inputCheckinDoc) {
                        inputCheckinDoc.value = '';
                        inputCheckinDoc.focus();
                    }
                    tabla.ajax.reload(null, false);
                } else {
                    Swal.fire('No se pudo registrar', data.message || 'Error al procesar el check-in.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Error de conexión con el servidor.', 'error'));
    }

    if (btnCheckinRapido) {
        btnCheckinRapido.addEventListener('click', procesarCheckinRapido);
    }

    if (inputCheckinDoc) {
        inputCheckinDoc.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                procesarCheckinRapido();
            }
        });
    }

    // ==========================================
    // CLIC EN VER DETALLE / MODAL DE ASISTENCIA
    // ==========================================
    document.addEventListener('click', function (e) {

        // VER DETALLE EN MODAL
        const btnVer = e.target.closest('.btn-ver-detalle');
        if (btnVer) {
            const id = btnVer.getAttribute('data-id');
            abrirModalDetalle(id);
            return;
        }

        // MARCAR ASISTENCIA RÁPIDA DESDE LA TABLA
        const btnMarcar = e.target.closest('.btn-marcar-rapido');
        if (btnMarcar) {
            const id = btnMarcar.getAttribute('data-id');
            const nombre = btnMarcar.getAttribute('data-nombre');

            Swal.fire({
                title: '¿Confirmar Asistencia?',
                html: `¿Deseas marcar la asistencia de <strong>${escapeHtml(nombre)}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Marcar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('id', id);

                fetch('?c=asistencias&a=marcar', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            tabla.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'Registrado',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo registrar la asistencia.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error de conexión con el servidor.', 'error'));
            });
            return;
        }

        // DESMARCAR ASISTENCIA RÁPIDA DESDE LA TABLA
        const btnDesmarcar = e.target.closest('.btn-desmarcar-rapido');
        if (btnDesmarcar) {
            const id = btnDesmarcar.getAttribute('data-id');
            const nombre = btnDesmarcar.getAttribute('data-nombre');

            Swal.fire({
                title: '¿Desmarcar Asistencia?',
                html: `¿Deseas anular la asistencia registrada para <strong>${escapeHtml(nombre)}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Desmarcar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('id', id);

                fetch('?c=asistencias&a=desmarcar', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            tabla.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'Desmarcado',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo desmarcar la asistencia.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error de conexión con el servidor.', 'error'));
            });
        }

        // AVISO EN SWEETALERT AL HACER CLIC EN EL CANDADO
        const btnBloqueado = e.target.closest('.btn-bloqueado');
        if (btnBloqueado) {
            const nombre = btnBloqueado.getAttribute('data-nombre');

            Swal.fire({
                icon: 'info',
                title: 'Asistencia Bloqueada',
                html: `La asistencia de <strong>${escapeHtml(nombre)}</strong> no se puede modificar porque su <strong>certificado digital ya fue emitido</strong>.`,
                confirmButtonColor: '#0097a7',
                confirmButtonText: 'Entendido'
            });
            return;
        }
    });

    // ==========================================
    // CARGAR Y ABRIR MODAL DE DETALLE
    // ==========================================
    function abrirModalDetalle(id) {
        if (!modalAsistencia) return;

        fetch(`?c=asistencias&a=obtener&id=${encodeURIComponent(id)}`)
            .then(res => res.json())
            .then(result => {
                if (!result.success || !result.data) {
                    Swal.fire('Error', result.message || 'No se encontró el registro del participante.', 'error');
                    return;
                }

                const d = result.data;
                const asistio = Number(d.asistencia) === 1;
                const certEmitido = Number(d.certificado_emitido) === 1;

                document.getElementById('asistencia_id').value = d.id;
                document.getElementById('asistencia_estado_inicial').value = asistio ? '1' : '0';
                document.getElementById('asistencia_cert_emitido').value = certEmitido ? '1' : '0';

                document.getElementById('modal_txt_documento').innerText = d.documento_id || '—';
                document.getElementById('modal_txt_nombre').innerText = `${d.nombres || ''} ${d.apellidos || ''}`.trim() || '—';
                document.getElementById('modal_txt_correo').innerText = d.correo || '—';
                document.getElementById('modal_txt_telefono').innerText = d.telefono || '—';
                document.getElementById('modal_txt_created_at').innerText = d.created_at || '—';
                document.getElementById('modal_txt_evento_id').innerText = d.evento_id ? `#${d.evento_id}` : '—';

                const bloqueInfoMarcado = document.getElementById('bloqueInfoMarcado');
                if (asistio) {
                    document.getElementById('modal_txt_fecha_asistencia').innerText = d.fecha_asistencia || '—';
                    document.getElementById('modal_txt_operador').innerText = d.operador_marcado_username || 'Sistema';
                    if (bloqueInfoMarcado) bloqueInfoMarcado.classList.remove('d-none');
                } else {
                    if (bloqueInfoMarcado) bloqueInfoMarcado.classList.add('d-none');
                }

                const avisoCert = document.getElementById('avisoCertificadoBloqueado');
                const lblCheck = document.getElementById('lbl_estado_check');

                if (checkAsistenciaModal) {
                    checkAsistenciaModal.checked = asistio;

                    if (certEmitido) {
                        checkAsistenciaModal.disabled = true;
                        if (avisoCert) avisoCert.classList.remove('d-none');
                        if (lblCheck) lblCheck.innerText = 'Asistencia bloqueada: Certificado ya emitido.';
                        if (btnGuardarAsistencia) btnGuardarAsistencia.disabled = true;
                    } else {
                        checkAsistenciaModal.disabled = false;
                        if (avisoCert) avisoCert.classList.add('d-none');
                        if (lblCheck) lblCheck.innerText = 'Activa o desactiva la casilla para cambiar el estado de asistencia.';
                        if (btnGuardarAsistencia) btnGuardarAsistencia.disabled = false;
                    }
                }

                modalAsistencia.show();
            })
            .catch(() => Swal.fire('Error', 'Error de conexión al obtener el detalle del participante.', 'error'));
    }

    // ==========================================
    // CONFIRMAR ASISTENCIA DESDE EL MODAL
    // ==========================================
    if (btnGuardarAsistencia) {
        btnGuardarAsistencia.addEventListener('click', function () {
            const id = document.getElementById('asistencia_id').value;
            const estadoInicial = document.getElementById('asistencia_estado_inicial').value === '1';
            const estadoNuevo = checkAsistenciaModal ? checkAsistenciaModal.checked : false;
            const certEmitido = document.getElementById('asistencia_cert_emitido').value === '1';

            if (certEmitido && !estadoNuevo) {
                Swal.fire('No permitido', 'No se puede desmarcar la asistencia porque el certificado ya fue emitido.', 'warning');
                return;
            }

            // Si no hubo cambio
            if (estadoInicial === estadoNuevo) {
                modalAsistencia.hide();
                return;
            }

            const endpoint = estadoNuevo ? '?c=asistencias&a=marcar' : '?c=asistencias&a=desmarcar';
            const formData = new FormData();
            formData.append('id', id);

            btnGuardarAsistencia.disabled = true;

            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    btnGuardarAsistencia.disabled = false;
                    if (data.success) {
                        modalAsistencia.hide();
                        tabla.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Actualizado',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo actualizar la asistencia.', 'error');
                    }
                })
                .catch(() => {
                    btnGuardarAsistencia.disabled = false;
                    Swal.fire('Error', 'Error de conexión al actualizar.', 'error');
                });
        });
    }

    // ==========================================
    // ACTUALIZACIÓN DE RESUMEN KPI
    // ==========================================
    function actualizarKpis(resumen) {
        const contenedor = document.getElementById('contenedor_resumen_kpis');
        if (!contenedor) return;

        if (!resumen) {
            contenedor.classList.add('d-none');
            return;
        }

        contenedor.classList.remove('d-none');
        const kpiInscritos = document.getElementById('kpi_total_inscritos');
        const kpiAsistieron = document.getElementById('kpi_total_asistieron');
        const kpiPendientes = document.getElementById('kpi_total_pendientes');
        const kpiCertificados = document.getElementById('kpi_total_certificados');

        if (kpiInscritos) kpiInscritos.innerText = Number(resumen.total_inscritos || 0);
        if (kpiAsistieron) {
            const porc = resumen.porcentaje_asistencia !== undefined ? ` (${resumen.porcentaje_asistencia}%)` : '';
            kpiAsistieron.innerText = `${Number(resumen.total_asistieron || 0)}${porc}`;
        }
        if (kpiPendientes) kpiPendientes.innerText = Number(resumen.total_pendientes || 0);
        if (kpiCertificados) kpiCertificados.innerText = Number(resumen.total_certificados || 0);
    }

    // ==========================================
    // HELPERS
    // ==========================================
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