document.addEventListener('DOMContentLoaded', () => {
    initCarousel();
    initHashRouter();
    initFormParticipanteGlobal();
    window.addEventListener('hashchange', initHashRouter);
});

function initHashRouter() {
    const hash = window.location.hash || '#';

    document.querySelectorAll('.landing-view').forEach(view => {
        view.style.display = 'none';
    });

    document.querySelectorAll('.landing-nav a').forEach(a => a.classList.remove('active'));

    if (hash === '#' || hash === '' || hash === '#inicio') {
        document.getElementById('view-inicio').style.display = 'block';
        document.getElementById('nav-inicio')?.classList.add('active');
    } 
    else if (hash.startsWith('#eventos')) {
        document.getElementById('view-eventos').style.display = 'block';
        document.getElementById('nav-eventos')?.classList.add('active');
    } 
    else if (hash === '#nosotros') {
        document.getElementById('view-nosotros').style.display = 'block';
        document.getElementById('nav-nosotros')?.classList.add('active');
    } 
    else if (hash.startsWith('#detalle=')) {
        const id = hash.split('=')[1];
        document.getElementById('view-detalle').style.display = 'block';
        loadEventDetail(id);
    }
}

function initFormParticipanteGlobal() {
    const form = document.getElementById('formParticipanteGlobal');
    if (!form) return;

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(form);

        fetch(`${BASE_URL}/?c=LandingPage&a=registrarParticipante`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire('¡Registrado!', data.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Atención', data.message, 'warning');
            }
        })
        .catch(() => Swal.fire('Error', 'Surgió un error al procesar el registro.', 'error'));
    });
}

function handleCredentialResponse(response) {
    const formData = new FormData();
    formData.append('credential', response.credential);

    fetch(`${BASE_URL}/?c=LandingPage&a=loginGoogle`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            if (res.registrado) {
                Swal.fire('¡Bienvenido!', res.message, 'success').then(() => location.reload());
            } else {
                // Pre-completar formulario para requerir DNI obligatoriamente
                document.getElementById('part_correo').value = res.data.correo || '';
                document.getElementById('part_nombres').value = res.data.nombres || '';
                document.getElementById('part_apellidos').value = res.data.apellidos || '';
                document.getElementById('part_google_id').value = res.data.google_id || '';

                Swal.fire('Por favor ingresa tu Documento de Identidad', 'Para completar tu registro requerimos tu DNI/CE.', 'info');
            }
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    });
}

function cerrarSesionLanding() {
    fetch(`${BASE_URL}/?c=LandingPage&a=logoutLanding`)
        .then(() => location.reload());
}

function loadEventDetail(id) {
    const container = document.getElementById('detalleContent');
    if (!container) return;

    container.innerHTML = `
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando evento...</p>
        </div>
    `;

    fetch(`${BASE_URL}/?c=LandingPage&a=getDetalleAjax&id=${id}`)
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                renderEventDetailUI(response.data);
            } else {
                container.innerHTML = `
                    <div class="text-center p-4">
                        <p class="text-danger">${response.message}</p>
                        <a href="#eventos" class="landing-btn">Volver a Eventos</a>
                    </div>
                `;
            }
        })
        .catch(() => {
            container.innerHTML = `
                <div class="text-center p-4">
                    <p class="text-danger">Error de red al consultar la información.</p>
                    <a href="#eventos" class="landing-btn">Volver a Eventos</a>
                </div>
            `;
        });
}

function renderEventDetailUI(evento) {
    const container = document.getElementById('detalleContent');

    const doc   = (typeof USER_DATA !== 'undefined' && USER_DATA) ? (USER_DATA.documento_id || '') : '';
    const nom   = (typeof USER_DATA !== 'undefined' && USER_DATA) ? (USER_DATA.nombres || '') : '';
    const ape   = (typeof USER_DATA !== 'undefined' && USER_DATA) ? (USER_DATA.apellidos || '') : '';
    const email = (typeof USER_DATA !== 'undefined' && USER_DATA) ? (USER_DATA.correo || '') : '';
    const tel   = (typeof USER_DATA !== 'undefined' && USER_DATA) ? (USER_DATA.telefono || '') : '';

    const precioNum = parseFloat(evento.precio);
    const esDePago = precioNum > 0;
    const estaBloqueado = evento.es_pasado || evento.cupos_disponibles <= 0;

    let mensajeEstado = '';
    if (evento.es_pasado) {
        mensajeEstado = '<div class="alert alert-warning text-center">El evento ha finalizado.</div>';
    } else if (evento.cupos_disponibles <= 0) {
        mensajeEstado = '<div class="alert alert-danger text-center">Cupos agotados.</div>';
    }

    container.innerHTML = `
        <a href="#eventos" class="landing-btn" style="margin-bottom: 1.5rem; background: #e0e0e0; color: #333;">
            &larr; Volver a Eventos
        </a>
        
        <div class="event-card" style="max-width: 850px; margin: 0 auto; height: auto;">
            <div class="event-card-img" style="height: 300px;">
                <img src="${evento.imagen ? evento.imagen : 'assets/img/default-event.jpg'}" alt="${evento.titulo}">
                <span class="event-badge">${evento.tema}</span>
            </div>
            <div class="event-card-body" style="padding: 2rem;">
                <div class="event-date" style="font-size: 1rem;">
                    <i class="far fa-calendar-alt"></i> ${evento.fecha_evento} &nbsp;|&nbsp; 
                    <i class="fas fa-map-marker-alt"></i> ${evento.direccion}
                </div>
                <h1 style="color: var(--color-primary-dark); margin: 0.5rem 0;">${evento.titulo}</h1>
                <p style="font-size: 1rem; color: var(--color-text-muted); margin: 1rem 0;">${evento.descripcion}</p>
                <div style="font-size: 0.95rem; margin-bottom: 1.5rem; color: var(--color-primary-main);">
                    <strong>Cupos Disponibles:</strong> ${evento.cupos_disponibles} de ${evento.cupos}
                </div>

                ${mensajeEstado}

                ${!estaBloqueado ? `
                    <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid var(--color-border);">
                    <h3 class="mb-3">Formulario de Inscripción al Evento</h3>
                    
                    <form id="registrationForm">
                        <input type="hidden" name="evento_id" value="${evento.id}">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Documento de Identidad (*)</label>
                                <input type="text" class="form-control" name="documento_id" value="${doc}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo Electrónico (*)</label>
                                <input type="email" class="form-control" name="correo" value="${email}" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombres (*)</label>
                                <input type="text" class="form-control" name="nombres" value="${nom}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos (*)</label>
                                <input type="text" class="form-control" name="apellidos" value="${ape}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" value="${tel}">
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <span class="event-price" style="font-size: 1.6rem; font-weight: bold;">
                                ${esDePago ? 'S/ ' + precioNum.toFixed(2) : 'Gratis'}
                            </span>
                            
                            ${!esDePago ? `
                                <button type="submit" class="landing-btn landing-btn-primary">Confirmar Inscripción Gratuita</button>
                            ` : `
                                <div id="paypal-button-container" style="width: 250px;"></div>
                            `}
                        </div>
                    </form>
                ` : ''}
            </div>
        </div>
    `;

    const form = document.getElementById('registrationForm');

    if (form && !esDePago) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            enviarInscripcion(new FormData(form));
        });
    }

    if (form && esDePago && window.paypal) {
        paypal.Buttons({
            createOrder: (data, actions) => {
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return actions.reject();
                }

                const montoUSD = (precioNum / 3.70).toFixed(2);

                return actions.order.create({
                    purchase_units: [{
                        amount: { value: montoUSD }
                    }]
                });
            },
            onApprove: (data, actions) => {
                return actions.order.capture().then(() => {
                    enviarInscripcion(new FormData(form));
                });
            },
            onError: () => {
                Swal.fire('Error', 'No se pudo completar el pago con PayPal.', 'error');
            }
        }).render('#paypal-button-container');
    }
}

function enviarInscripcion(formData) {
    Swal.fire({
        title: 'Procesando...',
        text: 'Guardando datos del registro...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch(`${BASE_URL}/?c=LandingPage&a=procesarInscripcion`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire('¡Registro Completado!', data.message, 'success').then(() => {
                window.location.hash = '#eventos';
            });
        } else {
            Swal.fire('Atención', data.message, 'warning');
        }
    })
    .catch(() => {
        Swal.fire('Error', 'Surgió un error de comunicación con el servidor.', 'error');
    });
}

function initCarousel() {
    const track = document.getElementById('carouselTrack');
    const prevBtn = document.getElementById('carouselPrev');
    const nextBtn = document.getElementById('carouselNext');

    if (!track || !prevBtn || !nextBtn) return;

    const cards = track.querySelectorAll('.event-card');
    if (cards.length === 0) return;

    let currentIndex = 0;

    function getVisibleCards() {
        if (window.innerWidth >= 992) return 3;
        if (window.innerWidth >= 600) return 2;
        return 1;
    }

    function updateCarouselPosition() {
        const visibleCards = getVisibleCards();
        const maxIndex = Math.max(0, cards.length - visibleCards);
        if (currentIndex > maxIndex) currentIndex = maxIndex;

        const cardWidth = cards[0].offsetWidth + 20;
        track.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
    }

    nextBtn.addEventListener('click', () => {
        const visibleCards = getVisibleCards();
        const maxIndex = Math.max(0, cards.length - visibleCards);
        if (currentIndex < maxIndex) {
            currentIndex++;
            updateCarouselPosition();
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex--;
            updateCarouselPosition();
        }
    });

    window.addEventListener('resize', updateCarouselPosition);
}