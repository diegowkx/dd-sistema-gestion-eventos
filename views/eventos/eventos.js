$(document).ready(function () {

    let estadoFiltro = 'ACTIVO';

    const modalEventoElement = document.getElementById('modalEvento');
    const eventoModal = modalEventoElement
        ? new bootstrap.Modal(modalEventoElement)
        : null;

    const formEvento = document.getElementById('formEvento');
    const lblTituloEvento = document.getElementById('lblTituloEvento');
    const avisoEdicionEvento = document.getElementById('avisoEdicionEvento');
    const btnGuardarEvento = document.getElementById('btnGuardarEvento');
    const tabImagenesEvento = document.getElementById('tabImagenesEvento');
    const avisoGuardarPrimero = document.getElementById('avisoGuardarPrimero');
    const bloqueImagenes = document.getElementById('bloqueImagenes');
    const gridImagenesActivas = document.getElementById('gridImagenesActivas');
    const gridImagenesPapelera = document.getElementById('gridImagenesPapelera');
    const papeleraVacia = document.getElementById('papeleraVacia');
    const contadorImagenes = document.getElementById('contadorImagenes');
    const inputImagenNueva = document.getElementById('inputImagenNueva');
    const btnSubirImagen = document.getElementById('btnSubirImagen');

    const BADGES_PUBLICACION = {
        PENDIENTE: 'bg-warning text-dark',
        APROBADO: 'bg-success',
        RECHAZADO: 'bg-danger'
    };

    // ==========================================
    // DATATABLE
    // ==========================================
    const tabla = $('#datatable_eventos').DataTable({
        serverSide: false,
        bDestroy: true,
        scrollX: true,
        scrollY: '55vh',
        scrollCollapse: true,

        ajax: {
            url: '?c=eventos&a=listar',
            data: function (d) {
                d.estado = estadoFiltro;
            },
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) {
                    return [];
                }
                return json.data;
            }
        },

        columns: [
            {
                data: 'titulo',
                render: function (data, type, row) {
                    const tituloCorto = truncar(data, 35);
                    const temaCorto = truncar(row.tema, 30);
                    return `
                        <span class="fw-bold text-dark d-block" title="${escapeAttribute(data || '')}">${escapeHtml(tituloCorto)}</span>
                        <span class="text-muted extra-small" title="${escapeAttribute(row.tema || '')}">${escapeHtml(temaCorto)}</span>
                    `;
                }
            },
            {
                data: 'fecha_evento',
                render: function (data) {
                    if (!data) return '<span class="text-muted">—</span>';
                    return `<span class="small">${escapeHtml(data)}</span>`;
                }
            },
            {
                data: 'cupos',
                render: function (data) {
                    return `<span class="fw-semibold">${Number(data || 0)}</span>`;
                }
            },
            {
                data: 'precio',
                render: function (data) {
                    const numero = Number(data || 0);
                    return `<span class="fw-semibold">${numero.toLocaleString('es-PE', { minimumFractionDigits: 2 })}</span>`;
                }
            },
            {
                data: 'publicacion',
                render: function (data) {
                    const claseBadge = BADGES_PUBLICACION[data] || 'bg-secondary';
                    return `<span class="badge ${claseBadge} px-3 py-2">${escapeHtml(data || '')}</span>`;
                }
            },
            {
                data: 'observaciones',
                render: function (data, type, row) {
                    if (!data) return '<span class="text-muted">—</span>';
                    const corto = data.length > 40 ? data.substring(0, 40) + '…' : data;
                    const clase = row.publicacion === 'RECHAZADO' ? 'text-danger fw-semibold' : '';
                    return `<span class="${clase}" title="${escapeAttribute(data)}">${escapeHtml(corto)}</span>`;
                }
            },
            {
                data: 'registro_username',
                render: function (data) {
                    return data ? escapeHtml(data) : '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'supervisor_username',
                render: function (data) {
                    return data ? escapeHtml(data) : '<span class="text-muted">—</span>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (row) {
                    const jsonEvento = encodeURIComponent(JSON.stringify(row));

                    const svgEditar = `
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                            <path d="m15 5 4 4"/>
                        </svg>
                    `;

                    const svgDesactivar = `
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                    `;

                    const svgRestaurar = `
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="1 4 1 10 7 10"/>
                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                        </svg>
                    `;

                    let botones = `
                        <button type="button"
                                class="btn btn-warning text-white btn-editar-evento rounded-circle shadow-sm"
                                data-evento="${jsonEvento}"
                                style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                title="Editar">
                            ${svgEditar}
                        </button>
                    `;

                    if (row.activo == 1) {
                        botones += `
                            <button type="button"
                                    class="btn btn-danger btn-desactivar-evento rounded-circle shadow-sm"
                                    data-id="${row.id}"
                                    data-titulo="${escapeAttribute(row.titulo || '')}"
                                    style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                    title="Eliminar">
                                ${svgDesactivar}
                            </button>
                        `;
                    } else {
                        botones += `
                            <button type="button"
                                    class="btn btn-success btn-restaurar-evento rounded-circle shadow-sm"
                                    data-id="${row.id}"
                                    data-titulo="${escapeAttribute(row.titulo || '')}"
                                    style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                    title="Restaurar">
                                ${svgRestaurar}
                            </button>
                        `;
                    }

                    return `<div class="d-flex align-items-center justify-content-center gap-2">${botones}</div>`;
                }
            }
        ],

        columnDefs: [
            { targets: 2, width: '80px' },   // Cupos
            { targets: 3, width: '90px' },   // Precio
            { targets: 4, width: '100px' },  // Publicación
            { targets: 6, width: '110px' },  // Registrado por
            { targets: 7, width: '110px' },  // Supervisor
            { targets: 8, width: '90px' },   // Acciones
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
    // PESTAÑAS DE ESTADO (ACTIVO / INACTIVO)
    // ==========================================
    document.querySelectorAll('#pills-tab-eventos .tab-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-eventos .tab-estado').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            estadoFiltro = this.getAttribute('data-estado');
            tabla.ajax.reload();
        });
    });

    // ==========================================
    // PESTAÑAS DENTRO DEL MODAL (DATOS / IMÁGENES)
    // ==========================================
    document.querySelectorAll('.tab-form-evento').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.tab-form-evento').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            document.querySelectorAll('.panel-form-evento').forEach(p => p.classList.add('d-none'));
            document.querySelector(this.getAttribute('data-target')).classList.remove('d-none');
        });
    });

    // ==========================================
    // NUEVO EVENTO
    // ==========================================
    const btnNuevo = document.querySelector('.btn-nuevo-evento');

    if (btnNuevo && eventoModal) {
        btnNuevo.addEventListener('click', function () {
            prepararFormularioNuevo();
            eventoModal.show();
        });
    }

    // ==========================================
    // EVENTOS DE TABLA
    // ==========================================
    document.addEventListener('click', function (e) {

        // EDITAR
        const btnEditar = e.target.closest('.btn-editar-evento');
        if (btnEditar) {
            const rawData = btnEditar.getAttribute('data-evento');
            let row;
            try {
                row = JSON.parse(decodeURIComponent(rawData));
            } catch (error) {
                Swal.fire('Error', 'No se pudo interpretar la información del evento.', 'error');
                return;
            }
            cargarEventoParaEdicion(row.id);
        }

        // ELIMINAR (lógico)
        const btnDesactivar = e.target.closest('.btn-desactivar-evento');
        if (btnDesactivar) {
            const id = btnDesactivar.getAttribute('data-id');
            const titulo = btnDesactivar.getAttribute('data-titulo');

            Swal.fire({
                title: '¿Eliminar Evento?',
                html: `¿Deseas eliminar <strong>"${escapeHtml(titulo)}"</strong>? El registro no se borrará de forma definitiva, solo quedará inactivo.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('id', id);

                fetch('?c=eventos&a=eliminar', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            tabla.ajax.reload(null, false);
                            Swal.fire('Eliminado', data.message, 'success');
                        } else {
                            Swal.fire('Error', data.message || 'Error al eliminar', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
            });
        }

        // RESTAURAR
        const btnRestaurar = e.target.closest('.btn-restaurar-evento');
        if (btnRestaurar) {
            const id = btnRestaurar.getAttribute('data-id');
            const titulo = btnRestaurar.getAttribute('data-titulo');

            Swal.fire({
                title: '¿Restaurar Evento?',
                text: `¿Deseas volver a activar "${titulo}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Restaurar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('id', id);

                fetch('?c=eventos&a=restaurar', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            tabla.ajax.reload(null, false);
                            Swal.fire('Restaurado', data.message, 'success');
                        } else {
                            Swal.fire('Error', data.message || 'Error al restaurar', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
            });
        }

        // ELIMINAR IMAGEN (a papelera)
        const btnEliminarImg = e.target.closest('.btn-eliminar-imagen');
        if (btnEliminarImg) {
            const imagenId = btnEliminarImg.getAttribute('data-imagen-id');
            const eventoId = document.getElementById('id').value;

            Swal.fire({
                title: '¿Enviar a la papelera?',
                text: 'La imagen podrá restaurarse mientras no se apruebe el evento.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('evento_id', eventoId);
                formData.append('imagen_id', imagenId);

                fetch('?c=eventos&a=eliminarImagen', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            renderImagenesActivas(data.imagenes);
                            renderImagenesPapelera(data.papelera);
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo enviar a la papelera', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
            });
        }

        // RESTAURAR IMAGEN (desde papelera)
        const btnRestaurarImg = e.target.closest('.btn-restaurar-imagen');
        if (btnRestaurarImg) {
            const imagenId = btnRestaurarImg.getAttribute('data-imagen-id');
            const eventoId = document.getElementById('id').value;

            const formData = new FormData();
            formData.append('evento_id', eventoId);
            formData.append('imagen_id', imagenId);

            fetch('?c=eventos&a=restaurarImagen', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderImagenesActivas(data.imagenes);
                        renderImagenesPapelera(data.papelera);
                    } else {
                        Swal.fire('Atención', data.message || 'No se pudo restaurar la imagen', 'warning');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
        }
    });

    // ==========================================
    // SUBIR IMAGEN
    // ==========================================
    if (btnSubirImagen) {
        btnSubirImagen.addEventListener('click', function () {
            const eventoId = document.getElementById('id').value;
            const archivo = inputImagenNueva.files[0];

            if (!eventoId || eventoId === '0') {
                Swal.fire('Atención', 'Guarda primero los datos del evento.', 'warning');
                return;
            }
            if (!archivo) {
                Swal.fire('Atención', 'Selecciona una imagen primero.', 'warning');
                return;
            }
            if (archivo.size > 5 * 1024 * 1024) {
                Swal.fire('Atención', 'La imagen no puede superar los 5 MB.', 'warning');
                return;
            }

            const formatosValidos = ['image/jpeg', 'image/png', 'image/webp'];
            if (!formatosValidos.includes(archivo.type)) {
                Swal.fire('Atención', 'Solo se permiten imágenes JPG, PNG o WEBP.', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('evento_id', eventoId);
            formData.append('imagen', archivo);

            btnSubirImagen.disabled = true;

            fetch('?c=eventos&a=subirImagen', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        inputImagenNueva.value = '';
                        renderImagenesActivas(data.imagenes);
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo subir la imagen', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'))
                .finally(() => { btnSubirImagen.disabled = false; });
        });
    }

    // ==========================================
    // SUBMIT (CREAR / ACTUALIZAR DATOS)
    // ==========================================
    if (formEvento) {
        formEvento.addEventListener('submit', async function (e) {
            e.preventDefault();

            const esEdicion = document.getElementById('es_edicion').value === '1';

            const camposObligatorios = ['titulo', 'tema', 'fecha_evento', 'direccion', 'cupos', 'precio'];
            for (const campo of camposObligatorios) {
                const el = document.getElementById(campo);
                if (!el.value || String(el.value).trim() === '') {
                    return Swal.fire('Atención', 'Completa todos los campos obligatorios.', 'warning');
                }
            }

            if (esEdicion) {
                const confirmacion = await Swal.fire({
                    title: '¿Actualizar evento?',
                    html: `
                        Estás modificando un evento.<br><br>
                        <strong>Si no cuentas con permiso para aprobar eventos, pasará a revisión (PENDIENTE).</strong>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, actualizar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d39e00',
                    cancelButtonColor: '#6c757d'
                });
                if (!confirmacion.isConfirmed) return;
            }

            const formData = new FormData(this);

            try {
                if (btnGuardarEvento) btnGuardarEvento.disabled = true;

                const res = await fetch('?c=eventos&a=guardar', { method: 'POST', body: formData });
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'Error al procesar el evento');
                }

                tabla.ajax.reload(null, false);

                const esRevision = esEdicion &&
                    typeof data.message === 'string' &&
                    (data.message.toUpperCase().includes('PENDIENTE') || data.message.toLowerCase().includes('revisión'));

                if (!esEdicion && data.id) {
                    // Evento recién creado: habilitar gestión de imágenes sin cerrar el modal.
                    document.getElementById('id').value = data.id;
                    document.getElementById('es_edicion').value = '1';
                    if (lblTituloEvento) lblTituloEvento.innerText = 'EDITAR EVENTO';
                    if (btnGuardarEvento) btnGuardarEvento.innerText = 'Actualizar Evento';
                    habilitarGestionImagenes(data.id);

                    Swal.fire('¡Evento creado!', data.message, 'success');
                    document.getElementById('tabImagenesEvento').click();
                } else {
                    eventoModal.hide();
                    Swal.fire(
                        esRevision ? 'Actualizado y enviado a revisión' : '¡Éxito!',
                        data.message,
                        esRevision ? 'warning' : 'success'
                    );
                }
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            } finally {
                if (btnGuardarEvento) btnGuardarEvento.disabled = false;
            }
        });
    }

    // ==========================================
    // FUNCIONES AUXILIARES
    // ==========================================
    function prepararFormularioNuevo() {
        if (formEvento) formEvento.reset();

        document.getElementById('id').value = '0';
        document.getElementById('es_edicion').value = '0';

        if (lblTituloEvento) lblTituloEvento.innerText = 'NUEVO EVENTO';
        if (avisoEdicionEvento) avisoEdicionEvento.classList.add('d-none');
        const avisoObs = document.getElementById('avisoObservacionesRechazo');
        if (avisoObs) avisoObs.classList.add('d-none');
        if (btnGuardarEvento) btnGuardarEvento.innerText = 'Guardar Evento';

        document.querySelector('.tab-form-evento[data-target="#panelDatosEvento"]').click();

        if (avisoGuardarPrimero) avisoGuardarPrimero.classList.remove('d-none');
        if (bloqueImagenes) bloqueImagenes.classList.add('d-none');

        gridImagenesActivas.innerHTML = '';
        gridImagenesPapelera.innerHTML = '';
    }

    function habilitarGestionImagenes(eventoId) {
        if (avisoGuardarPrimero) avisoGuardarPrimero.classList.add('d-none');
        if (bloqueImagenes) bloqueImagenes.classList.remove('d-none');
    }

    function cargarEventoParaEdicion(id) {
        fetch(`?c=eventos&a=obtener&id=${encodeURIComponent(id)}`)
            .then(res => res.json())
            .then(result => {
                if (!result.success) {
                    Swal.fire('Error', result.message || 'No se pudo cargar el evento', 'error');
                    return;
                }

                const data = result.data;

                if (formEvento) formEvento.reset();

                document.getElementById('id').value = data.id;
                document.getElementById('es_edicion').value = '1';
                document.getElementById('titulo').value = data.titulo || '';
                document.getElementById('tema').value = data.tema || '';
                
                // Formatear la fecha para input datetime-local
                if (data.fecha_evento) {
                    document.getElementById('fecha_evento').value = data.fecha_evento.replace(' ', 'T').substring(0, 16);
                }

                document.getElementById('direccion').value = data.direccion || '';
                document.getElementById('cupos').value = data.cupos || 0;
                document.getElementById('precio').value = data.precio || 0.00;
                document.getElementById('descripcion').value = data.descripcion || '';

                if (lblTituloEvento) lblTituloEvento.innerText = 'EDITAR EVENTO: ' + (data.titulo || '');
                if (avisoEdicionEvento) avisoEdicionEvento.classList.remove('d-none');
                if (btnGuardarEvento) btnGuardarEvento.innerText = 'Actualizar Evento';

                const avisoObs = document.getElementById('avisoObservacionesRechazo');
                if (data.publicacion === 'RECHAZADO' && data.observaciones) {
                    document.getElementById('textoObservacionesRechazo').innerText = data.observaciones;
                    avisoObs.classList.remove('d-none');
                } else {
                    avisoObs.classList.add('d-none');
                }

                document.querySelector('.tab-form-evento[data-target="#panelDatosEvento"]').click();

                habilitarGestionImagenes(data.id);
                renderImagenesActivas(data.imagenes || []);
                renderImagenesPapelera(data.imagenes_papelera || []);

                eventoModal.show();
            })
            .catch(() => Swal.fire('Error', 'Error de conexión al cargar el evento', 'error'));
    }

    function renderImagenesActivas(imagenes) {
        contadorImagenes.innerText = imagenes.length;

        gridImagenesActivas.innerHTML = imagenes.map(img => `
            <div class="col-6 col-md-3 imagen-activa-item" draggable="true" data-imagen-id="${img.id}">
                <div class="position-relative border rounded-2 overflow-hidden shadow-sm">
                    <span class="badge bg-dark position-absolute top-0 start-0 m-1">#${img.orden}</span>
                    <img src="${BASE_URL_JS()}/${img.ruta}" class="w-100" style="height: 110px; object-fit: cover;" alt="Imagen evento">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 btn-eliminar-imagen"
                            data-imagen-id="${img.id}" title="Enviar a papelera">
                        <i class="fas fa-trash fs-8"></i>
                    </button>
                </div>
            </div>
        `).join('') || '<p class="text-muted extra-small">Aún no hay imágenes activas.</p>';

        activarDragAndDrop();
    }

    function renderImagenesPapelera(imagenes) {
        if (!imagenes || imagenes.length === 0) {
            gridImagenesPapelera.innerHTML = '';
            if (papeleraVacia) papeleraVacia.classList.remove('d-none');
            return;
        }

        if (papeleraVacia) papeleraVacia.classList.add('d-none');

        gridImagenesPapelera.innerHTML = imagenes.map(img => `
            <div class="col-6 col-md-3">
                <div class="position-relative border rounded-2 overflow-hidden shadow-sm opacity-75">
                    <img src="${BASE_URL_JS()}/${img.ruta}" class="w-100" style="height: 110px; object-fit: cover;" alt="Imagen en papelera">
                    <button type="button" class="btn btn-sm btn-success position-absolute top-0 end-0 m-1 btn-restaurar-imagen"
                            data-imagen-id="${img.id}" title="Restaurar">
                        <i class="fas fa-undo fs-8"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    // ==========================================
    // REORDENAMIENTO POR DRAG & DROP
    // ==========================================
    let elementoArrastrado = null;

    function activarDragAndDrop() {
        const items = gridImagenesActivas.querySelectorAll('.imagen-activa-item');

        items.forEach(item => {
            item.addEventListener('dragstart', function () {
                elementoArrastrado = item;
                item.classList.add('opacity-50');
            });

            item.addEventListener('dragend', function () {
                item.classList.remove('opacity-50');
                guardarNuevoOrden();
            });

            item.addEventListener('dragover', function (e) {
                e.preventDefault();
                const enElemento = e.target.closest('.imagen-activa-item');
                if (!enElemento || enElemento === elementoArrastrado) return;

                const rect = enElemento.getBoundingClientRect();
                const despuesDeMedio = (e.clientX - rect.left) > (rect.width / 2);

                gridImagenesActivas.insertBefore(
                    elementoArrastrado,
                    despuesDeMedio ? enElemento.nextSibling : enElemento
                );
            });
        });
    }

    function guardarNuevoOrden() {
        const eventoId = document.getElementById('id').value;
        const ids = Array.from(gridImagenesActivas.querySelectorAll('.imagen-activa-item'))
            .map(item => item.getAttribute('data-imagen-id'));

        if (ids.length === 0) return;

        const formData = new FormData();
        formData.append('evento_id', eventoId);
        ids.forEach(id => formData.append('orden[]', id));

        fetch('?c=eventos&a=reordenarImagenes', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderImagenesActivas(data.imagenes);
                } else {
                    Swal.fire('Error', data.message || 'No se pudo guardar el nuevo orden', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
    }

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