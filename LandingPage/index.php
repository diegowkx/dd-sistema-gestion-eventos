<?php
$proximosEventos = $proximosEventos ?? [];
$todosEventos    = $todosEventos ?? [];
$baseUrl         = defined('BASE_URL') ? BASE_URL : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evenrity | Plataforma Oficial de Eventos</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/favicon.png">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/custom/views/landingpage.css">
</head>
<body class="landing-body">

    <!-- NAVBAR -->
    <header class="landing-navbar">
        <div class="landing-navbar-inner">
            <a href="#" class="landing-brand">
                Evenrity
                <span class="landing-brand-subtitle">Plataforma Oficial</span>
            </a>
            <nav class="landing-nav">
                <a href="#" id="nav-inicio" class="active">Inicio</a>
                <a href="#eventos" id="nav-eventos">Eventos</a>
                <a href="#nosotros" id="nav-nosotros">Nosotros</a>
                
            </nav>
        </div>
    </header>

    <!-- INICIO -->
    <main id="view-inicio" class="landing-view">
        <section class="landing-hero">
            <div class="landing-hero-inner">
                <div class="landing-hero-content">
                    <span class="landing-hero-eyebrow">Evenrity Events</span>
                    <h1>Conecta con los mejores <span>eventos y conferencias.</span></h1>
                    <p class="landing-hero-description">
                        Explora capacitaciones, seminarios y encuentros académicos. Asegura tu participación de forma rápida.
                    </p>
                    <a href="#eventos" class="landing-btn landing-btn-primary">
                        <i class="fas fa-calendar-alt"></i> Ver Eventos Disponibles
                    </a>
                </div>
            </div>
        </section>

        <section class="landing-events-section">
            <div class="landing-container">
                <div class="section-header text-center">
                    <h2>Eventos Destacados</h2>
                    <p>Próximas actividades a desarrollarse</p>
                </div>

                <div class="carousel-container">
                    <button class="carousel-control prev" id="carouselPrev"><i class="fas fa-chevron-left"></i></button>
                    <div class="carousel-track-wrapper">
                        <div class="carousel-track" id="carouselTrack">
                            <?php if (empty($proximosEventos)): ?>
                                <div class="no-events-message w-100 text-center">
                                    <p>No hay eventos próximos destacados en este momento.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($proximosEventos as $ev): ?>
                                    <article class="event-card" data-id="<?php echo $ev['id']; ?>">
                                        <div class="event-card-img">
                                            <img src="<?php echo !empty($ev['imagen']) ? $ev['imagen'] : $baseUrl . '/assets/images/logo-image.png'; ?>" alt="<?php echo htmlspecialchars($ev['titulo']); ?>">
                                            <span class="event-badge"><?php echo htmlspecialchars($ev['tema']); ?></span>
                                        </div>
                                        <div class="event-card-body">
                                            <div class="event-date">
                                                <i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y h:i A', strtotime($ev['fecha_evento'])); ?>
                                            </div>
                                            <h3 class="event-title"><?php echo htmlspecialchars($ev['titulo']); ?></h3>
                                            <p class="event-description"><?php echo htmlspecialchars($ev['descripcion']); ?></p>
                                            <div class="event-footer">
                                                <span class="event-price">
                                                    <?php echo $ev['precio'] > 0 ? 'S/ ' . number_format((float)$ev['precio'], 2) : 'Gratis'; ?>
                                                </span>
                                                <a href="#detalle=<?php echo $ev['id']; ?>" class="btn-card-details">Ver detalle &rarr;</a>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button class="carousel-control next" id="carouselNext"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </section>
    </main>

    <!-- CATÁLOGO -->
    <main id="view-eventos" class="landing-view" style="display: none;">
        <section class="landing-events-section">
            <div class="landing-container">
                <div class="section-header text-center">
                    <h2>Catálogo de Eventos</h2>
                    <p>Todos los seminarios, talleres y conferencias vigentes</p>
                </div>

                <div class="events-filter-bar" role="search">
                    <div class="filter-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label class="visually-hidden" for="eventSearch">Buscar eventos</label>
                        <input type="search" id="eventSearch" placeholder="Buscar por título, tema o descripción" autocomplete="off">
                    </div>
                    <div class="filter-date">
                        <label for="eventDateFrom">Desde</label>
                        <input type="date" id="eventDateFrom">
                    </div>
                    <div class="filter-date">
                        <label for="eventDateTo">Hasta</label>
                        <input type="date" id="eventDateTo">
                    </div>
                    <button type="button" class="filter-clear" id="clearEventFilters" title="Limpiar filtros">
                        <i class="fas fa-rotate-left" aria-hidden="true"></i>
                        <span>Limpiar</span>
                    </button>
                </div>

                <div class="events-grid" id="eventsGrid">
                    <?php if (empty($todosEventos)): ?>
                        <div class="no-events-message w-100 text-center" id="eventsEmptyState">
                            <i class="fas fa-calendar-times"></i>
                            <p>No hay eventos disponibles por el momento.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($todosEventos as $ev): ?>
                            <article class="event-card event-list-card"
                                     data-id="<?php echo $ev['id']; ?>"
                                     data-search="<?php echo htmlspecialchars(strtolower($ev['titulo'] . ' ' . $ev['tema'] . ' ' . $ev['descripcion'] . ' ' . ($ev['direccion'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>"
                                     data-date="<?php echo date('Y-m-d', strtotime($ev['fecha_evento'])); ?>">
                                <div class="event-card-img">
                                            <img src="<?php echo !empty($ev['imagen']) ? $ev['imagen'] : $baseUrl . '/assets/images/logo-image.png'; ?>" alt="<?php echo htmlspecialchars($ev['titulo']); ?>">
                                    <span class="event-badge"><?php echo htmlspecialchars($ev['tema']); ?></span>
                                </div>
                                <div class="event-card-body">
                                    <div class="event-date">
                                        <i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y h:i A', strtotime($ev['fecha_evento'])); ?>
                                    </div>
                                    <h3 class="event-title"><?php echo htmlspecialchars($ev['titulo']); ?></h3>
                                    <p class="event-description"><?php echo htmlspecialchars($ev['descripcion']); ?></p>
                                    <div class="event-footer">
                                        <span class="event-price">
                                            <?php echo $ev['precio'] > 0 ? 'S/ ' . number_format((float)$ev['precio'], 2) : 'Gratis'; ?>
                                        </span>
                                        <a href="#detalle=<?php echo $ev['id']; ?>" class="btn-card-details">Ver detalle &rarr;</a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="no-events-message filter-no-results" id="filterNoResults" hidden>
                    <i class="fas fa-filter-circle-xmark"></i>
                    <p>No encontramos eventos con esos criterios.</p>
                </div>
            </div>
        </section>
    </main>

    <!-- DETALLE -->
    <main id="view-detalle" class="landing-view" style="display: none;">
        <section class="landing-events-section">
            <div class="landing-container" id="detalleContent"></div>
        </section>
    </main>

    <!-- NOSOTROS -->
    <main id="view-nosotros" class="landing-view" style="display: none;">
        <section class="landing-hero">
            <div class="landing-container text-center">
                <h2>Sobre Evenrity</h2>
                <p>Plataforma para la gestión integral de eventos académicos.</p>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="landing-footer">
        <div class="landing-footer-inner">
            <div class="landing-footer-brand">Evenrity</div>
            <p>&copy; <?php echo date('Y'); ?> Evenrity. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- SCRIPTS FÍSICOS GLOBALES -->
    <script src="<?php echo $baseUrl; ?>/assets/default/default.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/config.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/jquery.min.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/sweetalert2.all.min.js"></script>

    <!-- SCRIPT LOCAL DE LANDINGPAGE -->
    <script>
        const BASE_URL  = "<?php echo $baseUrl; ?>";
    </script>
    <script src="<?php echo $baseUrl; ?>/LandingPage/landingPage.js"></script>
</body>
</html>