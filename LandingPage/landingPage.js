document.addEventListener('DOMContentLoaded', () => {
    initCarousel();
    initEventFilters();
    initHashRouter();
    window.addEventListener('hashchange', initHashRouter);
});

function initEventFilters() {
    const searchInput = document.getElementById('eventSearch');
    const dateFromInput = document.getElementById('eventDateFrom');
    const dateToInput = document.getElementById('eventDateTo');
    const clearButton = document.getElementById('clearEventFilters');
    const cards = Array.from(document.querySelectorAll('.event-list-card'));
    const noResults = document.getElementById('filterNoResults');

    if (!searchInput || !dateFromInput || !dateToInput || !clearButton) return;

    function applyFilters() {
        const search = searchInput.value.trim().toLowerCase();
        const dateFrom = dateFromInput.value;
        const dateTo = dateToInput.value;
        let visibleCards = 0;

        cards.forEach(card => {
            const matchesSearch = !search || card.dataset.search.includes(search);
            const eventDate = card.dataset.date;
            const matchesFrom = !dateFrom || eventDate >= dateFrom;
            const matchesTo = !dateTo || eventDate <= dateTo;
            const isVisible = matchesSearch && matchesFrom && matchesTo;

            card.hidden = !isVisible;
            if (isVisible) visibleCards++;
        });

        if (noResults) noResults.hidden = visibleCards > 0 || cards.length === 0;
    }

    searchInput.addEventListener('input', applyFilters);
    dateFromInput.addEventListener('change', applyFilters);
    dateToInput.addEventListener('change', applyFilters);
    clearButton.addEventListener('click', () => {
        searchInput.value = '';
        dateFromInput.value = '';
        dateToInput.value = '';
        applyFilters();
    });
}

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

function loadEventDetail(id) {
    const container = document.getElementById('detalleContent');
    if (!container) return;

    container.innerHTML = `
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando evento...</p>
        </div>
    `;

    fetch(`${BASE_URL}/?c=landing_page&a=getDetalleAjax&id=${id}`)
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

    const estaBloqueado = evento.es_pasado || evento.cupos_disponibles <= 0;
    const imagenes = Array.isArray(evento.imagenes) && evento.imagenes.length
        ? evento.imagenes.filter(Boolean)
        : [evento.imagen].filter(Boolean);
    const imagenInicial = imagenes[0] || `${BASE_URL}/assets/images/logo-image.png`;

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
        
        <article class="event-detail-layout">
            <div class="event-detail-gallery">
                <div class="event-detail-image-wrap">
                    <img id="detailGalleryImage" src="${imagenInicial}" alt="${evento.titulo}">
                    <span class="event-badge">${evento.tema}</span>
                    ${imagenes.length > 1 ? `
                        <button type="button" class="detail-gallery-control prev" id="detailGalleryPrev" aria-label="Imagen anterior">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="detail-gallery-control next" id="detailGalleryNext" aria-label="Imagen siguiente">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    ` : ''}
                </div>
                ${imagenes.length > 1 ? `
                    <div class="detail-gallery-footer">
                        <div class="detail-gallery-dots" id="detailGalleryDots">
                            ${imagenes.map((_, index) => `<button type="button" class="detail-gallery-dot${index === 0 ? ' active' : ''}" data-index="${index}" aria-label="Ver imagen ${index + 1}"></button>`).join('')}
                        </div>
                        <span class="detail-gallery-counter" id="detailGalleryCounter">1 / ${imagenes.length}</span>
                    </div>
                ` : ''}
            </div>

            <div class="event-detail-content">
                <div class="event-date">
                    <i class="far fa-calendar-alt"></i> ${evento.fecha_evento} &nbsp;|&nbsp; 
                    <i class="fas fa-map-marker-alt"></i> ${evento.direccion}
                </div>
                <h1>${evento.titulo}</h1>
                <p class="event-detail-description">${evento.descripcion}</p>
                <div class="event-detail-availability">
                    <strong>Cupos Disponibles:</strong> ${evento.cupos_disponibles} de ${evento.cupos}
                </div>

                ${mensajeEstado}

                ${!estaBloqueado ? `
                    <div class="event-detail-registration">
                        <h2>Formulario de inscripción</h2>

                        <form id="registrationForm">
                            <input type="hidden" name="evento_id" value="${evento.id}">

                            <!-- Fila 1: Datos personales básicos -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombres (*)</label>
                                    <input type="text" class="form-control" name="nombres" required placeholder="Ej. Juan">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Apellidos (*)</label>
                                    <input type="text" class="form-control" name="apellidos" required placeholder="Ej. Pérez">
                                </div>
                            </div>

                            <!-- Fila 2: Identificación y Teléfono (Misma fila para balancear) -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Doc. Identidad (*)</label>
                                    <input type="text" class="form-control" name="documento_id" maxlength="15" required placeholder="DNI / Pasaporte">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" name="telefono" maxlength="15" placeholder="Ej. 987654321">
                                </div>
                            </div>

                            <!-- Fila 3: Correo a todo lo ancho al final (Contacto principal) -->
                            <div class="mb-4">
                                <label class="form-label">Correo Electrónico (*)</label>
                                <input type="email" class="form-control" name="correo" required placeholder="nombre@correo.com">
                            </div>

                            <div class="event-detail-submit">
                                <span class="event-price">
                                    ${Number(evento.precio) > 0 ? 'S/ ' + Number(evento.precio).toFixed(2) : 'Gratis'}
                                </span>

                                <button type="submit" class="landing-btn landing-btn-primary">Confirmar Inscripción</button>
                            </div>
                        </form>
                    </div>
                ` : ''}
            </div>
        </article>
    `;

    if (imagenes.length > 1) {
        initDetailGallery(imagenes, evento.titulo);
    }

    const form = document.getElementById('registrationForm');

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            enviarInscripcion(new FormData(form));
        });
    }

}

function enviarInscripcion(formData) {
    Swal.fire({
        title: 'Procesando...',
        text: 'Guardando datos del registro...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch(`${BASE_URL}/?c=landing_page&a=procesarInscripcion`, {
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

function initDetailGallery(imagenes, titulo) {
    const image = document.getElementById('detailGalleryImage');
    const counter = document.getElementById('detailGalleryCounter');
    const dots = document.querySelectorAll('.detail-gallery-dot');
    let currentIndex = 0;
    let timer;

    function showImage(index) {
        currentIndex = (index + imagenes.length) % imagenes.length;
        image.src = imagenes[currentIndex];
        image.alt = `${titulo} - imagen ${currentIndex + 1}`;
        counter.textContent = `${currentIndex + 1} / ${imagenes.length}`;
        dots.forEach((dot, dotIndex) => dot.classList.toggle('active', dotIndex === currentIndex));
    }

    function restartAutoPlay() {
        window.clearInterval(timer);
        timer = window.setInterval(() => showImage(currentIndex + 1), 5000);
    }

    document.getElementById('detailGalleryPrev')?.addEventListener('click', () => {
        showImage(currentIndex - 1);
        restartAutoPlay();
    });
    document.getElementById('detailGalleryNext')?.addEventListener('click', () => {
        showImage(currentIndex + 1);
        restartAutoPlay();
    });
    dots.forEach(dot => dot.addEventListener('click', () => {
        showImage(Number(dot.dataset.index));
        restartAutoPlay();
    }));
    restartAutoPlay();
}