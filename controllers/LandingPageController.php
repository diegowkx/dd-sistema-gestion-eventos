<?php

declare(strict_types=1);

class LandingPageController
{
    /**
     * Página principal pública.
     */
    public function index(PDO $pdo): void
    {
        require_once __DIR__ . '/../models/LandingPage.php';

        $model = new LandingPage($pdo);

        /*
         * Solamente propiedades:
         *
         * - activas
         * - aprobadas
         *
         * No se muestran PENDIENTES ni RECHAZADAS.
         */
        

        $title = 'Terra Sogno';

        require_once __DIR__ . '/../LandingPage/views/inicio.php';
    }



}