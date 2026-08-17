$(document).ready(function () {

    let filtroActual = 'PENDIENTE';
    let idParaEliminarDefinitivo = null;
    let modoConfirmandoRechazo = false;

    const modalRevisionElement = document.getElementById('modalRevision');
    const modalRevision = modalRevisionElement ? new bootstrap.Modal(modalRevisionElement) : null;

    const modalEliminarDefElement = document.getElementById('modalEliminarDefinitivo');
    const modalEliminarDef = modalEliminarDefElement ? new bootstrap.Modal(modalEliminarDefElement) : null;

    const lblTituloRevision = document.getElementById('lblTituloRevision');
    const badgeEstadoRevision = document.getElementById('badgeEstadoRevision');
    const bloqueObservacionesPrevias = document.getElementById('bloqueObservacionesPrevias');
    const accionesRevisionPendiente = document.getElementById('accionesRevisionPendiente');
    const bloqueRechazo = document.getElementById('bloqueRechazo');
    const motivoRechazo = document.getElementById('motivoRechazo');
    const btnAprobarEvento = document.getElementById('btnAprobarEvento');
    const btnRechazarEvento = document.getElementById('btnRechazarEvento');
    const btnVolverPendiente = document.getElementById('btnVolverPendiente');
    const gridRevisionActivas = document.getElementById('gridRevisionActivas');
    const gridRevisionPapelera = document.getElementById('gridRevisionPapelera');
    const revisionPapeleraVacia = document.getElementById('revisionPapeleraVacia');

    const BADGES_PUBLICACION = {
        PENDIENTE: 'bg-warning text-dark',
        APROBADO: 'bg-success',
        RECHAZADO: 'bg-danger'
    };

    // ==========================================
    // DATATABLE
    // ==========================================
    const tabla = $('#datatable_supervision').DataTable({
        serverSide: false,
        bDestroy: true,
        scrollX: true,
        scrollY: '55vh',
        scrollCollapse: true,

        ajax: {
            url: '?c=supervision&a=listar',
            data: function (d) { d.filtro = filtroActual; },
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) return [];
                return json.data;
            }
        },

        columns: [
            {
                data: 'titulo',
                render: function (data, type, row) {
                    const tituloCorto = truncar(data, 35);
                    const lugarCorto = truncar(row.direccion, 40);
                    return `
                        <span class="fw-bold text-dark d-block" title="${escapeAttribute(data || '')}">${escapeHtml(tituloCorto)}</span>
                        <span class="text-muted extra-small" title="${escapeAttribute(row.direccion || '')}">${escapeHtml(lugarCorto)}</span>
                    `;
                }
            }, 
            {
                data: null,
                render: function (row) {
                    const tema = row.tema || '—';
                    const ubi = row.direccion || '—';
                    return `
                        <span class="d-block fw-semibold text-dark">${escapeHtml(tema)}</span>
                        <span class="text-muted extra-small">${escapeHtml(ubi)}</span>
                    `;
                }
            },
            {
                data: 'registro_username',
                render: function (data) {
                    return data ? escapeHtml(data) : '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'publicacion',
                render: function (data) {
                    const clase = BADGES_PUBLICACION[data] || 'bg-secondary';
                    return `<span class="badge ${clase} px-3 py-2">${escapeHtml(data || '')}</span>`;
                }
            },
            {
                data: 'supervisor_username',
                render: function (data) {
                    return data ? escapeHtml(data) : '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'observaciones',
                render: function (data) {
                    if (!data) return '<span class="text-muted">—</span>';
                    const corto = data.length > 40 ? data.substring(0, 40) + '…' : data;
                    return `<span title="${escapeAttribute(data)}">${escapeHtml(corto)}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (row) {
                    if (filtroActual === 'ELIMINADOS') {
                        return `
                            <button type="button"
                                    class="btn btn-sm btn-danger btn-eliminar-definitivo rounded-2 px-3 fw-semibold"
                                    data-id="${row.id}"
                                    data-titulo="${escapeAttribute(row.titulo || '')}">
                                <i class="fas fa-trash-alt fs-8 me-1"></i>Eliminar definitivo
                            </button>
                        `;
                    }

                    const puedeAccionar = row.publicacion === 'PENDIENTE' || row.publicacion === 'RECHAZADO';
                    return `
                        <button type="button"
                                class="btn btn-sm ${puedeAccionar ? 'btn-luxury-primary' : 'btn-outline-secondary'} btn-revisar-evento rounded-2 px-3 fw-semibold"
                                data-id="${row.id}">
                            <i class="fas ${puedeAccionar ? 'fa-clipboard-check' : 'fa-eye'} fs-8 me-1"></i>${puedeAccionar ? 'Revisar' : 'Ver'}
                        </button>
                    `;
                }
            }
        ],

        columnDefs: [
            { targets: 2, width: '110px' },
            { targets: 3, width: '100px' },
            { targets: 4, width: '110px' },
            { targets: 6, width: '100px' },
        ],

        language: {
            paginate: {
                next: '<i class="fa fa-angle-double-right"></i>',
                previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
            },
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron eventos",
            emptyTable: "No hay eventos en esta sección",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    $(window).on('resize', function () {
        tabla.columns.adjust();
    });

    // ==========================================
    // PESTAÑAS (PENDIENTE / APROBADO / RECHAZADO / ELIMINADOS)
    // ==========================================
    document.querySelectorAll('#pills-tab-supervision .tab-filtro').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-supervision .tab-filtro').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filtroActual = this.getAttribute('data-filtro');
            tabla.ajax.reload();
        });
    });

    // ==========================================
    // EVENTOS DE TABLA Y RESTAURACIÓN
    // ==========================================
    document.addEventListener('click', function (e) {
        const btnRevisar = e.target.closest('.btn-revisar-evento');
        if (btnRevisar) {
            abrirModalRevision(btnRevisar.getAttribute('data-id'));
        }

        const btnEliminarDef = e.target.closest('.btn-eliminar-definitivo');
        if (btnEliminarDef) {
            idParaEliminarDefinitivo = btnEliminarDef.getAttribute('data-id');
            document.getElementById('tituloEliminarDefinitivo').innerText = btnEliminarDef.getAttribute('data-titulo');
            if (modalEliminarDef) modalEliminarDef.show();
        }

        const btnRestaurarImg = e.target.closest('.btn-restaurar-imagen-revision');
        if (btnRestaurarImg) {
            const imagenId = btnRestaurarImg.getAttribute('data-imagen-id');
            const eventoId = document.getElementById('revisionEventoId').value;

            const formData = new FormData();
            formData.append('evento_id', eventoId);
            formData.append('imagen_id', imagenId);

            fetch('?c=supervision&a=restaurarImagen', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderImagenesActivasRevision(data.imagenes || []);
                        renderImagenesPapeleraRevision(data.papelera || []);
                    } else {
                        Swal.fire('Atención', data.message || 'No se pudo restaurar la imagen', 'warning');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
        }
    });

    // ==========================================
    // CONFIRMAR ELIMINACIÓN DEFINITIVA
    // ==========================================
    const btnConfirmarEliminarDef = document.getElementById('btnConfirmarEliminarDefinitivo');
    if (btnConfirmarEliminarDef) {
        btnConfirmarEliminarDef.addEventListener('click', function () {
            if (!idParaEliminarDefinitivo) return;

            const formData = new FormData();
            formData.append('id', idParaEliminarDefinitivo);

            btnConfirmarEliminarDef.disabled = true;

            fetch('?c=supervision&a=eliminarDefinitivo', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (modalEliminarDef) modalEliminarDef.hide();
                    if (data.success) {
                        tabla.ajax.reload(null, false);
                        Swal.fire('Listo', data.message, 'success');
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo eliminar el evento', 'error');
                    }
                })
                .catch(() => {
                    if (modalEliminarDef) modalEliminarDef.hide();
                    Swal.fire('Error', 'Error en la petición al servidor', 'error');
                })
                .finally(() => {
                    btnConfirmarEliminarDef.disabled = false;
                    idParaEliminarDefinitivo = null;
                });
        });
    }

    // ==========================================
    // ABRIR MODAL DE REVISIÓN
    // ==========================================
    function abrirModalRevision(id) {
        fetch(`?c=supervision&a=obtener&id=${encodeURIComponent(id)}`)
            .then(res => res.json())
            .then(result => {
                if (!result.success) {
                    Swal.fire('Error', result.message || 'No se pudo cargar el evento', 'error');
                    return;
                }

                const data = result.data;

                document.getElementById('revisionEventoId').value = data.id;
                if (lblTituloRevision) lblTituloRevision.innerText = data.titulo || '';

                const claseBadge = BADGES_PUBLICACION[data.publicacion] || 'bg-secondary';
                badgeEstadoRevision.innerHTML = `<span class="badge ${claseBadge} px-3 py-2">${escapeHtml(data.publicacion || '')}</span>`;

                document.getElementById('revLugar').innerText = data.direccion || '—';
                document.getElementById('revCategoriaTipo').innerText = data.tema || '—';
                document.getElementById('revOrganizador').innerText = data.registro_username || '—';
                document.getElementById('revPrecio').innerText = Number(data.precio || 0).toLocaleString('es-PE', { minimumFractionDigits: 2 });
                document.getElementById('revFechaHora').innerText = data.fecha_evento || '—';
                document.getElementById('revAforo').innerText = data.cupos ? `${data.cupos} personas` : '—';
                document.getElementById('revRegistrador').innerText = data.registro_username || '—';
                document.getElementById('revSupervisor').innerText = data.supervisor_username || '—';
                document.getElementById('revDescripcion').innerText = data.descripcion || 'Sin descripción';

                if (data.observaciones) {
                    document.getElementById('revObservaciones').innerText = data.observaciones;
                    bloqueObservacionesPrevias.classList.remove('d-none');
                } else {
                    bloqueObservacionesPrevias.classList.add('d-none');
                }

                renderImagenesActivasRevision(data.imagenes || []);
                renderImagenesPapeleraRevision(data.imagenes_papelera || []);

                resetearBloqueRechazo();
                if (data.publicacion === 'PENDIENTE') {
                    accionesRevisionPendiente.classList.remove('d-none');
                    btnRechazarEvento.classList.remove('d-none');
                    btnAprobarEvento.classList.remove('d-none');
                    btnVolverPendiente.classList.add('d-none');
                } else if (data.publicacion === 'RECHAZADO') {
                    accionesRevisionPendiente.classList.remove('d-none');
                    btnRechazarEvento.classList.add('d-none');
                    btnAprobarEvento.classList.add('d-none');
                    btnVolverPendiente.classList.remove('d-none');
                } else {
                    accionesRevisionPendiente.classList.add('d-none');
                }

                if (modalRevision) modalRevision.show();
            })
            .catch(() => Swal.fire('Error', 'Error de conexión al cargar el evento', 'error'));
    }

    function renderImagenesActivasRevision(imagenes) {
        gridRevisionActivas.innerHTML = imagenes.map(img => `
            <div class="col-6 col-md-3">
                <div class="position-relative border rounded-2 overflow-hidden shadow-sm">
                    <span class="badge bg-dark position-absolute top-0 start-0 m-1">#${img.orden}</span>
                    <img src="${BASE_URL_JS()}/${img.ruta}" class="w-100" style="height: 110px; object-fit: cover;" alt="Imagen evento">
                </div>
            </div>
        `).join('') || '<p class="text-muted extra-small">Este evento no tiene imágenes activas.</p>';
    }

    function renderImagenesPapeleraRevision(imagenes) {
        if (!imagenes || imagenes.length === 0) {
            gridRevisionPapelera.innerHTML = '';
            if (revisionPapeleraVacia) revisionPapeleraVacia.classList.remove('d-none');
            return;
        }

        if (revisionPapeleraVacia) revisionPapeleraVacia.classList.add('d-none');

        gridRevisionPapelera.innerHTML = imagenes.map(img => `
            <div class="col-6 col-md-3">
                <div class="position-relative border rounded-2 overflow-hidden shadow-sm opacity-75">
                    <img src="${BASE_URL_JS()}/${img.ruta}" class="w-100" style="height: 110px; object-fit: cover;" alt="Imagen en papelera">
                    <button type="button" class="btn btn-sm btn-success position-absolute top-0 end-0 m-1 btn-restaurar-imagen-revision"
                            data-imagen-id="${img.id}" title="Restaurar">
                        <i class="fas fa-undo fs-8"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    // ==========================================
    // VOLVER A PENDIENTE
    // ==========================================
    if (btnVolverPendiente) {
        btnVolverPendiente.addEventListener('click', function () {
            const id = document.getElementById('revisionEventoId').value;

            Swal.fire({
                title: '¿Volver a pendiente?',
                text: 'El evento regresará a la cola de revisión (PENDIENTE) para que se vuelva a evaluar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, reenviar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d39e00',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('id', id);

                btnVolverPendiente.disabled = true;

                fetch('?c=supervision&a=volverAPendiente', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (modalRevision) modalRevision.hide();
                            tabla.ajax.reload(null, false);
                            Swal.fire('Listo', data.message, 'success');
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo reenviar el evento', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'))
                    .finally(() => { btnVolverPendiente.disabled = false; });
            });
        });
    }

    // ==========================================
    // APROBAR
    // ==========================================
    if (btnAprobarEvento) {
        btnAprobarEvento.addEventListener('click', function () {
            const id = document.getElementById('revisionEventoId').value;

            Swal.fire({
                title: '¿Aprobar evento?',
                html: 'Las imágenes que sigan en la papelera se eliminarán definitivamente al aprobar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, aprobar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('id', id);

                btnAprobarEvento.disabled = true;

                fetch('?c=supervision&a=aprobar', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (modalRevision) modalRevision.hide();
                            tabla.ajax.reload(null, false);
                            Swal.fire('Aprobado', data.message, 'success');
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo aprobar el evento', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'))
                    .finally(() => { btnAprobarEvento.disabled = false; });
            });
        });
    }

    // ==========================================
    // RECHAZAR
    // ==========================================
    if (btnRechazarEvento) {
        btnRechazarEvento.addEventListener('click', function () {
            if (!modoConfirmandoRechazo) {
                bloqueRechazo.classList.remove('d-none');
                motivoRechazo.focus();
                btnRechazarEvento.innerText = 'Confirmar Rechazo';
                modoConfirmandoRechazo = true;
                return;
            }

            const observaciones = motivoRechazo.value.trim();
            if (observaciones === '') {
                Swal.fire('Atención', 'Debes indicar el motivo del rechazo.', 'warning');
                return;
            }

            const id = document.getElementById('revisionEventoId').value;
            const formData = new FormData();
            formData.append('id', id);
            formData.append('observaciones', observaciones);

            btnRechazarEvento.disabled = true;

            fetch('?c=supervision&a=rechazar', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (modalRevision) modalRevision.hide();
                        tabla.ajax.reload(null, false);
                        Swal.fire('Rechazado', data.message, 'success');
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo rechazar el evento', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'))
                .finally(() => { btnRechazarEvento.disabled = false; });
        });
    }

    function resetearBloqueRechazo() {
        modoConfirmandoRechazo = false;
        if (bloqueRechazo) bloqueRechazo.classList.add('d-none');
        if (motivoRechazo) motivoRechazo.value = '';
        if (btnRechazarEvento) btnRechazarEvento.innerText = 'Rechazar';
    }

    if (modalRevisionElement) {
        modalRevisionElement.addEventListener('hidden.bs.modal', resetearBloqueRechazo);
    }

    // ==========================================
    // AUXILIARES
    // ==========================================
    function BASE_URL_JS() {
        return (typeof BASE_URL !== 'undefined') ? BASE_URL : '';
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