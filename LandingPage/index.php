<?php
$proximosEventos = $proximosEventos ?? [];
$todosEventos    = $todosEventos ?? [];
$usuario         = $usuario ?? ($_SESSION['participante_landing'] ?? null);
$baseUrl         = defined('BASE_URL') ? BASE_URL : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evenrity | Plataforma Oficial de Eventos</title>
    
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/custom/views/landingpage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://www.paypal.com/sdk/js?client-id=TU_CLIENT_ID_PAYPAL&currency=USD"></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
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
                
                <?php if ($usuario): ?>
                    <div class="dropdown d-inline-block">
                        <button class="landing-btn landing-btn-primary dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-check"></i> <?php echo htmlspecialchars($usuario['nombres']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="cerrarSesionLanding()"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <button type="button" class="btn-auth-modal" onclick="abrirModalParticipante()">
                        <i class="fas fa-user-circle"></i> Ingresar / Registrarme
                    </button>
                <?php endif; ?>
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
                                            <img src="<?php echo !empty($ev['imagen']) ? $ev['imagen'] : $baseUrl . '/assets/img/default-event.jpg'; ?>" alt="<?php echo htmlspecialchars($ev['titulo']); ?>">
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

                <div class="events-grid">
                    <?php if (empty($todosEventos)): ?>
                        <div class="no-events-message w-100 text-center">
                            <i class="fas fa-calendar-times"></i>
                            <p>No hay eventos disponibles por el momento.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($todosEventos as $ev): ?>
                            <article class="event-card" data-id="<?php echo $ev['id']; ?>">
                                <div class="event-card-img">
                                    <img src="<?php echo !empty($ev['imagen']) ? $ev['imagen'] : $baseUrl . '/assets/img/default-event.jpg'; ?>" alt="<?php echo htmlspecialchars($ev['titulo']); ?>">
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

    <!-- MODAL DE REGISTRO / IDENTIFICACIÓN -->
    <div id="modalParticipante" class="modal-custom-overlay" style="display: none;">
        <div class="modal-custom-card">
            <div class="modal-custom-header">
                <small class="text-uppercase text-muted fw-bold">Identificación del Participante</small>
                <h4 class="m-0 font-weight-bold">REGISTRO / INGRESO</h4>
                <button type="button" class="btn-close-custom" onclick="cerrarModalParticipante()">&times;</button>
            </div>
            
            <div class="modal-custom-body">
                <!-- Google Sign-In -->
                <div class="text-center mb-3">
                    <p class="text-muted small mb-2">Ingresa rápidamente usando tu cuenta de Google:</p>
                    <div id="g_id_onload"
                         data-client_id="TU_CLIENT_ID_GOOGLE.apps.googleusercontent.com"
                         data-callback="handleCredentialResponse">
                    </div>
                    <div class="g_id_signin d-flex justify-content-center" data-type="standard" data-shape="rectangular" data-theme="outline" data-text="signin_with" data-size="large"></div>
                </div>

                <div class="divider-text my-3">
                    <span>O completa tus datos</span>
                </div>

                <!-- Formulario -->
                <form id="formParticipanteGlobal">
                    <input type="hidden" name="google_id" id="part_google_id">
                    
                    <div class="form-group-custom mb-3">
                        <label class="form-label-custom">Documento de Identidad (DNI/CE) *</label>
                        <input type="text" class="form-control-custom" name="documento_id" id="part_documento_id" maxlength="15" placeholder="00000000" required>
                    </div>

                    <div class="row-custom mb-3">
                        <div class="col-custom">
                            <label class="form-label-custom">Nombres *</label>
                            <input type="text" class="form-control-custom" name="nombres" id="part_nombres" required>
                        </div>
                        <div class="col-custom">
                            <label class="form-label-custom">Apellidos *</label>
                            <input type="text" class="form-control-custom" name="apellidos" id="part_apellidos" required>
                        </div>
                    </div>

                    <div class="form-group-custom mb-3">
                        <label class="form-label-custom">Correo Electrónico *</label>
                        <input type="email" class="form-control-custom" name="correo" id="part_correo" placeholder="correo@ejemplo.com" required>
                    </div>

                    <div class="form-group-custom mb-4">
                        <label class="form-label-custom">Teléfono / Celular</label>
                        <input type="text" class="form-control-custom" name="telefono" id="part_telefono" placeholder="999888777">
                    </div>

                    <div class="modal-custom-footer">
                        <button type="button" class="btn-modal-cancel" onclick="cerrarModalParticipante()">Cancelar</button>
                        <button type="submit" class="btn-modal-submit"><i class="fas fa-check-circle"></i> Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="landing-footer">
        <div class="landing-footer-inner">
            <div class="landing-footer-brand">Evenrity</div>
            <p>&copy; <?php echo date('Y'); ?> Evenrity. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- SCRIPTS FÍSICOS GLOBALES -->
    <script src="<?php echo $baseUrl; ?>/assets/default/default.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/jquery.min.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $baseUrl; ?>/assets/js/sweetalert2.all.min.js"></script>

    <!-- SCRIPT LOCAL DE LANDINGPAGE -->
    <script>
        const USER_DATA = <?php echo json_encode($usuario); ?>;
        const BASE_URL  = "<?php echo $baseUrl; ?>";
    </script>
    <script src="<?php echo $baseUrl; ?>/LandingPage/landingPage.js"></script>
</body>
</html>