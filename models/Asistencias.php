<?php

declare(strict_types=1);

class Asistencias {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * SELECT_BASE con los campos exactos de la tabla 'entradas_participantes'.
     */
    private const SELECT_BASE = "
        SELECT 
            id, 
            evento_id, 
            documento_id, 
            nombres, 
            apellidos, 
            correo, 
            telefono, 
            asistencia, 
            fecha_asistencia, 
            operador_marcado_username, 
            certificado_emitido, 
            fecha_emision_certificado, 
            created_at, 
            updated_at
        FROM entradas_participantes
    ";

    public function getAll(?int $eventoId = null, string $estado = 'TODOS'): array {
        $sql = self::SELECT_BASE . " WHERE 1=1";
        $params = [];

        if ($eventoId !== null && $eventoId > 0) {
            $sql .= " AND evento_id = :evento_id";
            $params[':evento_id'] = $eventoId;
        }

        $estadoUpper = strtoupper(trim($estado));
        if ($estadoUpper === 'ASISTIO') {
            $sql .= " AND asistencia = 1";
        } elseif ($estadoUpper === 'PENDIENTE') {
            $sql .= " AND asistencia = 0";
        }

        $sql .= " ORDER BY apellidos ASC, nombres ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByEvento(int $eventoId, string $estado = 'TODOS'): array {
        return $this->getAll($eventoId, $estado);
    }

    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare(self::SELECT_BASE . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByEventoYDocumento(int $eventoId, string $documentoId): array|false {
        $stmt = $this->pdo->prepare(self::SELECT_BASE . " WHERE evento_id = ? AND documento_id = ?");
        $stmt->execute([$eventoId, trim($documentoId)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function marcar(int $id, string $operadorUsername): bool {
        $stmt = $this->pdo->prepare("
            UPDATE entradas_participantes
            SET 
                asistencia = 1,
                fecha_asistencia = NOW(),
                operador_marcado_username = :operador
            WHERE id = :id
        ");
        return $stmt->execute([
            ':operador' => $operadorUsername,
            ':id'       => $id
        ]);
    }

    /**
     * Desmarca la asistencia de un participante solo si aún no se ha emitido su certificado.
     */
    public function desmarcar(int $id): bool {
        $stmt = $this->pdo->prepare("
            UPDATE entradas_participantes
            SET 
                asistencia = 0,
                fecha_asistencia = NULL,
                operador_marcado_username = NULL
            WHERE id = :id AND certificado_emitido = 0
        ");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Busca y marca la asistencia directamente por número de documento en un evento.
     */
    public function marcarPorDocumento(int $eventoId, string $documentoId, string $operadorUsername): array {
        $participante = $this->getByEventoYDocumento($eventoId, $documentoId);
        if (!$participante) {
            return [
                'ok'      => false,
                'mensaje' => "El documento '{$documentoId}' no se encuentra inscrito en este evento.",
                'data'    => null
            ];
        }

        if ((int)$participante['asistencia'] === 1) {
            $fecha = !empty($participante['fecha_asistencia']) ? $participante['fecha_asistencia'] : '';
            return [
                'ok'      => false,
                'mensaje' => "La asistencia del participante ya fue registrada previamente ({$fecha}).",
                'data'    => $participante
            ];
        }

        $exito = $this->marcar((int)$participante['id'], $operadorUsername);
        if ($exito) {
            $participanteActualizado = $this->getById((int)$participante['id']);
            $nombreCompleto = trim($participante['nombres'] . ' ' . $participante['apellidos']);
            return [
                'ok'      => true,
                'mensaje' => "Asistencia registrada correctamente para {$nombreCompleto}.",
                'data'    => $participanteActualizado
            ];
        }

        return [
            'ok'      => false,
            'mensaje' => 'Error interno al registrar la asistencia.',
            'data'    => null
        ];
    }

    public function tieneCertificadoEmitido(int $id): bool {
        $stmt = $this->pdo->prepare("SELECT certificado_emitido FROM entradas_participantes WHERE id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() === 1;
    }

    public function getEstadisticasEvento(int $eventoId): array {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) AS total_inscritos,
                SUM(CASE WHEN asistencia = 1 THEN 1 ELSE 0 END) AS total_asistieron,
                SUM(CASE WHEN asistencia = 0 THEN 1 ELSE 0 END) AS total_pendientes,
                SUM(CASE WHEN certificado_emitido = 1 THEN 1 ELSE 0 END) AS total_certificados
            FROM entradas_participantes
            WHERE evento_id = ?
        ");
        $stmt->execute([$eventoId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $totalInscritos    = (int)($stats['total_inscritos'] ?? 0);
        $totalAsistieron   = (int)($stats['total_asistieron'] ?? 0);
        $totalPendientes   = (int)($stats['total_pendientes'] ?? 0);
        $totalCertificados = (int)($stats['total_certificados'] ?? 0);

        $porcentaje = $totalInscritos > 0 
            ? round(($totalAsistieron / $totalInscritos) * 100, 1) 
            : 0.0;

        return [
            'evento_id'            => $eventoId,
            'total_inscritos'      => $totalInscritos,
            'total_asistieron'     => $totalAsistieron,
            'total_pendientes'     => $totalPendientes,
            'total_certificados'   => $totalCertificados,
            'porcentaje_asistencia'=> $porcentaje
        ];
    }

    public function existeInscripcion(int $eventoId, string $documentoId, ?int $excludeId = null): bool {
        $documentoId = trim($documentoId);
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM entradas_participantes 
                WHERE evento_id = ? AND documento_id = ? AND id != ?
            ");
            $stmt->execute([$eventoId, $documentoId, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM entradas_participantes 
                WHERE evento_id = ? AND documento_id = ?
            ");
            $stmt->execute([$eventoId, $documentoId]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    public function mapCampos(array $data): array {
        return [
            ':evento_id'    => (int)$data['evento_id'],
            ':documento_id' => trim((string)$data['documento_id']),
            ':nombres'      => trim((string)$data['nombres']),
            ':apellidos'    => trim((string)$data['apellidos']),
            ':correo'       => strtolower(trim((string)$data['correo'])),
            ':telefono'     => !empty($data['telefono']) ? trim((string)$data['telefono']) : null,
        ];
    }
}
