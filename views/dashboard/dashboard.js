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
    const ETIQUETAS_PUBLICACION = { PENDIENTE: 'Pendientes', APROBADO: 'Publicados', RECHAZADO: 'Rechazados' };
    const COLOR_PUBLICACION = { PENDIENTE: 'bg-warning', APROBADO: 'bg-success', RECHAZADO: 'bg-danger' };

    function pintarKpis(resumen) {
        document.getElementById('kpi_eventos_totales').innerText = resumen.eventos_totales;
        document.getElementById('kpi_eventos_pendientes').innerText = resumen.eventos_pendientes;
        document.getElementById('kpi_ventas_realizadas').innerText = resumen.ventas_realizadas;
        document.getElementById('kpi_ingresos_estimados').innerText = Number(resumen.ingresos_estimados).toLocaleString('es-PE', { style: 'currency', currency: 'PEN' });
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

    function pintarAsistencia(resumen) {
        const contenedor = document.getElementById('lista-asistencia');
        if (!contenedor) return;
        const asistentes = Number(resumen.asistentes) || 0;
        const ventas = Number(resumen.ventas_realizadas) || 0;
        const porcentaje = Number(resumen.porcentaje_asistencia) || 0;
        contenedor.innerHTML = `
            <div class="mb-3"><div class="d-flex justify-content-between extra-small mb-1"><span class="fw-semibold">Asistentes</span><span>${asistentes}</span></div><div class="progress" style="height: 10px"><div class="progress-bar bg-success" style="width: ${porcentaje}%"></div></div></div>
            <div class="d-flex justify-content-between extra-small"><span class="fw-semibold">Inscritos</span><span>${ventas}</span></div>
            <div class="d-flex justify-content-between extra-small"><span class="fw-semibold">Porcentaje de asistencia</span><span>${porcentaje}%</span></div>`;
    }

    function pintarCertificaciones(resumen) {
        const contenedor = document.getElementById('lista-certificaciones');
        if (!contenedor) return;
        contenedor.innerHTML = `
            <div class="d-flex justify-content-between mb-2 extra-small"><span>Emitidos</span><strong class="text-success">${resumen.certificados_emitidos}</strong></div>
            <div class="d-flex justify-content-between mb-2 extra-small"><span>Pendientes</span><strong class="text-warning">${resumen.certificados_pendientes}</strong></div>
            <div class="d-flex justify-content-between extra-small"><span>Total de inscritos</span><strong>${resumen.ventas_realizadas}</strong></div>`;
    }

    let chartVentas = null;
    function pintarChartIngresos(datos) {
        const canvas = document.getElementById('chart_ingresos');
        if (!canvas || typeof Chart === 'undefined') return;

        const etiquetas = datos.map(d => d.fecha);
        const ingresos = datos.map(d => Number(d.total));

        const mensajeVacio = document.getElementById('mensaje-chart-ingresos');
        if (datos.length === 0) {
            canvas.style.display = 'none';
            if (mensajeVacio) mensajeVacio.classList.remove('d-none');
            return;
        }

        canvas.style.display = '';
        if (mensajeVacio) mensajeVacio.classList.add('d-none');

        if (chartVentas) {
            chartVentas.data.labels = etiquetas;
            chartVentas.data.datasets[0].data = ingresos;
            chartVentas.update();
            return;
        }

        chartVentas = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: etiquetas,
                datasets: [
                    { label: 'Ingresos estimados', data: ingresos, backgroundColor: '#d9383a', borderRadius: 6, maxBarThickness: 38 }
                ]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: context => ` ${Number(context.raw).toLocaleString('es-PE', { style: 'currency', currency: 'PEN' })}`
                        }
                    }
                }
            }
        });
    }

    function cargarResumenDashboard() {
        const params = new URLSearchParams({ c: 'dashboard', a: 'resumen' });
        const fechaDesde = document.getElementById('fecha_desde')?.value;
        const fechaHasta = document.getElementById('fecha_hasta')?.value;
        if (fechaDesde) params.set('fecha_desde', fechaDesde);
        if (fechaHasta) params.set('fecha_hasta', fechaHasta);

        fetch(`?${params.toString()}`, { cache: 'no-store' })
            .then(res => res.json())
            .then(result => {
                if (!result.success) {
                    mostrarErrorDashboard(result.message || 'No fue posible cargar el resumen.');
                    return;
                }
                pintarKpis(result.data.resumen);
                pintarListaConteo('lista-publicacion', result.data.publicacion, ETIQUETAS_PUBLICACION, COLOR_PUBLICACION);
                pintarAsistencia(result.data.resumen);
                pintarCertificaciones(result.data.resumen);
                pintarChartIngresos(result.data.ingresos_por_fecha);
            })
            .catch(() => mostrarErrorDashboard('No fue posible conectar con el servidor.'));
    }

    function mostrarErrorDashboard(mensaje) {
        ['lista-publicacion', 'lista-asistencia', 'lista-certificaciones'].forEach(id => {
            const contenedor = document.getElementById(id);
            if (contenedor) contenedor.innerHTML = `<p class="text-danger extra-small mb-0">${mensaje}</p>`;
        });
    }

    $('#filtro-dashboard').on('submit', function(event) {
        event.preventDefault();
        cargarResumenDashboard();
    });

    function establecerMesActual() {
        const ahora = new Date();
        const primerDia = new Date(ahora.getFullYear(), ahora.getMonth(), 1);
        const ultimoDia = new Date(ahora.getFullYear(), ahora.getMonth() + 1, 0);
        const formato = fecha => `${fecha.getFullYear()}-${String(fecha.getMonth() + 1).padStart(2, '0')}-${String(fecha.getDate()).padStart(2, '0')}`;
        document.getElementById('fecha_desde').value = formato(primerDia);
        document.getElementById('fecha_hasta').value = formato(ultimoDia);
    }

    $('#limpiar-filtro-dashboard').on('click', function() {
        establecerMesActual();
        cargarResumenDashboard();
    });

    establecerMesActual();
    cargarResumenDashboard();
});