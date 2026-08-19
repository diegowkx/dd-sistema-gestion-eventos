<?php

declare(strict_types=1);

class DashboardDefault {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    
    public function getDatosGeneralesDefault(): array {
        $stmt = $this->pdo->query("
            SELECT
                COALESCE(SUM(publicacion = 'APROBADO'), 0) AS eventos_publicados,
                COALESCE(SUM(publicacion = 'PENDIENTE'), 0) AS eventos_pendientes,
                COALESCE(SUM(publicacion = 'RECHAZADO'), 0) AS eventos_rechazados,
                COUNT(*) AS eventos_totales
            FROM eventos
            WHERE activo = 1
        ");
        $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        foreach (['eventos_totales', 'eventos_publicados', 'eventos_pendientes', 'eventos_rechazados'] as $field) {
            $data[$field] = (int)($data[$field] ?? 0);
        }
        return $data;
    }
}