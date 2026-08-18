<?php

declare(strict_types=1);


$baseUrl = defined('BASE_URL')
    ? BASE_URL
    : '/dd-sistema-gestion-eventos';


function landingUrl(string $ruta): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($ruta, '/');
}




?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($title ?? 'Evenrity') ?>
    </title>

    <link
        rel="stylesheet"
        href="<?= htmlspecialchars($baseUrl) ?>/assets/css/custom/views/landingpage.css"
    >

</head>


<body class="landing-body">


<!-- =========================================================
     NAVBAR
     ========================================================= -->

<header class="landing-navbar">

    <div class="landing-navbar-inner">

        <a
            href="<?= htmlspecialchars($baseUrl) ?>"
            class="landing-brand"
        >

            Terra Sogno

            <span class="landing-brand-subtitle">
                Propiedades &amp; terrenos
            </span>

        </a>


        <nav class="landing-nav">

            <a href="<?= htmlspecialchars($baseUrl) ?>">
                Inicio
            </a>

            <a href="#propiedades">
                Propiedades
            </a>

            <a href="#contacto">
                Contáctanos
            </a>

            <a
                href="<?= htmlspecialchars($baseUrl) ?>?c=auth&a=login"
                class="landing-nav-contact"
            >
                Acceso
            </a>

        </nav>

    </div>

</header>


<!-- =========================================================
     HERO
     ========================================================= -->

<section class="landing-hero">

    <div class="landing-hero-inner">

        <div class="landing-hero-content">

            <span class="landing-hero-eyebrow">
                Terra Sogno
            </span>


            <h1>

                Encuentra el lugar
                <span>
                    donde comienza tu próxima historia.
                </span>

            </h1>


            <p class="landing-hero-description">

                Descubre propiedades y terrenos seleccionados
                para encontrar el espacio que mejor se adapte
                a tus planes, necesidades y sueños.

            </p>


            <div class="landing-hero-actions">

                <a
                    href="#propiedades"
                    class="landing-btn landing-btn-primary"
                >
                    Ver propiedades
                </a>


                <a
                    href="#contacto"
                    class="landing-btn landing-btn-outline"
                >
                    Quiero información
                </a>

            </div>

        </div>

    </div>

</section>



<!-- =========================================================
     FOOTER
     ========================================================= -->

<footer class="landing-footer">

    <div class="landing-footer-brand">
        Terra Sogno
    </div>


    <p>
        Propiedades y terrenos para nuevos comienzos.
    </p>


    <p class="landing-footer-copy">
        © <?= date('Y') ?> Terra Sogno.
        Todos los derechos reservados.
    </p>

</footer>


<script
    src="<?= htmlspecialchars($baseUrl) ?>/LandingPage/views/inicio/inicio.js"
></script>


</body>

</html>