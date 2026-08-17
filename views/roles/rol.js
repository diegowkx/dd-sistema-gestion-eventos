$(document).ready(function () {
    let estadoFiltro = 'ACTIVO';

    const modalRolElement = document.getElementById('modalRol');
    const rolModal = modalRolElement ? new bootstrap.Modal(modalRolElement) : null;

    const formRol = document.getElementById('formRol');
    const lblTituloRol = document.getElementById('lblTituloRol');
    const lblCantPermisos = document.getElementById('lblCantPermisos');

    // ==========================================
    // CONTADOR DE PERMISOS SELECCIONADOS
    // ==========================================
    function actualizarContadorPermisos() {
        if (!lblCantPermisos) return;
        const total = document.querySelectorAll('.chk-permiso:checked').length;
        lblCantPermisos.innerText = `${total} seleccionado(s)`;
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('chk-permiso')) {
            actualizarContadorPermisos();
        }
    });

    function limpiarChecksPermisos() {
        document.querySelectorAll('.chk-permiso').forEach(chk => chk.checked = false);
        actualizarContadorPermisos();
    }

    function marcarChecksPermisos(permisosDelRol) {
        limpiarChecksPermisos();
        if (!Array.isArray(permisosDelRol)) return;

        permisosDelRol.forEach(nombrePermiso => {
            const chk = document.getElementById('permiso_' + nombrePermiso);
            if (chk) chk.checked = true;
        });
        actualizarContadorPermisos();
    }

    // Convertir nombre de rol a mayúsculas dinámicamente al escribir
    const inputNombreRol = document.getElementById('nombre_rol');
    if (inputNombreRol) {
        inputNombreRol.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // ==========================================
    // DATATABLE PRINCIPAL
    // ==========================================
    const tabla = $('#datatable_roles').DataTable({
        autoWidth: false,
        serverSide: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=roles&a=listar',
            data: function (d) {
                d.estado = estadoFiltro;
            },
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) return [];
                return json.data;
            }
        },
        columns: [
            { data: 'id' },
            {
                data: 'nombre',
                render: function (data) {
                    return `<span class="fw-bold text-dark">${data}</span>`;
                }
            },
            {
                data: 'permisos',
                orderable: false,
                render: function (permisos) {
                    if (!Array.isArray(permisos) || permisos.length === 0) {
                        return '<span class="text-muted">Sin módulos asignados</span>';
                    }
                    return permisos.map(p =>
                        `<span class="badge bg-secondary me-1 mb-1 fs-7">${p}</span>`
                    ).join('');
                }
            },
            {
                data: 'activo',
                render: function (data) {
                    return data == 1
                        ? '<span class="badge bg-success px-3 py-2">ACTIVO</span>'
                        : '<span class="badge bg-danger px-3 py-2">INACTIVO</span>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (row) {
                    const jsonRol = encodeURIComponent(JSON.stringify(row));

                    // SVG de Lápiz (Editar)
                    const svgEditar = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>`;

                    // SVG de Usuario Desactivar
                    const svgDesactivar = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" y1="8" x2="22" y2="13"/><line x1="22" y1="8" x2="17" y2="13"/></svg>`;

                    // SVG de Usuario Restaurar / Check
                    const svgRestaurar = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>`;

                    let botones = `
                        <button type="button" class="btn btn-warning text-white btn-editar-rol rounded-circle shadow-sm" data-rol="${jsonRol}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Editar">
                            ${svgEditar}
                        </button>
                    `;

                    if (row.activo == 1) {
                        botones += `
                            <button type="button" class="btn btn-danger btn-desactivar-rol rounded-circle shadow-sm" data-id="${row.id}" data-nombre="${row.nombre}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Desactivar">
                                ${svgDesactivar}
                            </button>
                        `;
                    } else {
                        botones += `
                            <button type="button" class="btn btn-success btn-restaurar-rol rounded-circle shadow-sm" data-id="${row.id}" data-nombre="${row.nombre}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Restaurar">
                                ${svgRestaurar}
                            </button>
                        `;
                    }

                    return `<div class="d-flex align-items-center justify-content-center gap-2">${botones}</div>`;
                }
            }
        ],
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron roles",
            emptyTable: "No hay roles en esta sección",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    // Cambiar Pestañas (Se delega el estado activo y recarga de Ajax)
    document.querySelectorAll('#pills-tab-roles .tab-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-roles .tab-estado').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            estadoFiltro = this.getAttribute('data-estado');
            tabla.ajax.reload();
        });
    });

    // Nuevo Rol
    const btnNuevo = document.querySelector('.btn-nuevo-rol');
    if (btnNuevo && rolModal) {
        btnNuevo.addEventListener('click', function () {
            if (formRol) formRol.reset();
            limpiarChecksPermisos();

            const hiddenId = document.getElementById('rol_id_hidden');
            if (hiddenId) hiddenId.value = '';

            const esEdicionInput = document.getElementById('es_edicion');
            if (esEdicionInput) esEdicionInput.value = '0';

            if (lblTituloRol) lblTituloRol.innerText = 'NUEVO ROL';

            rolModal.show();
        });
    }

    // ACCIONES DE TABLA
    document.addEventListener('click', function (e) {

        // --- EDITAR ROL ---
        const btnEditar = e.target.closest('.btn-editar-rol');
        if (btnEditar && rolModal) {
            const rawData = btnEditar.getAttribute('data-rol');
            const rowData = JSON.parse(decodeURIComponent(rawData));

            fetch(`?c=roles&a=obtener&id=${rowData.id}`)
                .then(res => res.json())
                .then(result => {
                    if (!result.success) {
                        Swal.fire('Error', result.message || 'No se pudo cargar el rol', 'error');
                        return;
                    }

                    const data = result.data;

                    if (formRol) formRol.reset();

                    const hiddenId = document.getElementById('rol_id_hidden');
                    if (hiddenId) hiddenId.value = data.id;

                    const esEdicionInput = document.getElementById('es_edicion');
                    if (esEdicionInput) esEdicionInput.value = '1';

                    const inputNombre = document.getElementById('nombre_rol');
                    if (inputNombre) inputNombre.value = data.nombre;

                    if (lblTituloRol) lblTituloRol.innerText = 'EDITAR ROL: ' + data.nombre;

                    marcarChecksPermisos(data.permisos || []);

                    rolModal.show();
                })
                .catch(() => {
                    Swal.fire('Error', 'Error de conexión al cargar el rol', 'error');
                });
        }

        // --- DESACTIVAR ROL ---
        const btnDesactivar = e.target.closest('.btn-desactivar-rol');
        if (btnDesactivar) {
            const id = btnDesactivar.getAttribute('data-id');
            const nombre = btnDesactivar.getAttribute('data-nombre');

            Swal.fire({
                title: '¿Desactivar Rol?',
                text: `¿Deseas desactivar el rol "${nombre}"? Los usuarios con este rol no podrán iniciar sesión hasta reasignarlos.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);

                    fetch('?c=roles&a=eliminar', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Desactivado',
                                    text: data.message,
                                    confirmButtonColor: '#569208'
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Error al desactivar', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }

        // --- RESTAURAR ROL ---
        const btnRestaurar = e.target.closest('.btn-restaurar-rol');
        if (btnRestaurar) {
            const id = btnRestaurar.getAttribute('data-id');
            const nombre = btnRestaurar.getAttribute('data-nombre');

            Swal.fire({
                title: '¿Restaurar Rol?',
                text: `¿Deseas volver a activar el rol "${nombre}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#569208',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Restaurar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);

                    fetch('?c=roles&a=restaurar', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Restaurado',
                                    text: data.message,
                                    confirmButtonColor: '#569208'
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Error al restaurar', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }
    });

    // ==========================================
    // GUARDAR ROL CON VALIDACIONES FRONTEND
    // ==========================================
    if (formRol) {
        formRol.addEventListener('submit', async function (e) {
            e.preventDefault();

            const nombreInput = document.getElementById('nombre_rol');
            const nombreVal = nombreInput ? nombreInput.value.trim() : '';
            const permisosSeleccionados = document.querySelectorAll('.chk-permiso:checked');

            // 1. Validación: Nombre no vacío
            if (!nombreVal) {
                Swal.fire('Atención', 'Por favor ingrese el nombre del rol.', 'warning');
                if (nombreInput) nombreInput.focus();
                return;
            }

            // 2. Validación: Nombre con longitud mínima
            if (nombreVal.length < 3) {
                Swal.fire('Atención', 'El nombre del rol debe tener al menos 3 caracteres.', 'warning');
                if (nombreInput) nombreInput.focus();
                return;
            }

            // 3. Validación: Al menos 1 permiso/módulo seleccionado
            if (permisosSeleccionados.length === 0) {
                Swal.fire('Atención', 'Debe seleccionar al menos un módulo o permiso para el rol.', 'warning');
                return;
            }

            const formData = new FormData(this);

            try {
                const res = await fetch('?c=roles&a=guardar', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'Error al procesar el rol');
                }

                if (rolModal) rolModal.hide();
                tabla.ajax.reload(null, false);

                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: data.message,
                    confirmButtonColor: '#569208'
                });

            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }
});