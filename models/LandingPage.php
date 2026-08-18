<?php

declare(strict_types=1);

class LandingPage {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getProximosEventos(): array {
        $sql = "SELECT e.*, 
                       (SELECT ruta FROM imagenes_evento WHERE evento_id = e.id AND eliminacion = 0 ORDER BY orden ASC LIMIT 1) as imagen
                FROM eventos e
                WHERE e.publicacion = 'APROBADO' 
                  AND e.activo = 1 
                  AND e.fecha_evento >= NOW()
                ORDER BY e.fecha_evento ASC 
                LIMIT 9";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTodosEventos(): array {
        $sql = "SELECT e.*, 
                       (SELECT ruta FROM imagenes_evento WHERE evento_id = e.id AND eliminacion = 0 ORDER BY orden ASC LIMIT 1) as imagen
                FROM eventos e
                WHERE e.publicacion = 'APROBADO' 
                  AND e.activo = 1 
                  AND e.fecha_evento >= NOW()
                ORDER BY e.fecha_evento ASC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventoPorId(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT e.*, 
                   (SELECT ruta FROM imagenes_evento WHERE evento_id = e.id AND eliminacion = 0 ORDER BY orden ASC LIMIT 1) as imagen
            FROM eventos e
            WHERE e.id = ? AND e.publicacion = 'APROBADO' AND e.activo = 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerParticipantePorCorreoOGoogleId(string $correo, string $googleId): array|false {
        $stmt = $this->pdo->prepare("
            SELECT * FROM participantes 
            WHERE correo = ? OR (google_id IS NOT NULL AND google_id = ?)
            LIMIT 1
        ");
        $stmt->execute([strtolower(trim($correo)), $googleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardarOActualizarParticipanteGlobal(array $data): void {
        $sql = "INSERT INTO participantes (documento_id, nombres, apellidos, correo, telefono, google_id)
                VALUES (:documento_id, :nombres, :apellidos, :correo, :telefono, :google_id)
                ON DUPLICATE KEY UPDATE
                    nombres = VALUES(nombres),
                    apellidos = VALUES(apellidos),
                    telefono = COALESCE(VALUES(telefono), telefono),
                    google_id = COALESCE(VALUES(google_id), google_id)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':documento_id' => trim((string)$data['documento_id']),
            ':nombres'      => trim((string)$data['nombres']),
            ':apellidos'    => trim((string)$data['apellidos']),
            ':correo'       => strtolower(trim((string)$data['correo'])),
            ':telefono'     => !empty($data['telefono']) ? trim((string)$data['telefono']) : null,
            ':google_id'    => $data['google_id'] ?? null
        ]);
    }

    public function estaInscrito(int $eventoId, string $documentoId): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM entradas_participantes 
            WHERE evento_id = ? AND documento_id = ?
        ");
        $stmt->execute([$eventoId, trim($documentoId)]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getCuposDisponibles(int $eventoId, int $cuposTotales): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM entradas_participantes WHERE evento_id = ?");
        $stmt->execute([$eventoId]);
        $inscritos = (int)$stmt->fetchColumn();
        return max(0, $cuposTotales - $inscritos);
    }

    public function registrarEntradaInamovible(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO entradas_participantes (evento_id, documento_id, nombres, apellidos, correo, telefono)
            VALUES (:evento_id, :documento_id, :nombres, :apellidos, :correo, :telefono)
        ");
        return $stmt->execute([
            ':evento_id'    => (int)$data['evento_id'],
            ':documento_id' => trim((string)$data['documento_id']),
            ':nombres'      => trim((string)$data['nombres']),
            ':apellidos'    => trim((string)$data['apellidos']),
            ':correo'       => strtolower(trim((string)$data['correo'])),
            ':telefono'     => !empty($data['telefono']) ? trim((string)$data['telefono']) : null,
        ]);
    }
}