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
         * Solamente eventos:
         *
         * - activos
         * - aprobados
         *
         * No se muestran pendientes ni rechazados.
         */
        

        $title = 'Evenrity';

        require_once __DIR__ . '/../LandingPage/views/inicio/inicio.php';
    }



}