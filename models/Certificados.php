<?php

declare(strict_types=1);

class Certificados {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene participantes con asistencia confirmada (asistencia = 1)
     * pertenecientes a eventos ya finalizados.
     */
    public function getAll(?int $eventoId = null, string $estado = 'TODOS'): array {
        $sql = "
            SELECT 
                ep.id, 
                ep.evento_id, 
                ep.documento_id, 
                ep.nombres, 
                ep.apellidos, 
                ep.correo, 
                ep.telefono, 
                ep.asistencia, 
                ep.fecha_asistencia, 
                ep.certificado_emitido, 
                ep.fecha_emision_certificado, 
                ep.created_at,
                e.titulo AS evento_titulo,
                e.fecha_evento AS evento_fecha
            FROM entradas_participantes ep
            INNER JOIN eventos e ON ep.evento_id = e.id
            WHERE ep.asistencia = 1 
              AND e.fecha_evento <= CURRENT_DATE()
        ";

        $params = [];

        if ($eventoId !== null && $eventoId > 0) {
            $sql .= " AND ep.evento_id = :evento_id";
            $params[':evento_id'] = $eventoId;
        }

        $estadoUpper = strtoupper(trim($estado));
        if ($estadoUpper === 'EMITIDO') {
            $sql .= " AND ep.certificado_emitido = 1";
        } elseif ($estadoUpper === 'PENDIENTE') {
            $sql .= " AND ep.certificado_emitido = 0";
        }

        $sql .= " ORDER BY ep.apellidos ASC, ep.nombres ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los datos detallados del participante con el JOIN del evento para el PDF.
     */
    public function getById(int $id): array|false {
        $sql = "
            SELECT 
                ep.id, 
                ep.evento_id, 
                ep.documento_id, 
                ep.nombres, 
                ep.apellidos, 
                ep.correo, 
                ep.telefono, 
                ep.asistencia, 
                ep.fecha_asistencia, 
                ep.certificado_emitido, 
                ep.fecha_emision_certificado, 
                e.titulo AS evento_titulo,
                e.fecha_evento AS evento_fecha
            FROM entradas_participantes ep
            INNER JOIN eventos e ON ep.evento_id = e.id
            WHERE ep.id = ? AND ep.asistencia = 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza únicamente los campos de emisión del certificado.
     */
    public function marcarEmitido(int $id): bool {
        $stmt = $this->pdo->prepare("
            UPDATE entradas_participantes
            SET 
                certificado_emitido = 1,
                fecha_emision_certificado = NOW()
            WHERE id = :id AND asistencia = 1
        ");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Obtiene los eventos finalizados que cuentan con participantes asistentes.
     */
    public function getEventosFinalizados(): array {
        $sql = "
            SELECT DISTINCT e.id, e.titulo, e.fecha_evento
            FROM eventos e
            INNER JOIN entradas_participantes ep ON e.id = ep.evento_id
            WHERE ep.asistencia = 1 AND e.fecha_evento <= CURRENT_DATE()
            ORDER BY e.fecha_evento DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Estadísticas rápidas para los KPIs de la vista.
     */
    public function getEstadisticasEvento(?int $eventoId = null): array {
        $sql = "
            SELECT 
                COUNT(*) AS total_asistentes,
                SUM(CASE WHEN ep.certificado_emitido = 1 THEN 1 ELSE 0 END) AS total_emitidos,
                SUM(CASE WHEN ep.certificado_emitido = 0 THEN 1 ELSE 0 END) AS total_pendientes
            FROM entradas_participantes ep
            INNER JOIN eventos e ON ep.evento_id = e.id
            WHERE ep.asistencia = 1 AND e.fecha_evento <= CURRENT_DATE()
        ";

        $params = [];
        if ($eventoId !== null && $eventoId > 0) {
            $sql .= " AND ep.evento_id = :evento_id";
            $params[':evento_id'] = $eventoId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_asistentes' => (int)($stats['total_asistentes'] ?? 0),
            'total_emitidos'   => (int)($stats['total_emitidos'] ?? 0),
            'total_pendientes' => (int)($stats['total_pendientes'] ?? 0)
        ];
    }
}