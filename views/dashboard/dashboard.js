$(document).ready(function() {

    $.fn.dataTable.ext.errMode = 'none';

    const modalElement = document.getElementById('modalIpDetalle');
    let modalIp = null;
    if (modalElement && typeof bootstrap !== 'undefined') {
        modalIp = new bootstrap.Modal(modalElement);
    }

    const lenguajeEspanol = {
        processing: "Procesando...",
        search: "Buscar:",
        lengthMenu: "Mostrar _MENU_ registros",
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        loadingRecords: "Cargando...",
        zeroRecords: "No se encontraron resultados",
        emptyTable: "Ningún dato disponible en esta tabla",
        paginate: { first: "Primero", previous: "Anterior", next: "Siguiente", last: "Último" }
    };

    // ==========================================
    // DATATABLE: AUDITORÍA / HISTORIAL DE IPS
    // ==========================================
    if ($('#datatable_ips').length) {
        $('#datatable_ips').DataTable({
            autoWidth: false,
            responsive: true,
            bDestroy: true,
            pagingType: 'simple_numbers',
            ajax: { url: '?c=dashboard&a=listarIps', dataSrc: 'data' },
            columns: [
                { data: 'usuario_username', render: d => `<strong>${d}</strong>` },
                { data: 'usuario_nombre_completo' },
                { data: 'rol_name', render: d => `<span class="badge text-white" style="background-color: #6f42c1;">${d}</span>` },
                { data: 'ip_address', render: d => `<code class="text-danger fw-bold">${d}</code>` },
                { data: 'fecha_acceso' },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        const svgVer = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>`;
                        return `<button type="button" class="btn btn-ver-ip rounded-circle text-white shadow-sm" data-id="${row.id}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #d9383a; border: none;" title="Ver Detalle">${svgVer}</button>`;
                    }
                }
            ],
            language: lenguajeEspanol
        });
    }

    document.addEventListener('click', function(e) {
        const btnVerIp = e.target.closest('.btn-ver-ip');
        if (btnVerIp) {
            const id = btnVerIp.getAttribute('data-id');
            fetch(`?c=dashboard&a=getIpById&id=${id}`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const d = res.data;
                        document.getElementById('det_ip_id').innerText = `#${d.id}`;
                        document.getElementById('det_ip_usuario').innerText = d.usuario_username;
                        document.getElementById('det_ip_nombre').innerText = d.usuario_nombre_completo;
                        document.getElementById('det_ip_correo').innerText = d.correo || '-';
                        document.getElementById('det_ip_rol').innerText = d.rol_name;
                        document.getElementById('det_ip_address').innerText = d.ip_address;
                        document.getElementById('det_ip_fecha').innerText = d.fecha_acceso;
                        if (!modalIp && modalElement && typeof bootstrap !== 'undefined') {
                            modalIp = new bootstrap.Modal(modalElement);
                        }
                        if (modalIp) modalIp.show();
                    }
                });
        }
    });

    // ==========================================
    // KPIs + PANELES + GRÁFICO
    // ==========================================
    const ETIQUETAS_PUBLICACION = { PENDIENTE: 'Pendiente', APROBADO: 'Aprobado', RECHAZADO: 'Rechazado' };
    const COLOR_PUBLICACION = { PENDIENTE: 'bg-warning', APROBADO: 'bg-success', RECHAZADO: 'bg-danger' };

    const ETIQUETAS_ESTADO = { DISPONIBLE: 'Disponible', RESERVADA: 'Reservada', ALQUILADA: 'Alquilada', VENDIDA: 'Vendida' };
    const COLOR_ESTADO = { DISPONIBLE: 'bg-success', RESERVADA: 'bg-warning', ALQUILADA: 'bg-info', VENDIDA: 'bg-secondary' };

    function pintarKpis(resumen) {
        document.getElementById('kpi_total_inmuebles').innerText = resumen.total_inmuebles;
        document.getElementById('kpi_pendientes_revision').innerText = resumen.pendientes_revision;
        document.getElementById('kpi_usuarios_activos').innerText = resumen.usuarios_activos;
        document.getElementById('kpi_interesados_mes').innerText = resumen.interesados_este_mes;
    }

    function pintarListaConteo(contenedorId, datos, etiquetas, colores) {
        const contenedor = document.getElementById(contenedorId);
        if (!contenedor) return;

        const total = Object.values(datos).reduce((a, b) => a + b, 0);

        contenedor.innerHTML = Object.keys(etiquetas).map(clave => {
            const cantidad = datos[clave] || 0;
            const pct = total > 0 ? Math.round((cantidad / total) * 100) : 0;
            return `
                <div class="mb-3">
                    <div class="d-flex justify-content-between extra-small mb-1">
                        <span class="fw-semibold">${etiquetas[clave]}</span>
                        <span class="text-muted">${cantidad} (${pct}%)</span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 999px;">
                        <div class="progress-bar ${colores[clave]}" role="progressbar" style="width: ${pct}%; border-radius: 999px;"></div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function pintarInteresados(lista) {
        const contenedor = document.getElementById('lista-interesados');
        if (!contenedor) return;

        if (!Array.isArray(lista) || lista.length === 0) {
            contenedor.innerHTML = '<p class="text-muted extra-small mb-0">Sin registros recientes.</p>';
            return;
        }

        contenedor.innerHTML = lista.map(item => `
            <div class="d-flex justify-content-between align-items-start mb-3 pb-2 border-bottom border-light">
                <div>
                    <span class="fw-semibold d-block">${escapeHtml(item.nombres)} ${escapeHtml(item.apellidos)}</span>
                    <span class="extra-small text-muted">${escapeHtml(item.inmueble_titulo || '')}</span>
                </div>
                <span class="extra-small text-muted text-end">${item.fecha_contacto}</span>
            </div>
        `).join('');
    }

    let chartVentas = null;
    function pintarChartVentas(datos) {
        const canvas = document.getElementById('chart_ventas_alquileres');
        if (!canvas || typeof Chart === 'undefined') return;

        const etiquetas = datos.map(d => {
            const [anio, mes] = d.mes.split('-');
            const fecha = new Date(anio, mes - 1, 1);
            return fecha.toLocaleDateString('es-PE', { month: 'short', year: '2-digit' });
        });
        const vendidas = datos.map(d => parseInt(d.vendidas, 10));
        const alquiladas = datos.map(d => parseInt(d.alquiladas, 10));

        if (chartVentas) {
            chartVentas.data.labels = etiquetas;
            chartVentas.data.datasets[0].data = vendidas;
            chartVentas.data.datasets[1].data = alquiladas;
            chartVentas.update();
            return;
        }

        chartVentas = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: etiquetas,
                datasets: [
                    { label: 'Vendidas', data: vendidas, backgroundColor: '#6f42c1', borderRadius: 6, maxBarThickness: 28 },
                    { label: 'Alquiladas', data: alquiladas, backgroundColor: '#0dcaf0', borderRadius: 6, maxBarThickness: 28 }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    function cargarResumenDashboard() {
        fetch('?c=dashboard&a=resumen')
            .then(res => res.json())
            .then(result => {
                if (!result.success) return;
                pintarKpis(result.data.resumen);
                pintarListaConteo('lista-publicacion', result.data.inmuebles_por_publicacion, ETIQUETAS_PUBLICACION, COLOR_PUBLICACION);
                pintarListaConteo('lista-estado-comercial', result.data.inmuebles_por_estado, ETIQUETAS_ESTADO, COLOR_ESTADO);
                pintarInteresados(result.data.ultimos_interesados);
                pintarChartVentas(result.data.ventas_alquileres_6m);
            })
            .catch(() => {});
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value || '';
        return div.innerHTML;
    }

    cargarResumenDashboard();
    setInterval(cargarResumenDashboard, 60000);
});