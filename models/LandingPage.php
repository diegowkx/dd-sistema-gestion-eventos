<?php

declare(strict_types=1);

class LandingPage
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


}