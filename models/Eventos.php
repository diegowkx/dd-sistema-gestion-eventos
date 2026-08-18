<?php

declare(strict_types=1);

class Eventos {

    private PDO $pdo;
    public const MAX_IMAGENES = 5;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * SELECT_BASE con los campos exactos de la tabla 'eventos'.
     */
    private const SELECT_BASE = "
        SELECT 
            id, 
            titulo, 
            tema, 
            descripcion, 
            fecha_evento, 
            direccion, 
            cupos, 
            precio, 
            registro_username, 
            publicacion, 
            supervisor_username, 
            observaciones, 
            activo, 
            created_at, 
            updated_at
        FROM eventos
    ";

    public function getAll(): array {
        $stmt = $this->pdo->query(self::SELECT_BASE . " WHERE activo = 1 ORDER BY fecha_evento DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllInactive(): array {
        $stmt = $this->pdo->query(self::SELECT_BASE . " WHERE activo = 0 ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Consulta auxiliar para el módulo de supervisión: lista eventos activos filtrados
     * por su estado de publicación (PENDIENTE / APROBADO / RECHAZADO).
     */
    public function getAllPorPublicacion(string $publicacion): array {
        $stmt = $this->pdo->prepare(self::SELECT_BASE . " WHERE activo = 1 AND publicacion = ? ORDER BY updated_at DESC");
        $stmt->execute([$publicacion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare(self::SELECT_BASE . " WHERE id = ?");
        $stmt->execute([$id]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($evento) {
            $evento['imagenes'] = $this->getImagenes($id);
        }
        return $evento;
    }

    /**
     * Relaciones con asistencias o certificados que impiden eliminación física.
     */
    public function tieneRelacionesFuturas(int $id): bool {
        foreach (['asistencias', 'certificados'] as $tabla) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$tabla} WHERE evento_id = ?");
            $stmt->execute([$id]);
            if ((int)$stmt->fetchColumn() > 0) {
                return true;
            }
        }
        return false;
    }

    public function create(array $data, string $username): int|false {
        $stmt = $this->pdo->prepare("
            INSERT INTO eventos (
                titulo, tema, descripcion, fecha_evento, direccion, cupos, precio,
                registro_username, publicacion, supervisor_username, observaciones
            ) VALUES (
                :titulo, :tema, :descripcion, :fecha_evento, :direccion, :cupos, :precio,
                :registro_username, 'PENDIENTE', NULL, NULL
            )
        ");
        $ok = $stmt->execute($this->mapCampos($data, $username));
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    /**
     * Si $tienePermisoAprobar es false: fuerza publicacion='PENDIENTE' y registro_username=$username.
     * Si es true: actualiza los campos sin tocar publicacion/registro_username/supervisor/observaciones.
     */
    public function update(int $id, array $data, string $username, bool $tienePermisoAprobar): bool {
        $sql = "
            UPDATE eventos SET
                titulo = :titulo, tema = :tema, descripcion = :descripcion,
                fecha_evento = :fecha_evento, direccion = :direccion,
                cupos = :cupos, precio = :precio
        ";
        $params = $this->mapCampos($data, $username);
        unset($params[':registro_username']);

        if (!$tienePermisoAprobar) {
            $sql .= ", publicacion = 'PENDIENTE', registro_username = :registro_username";
            $params[':registro_username'] = $username;
        }

        $sql .= " WHERE id = :id";
        $params[':id'] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Marca el evento como modificado (usado cuando solo cambian imágenes) para usuarios
     * SIN APROBAR_EVENTOS: fuerza publicacion='PENDIENTE' y registro_username=$username.
     */
    public function marcarModificacionPendiente(int $id, string $username): bool {
        $stmt = $this->pdo->prepare("
            UPDATE eventos SET publicacion = 'PENDIENTE', registro_username = :username WHERE id = :id
        ");
        return $stmt->execute([':username' => $username, ':id' => $id]);
    }

    public function aprobar(int $id, string $supervisorUsername): bool {
        $stmt = $this->pdo->prepare("
            UPDATE eventos SET publicacion = 'APROBADO', supervisor_username = :su, observaciones = NULL WHERE id = :id
        ");
        $ok = $stmt->execute([':su' => $supervisorUsername, ':id' => $id]);
        if ($ok) {
            $this->limpiarImagenesMarcadas($id);
        }
        return $ok;
    }

    public function rechazar(int $id, string $supervisorUsername, string $observaciones): bool {
        $stmt = $this->pdo->prepare("
            UPDATE eventos SET publicacion = 'RECHAZADO', supervisor_username = :su, observaciones = :obs WHERE id = :id
        ");
        return $stmt->execute([':su' => $supervisorUsername, ':obs' => $observaciones, ':id' => $id]);
    }

    public function volverAPendiente(int $id): bool {
        $stmt = $this->pdo->prepare("
            UPDATE eventos SET publicacion = 'PENDIENTE', supervisor_username = NULL, observaciones = NULL WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Elimina físicamente solo si NO tiene relaciones; en caso contrario marca activo = 0.
     */
    public function delete(int $id): array {
        if ($this->tieneRelacionesFuturas($id)) {
            $stmt = $this->pdo->prepare("UPDATE eventos SET activo = 0 WHERE id = ?");
            $stmt->execute([$id]);
            return ['eliminado_fisico' => false, 'ok' => true, 'rutas_imagenes' => []];
        }

        $this->pdo->beginTransaction();
        try {
            $stmtImg = $this->pdo->prepare("SELECT ruta FROM imagenes_evento WHERE evento_id = ?");
            $stmtImg->execute([$id]);
            $rutas = $stmtImg->fetchAll(PDO::FETCH_COLUMN);

            $this->pdo->prepare("DELETE FROM imagenes_evento WHERE evento_id = ?")->execute([$id]);
            $ok = $this->pdo->prepare("DELETE FROM eventos WHERE id = ?")->execute([$id]);

            $this->pdo->commit();
            return ['eliminado_fisico' => true, 'ok' => $ok, 'rutas_imagenes' => $rutas];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function restore(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE eventos SET activo = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function eliminarLogico(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE eventos SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function mapCampos(array $data, string $username): array {
        return [
            ':titulo'            => trim((string)$data['titulo']),
            ':tema'              => trim((string)$data['tema']),
            ':descripcion'       => trim((string)($data['descripcion'] ?? '')) !== '' ? trim((string)$data['descripcion']) : null,
            ':fecha_evento'      => $data['fecha_evento'],
            ':direccion'         => trim((string)$data['direccion']),
            ':cupos'             => (int)($data['cupos'] ?? 0),
            ':precio'            => (float)($data['precio'] ?? 0.00),
            ':registro_username' => $username,
        ];
    }

    // ==========================================
    // IMÁGENES
    // ==========================================

    public function getImagenes(int $eventoId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, evento_id, ruta, orden
            FROM imagenes_evento
            WHERE evento_id = ? AND eliminacion = 0
            ORDER BY orden ASC
        ");
        $stmt->execute([$eventoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getImagenesPapelera(int $eventoId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, evento_id, ruta, orden, eliminacion, created_at
            FROM imagenes_evento
            WHERE evento_id = ? AND eliminacion = 1
            ORDER BY id ASC
        ");
        $stmt->execute([$eventoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarImagenes(int $eventoId): int {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM imagenes_evento WHERE evento_id = ? AND eliminacion = 0
        ");
        $stmt->execute([$eventoId]);
        return (int)$stmt->fetchColumn();
    }

    public function existeEventoActivo(int $eventoId): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM eventos WHERE id = ?");
        $stmt->execute([$eventoId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function imagenPerteneceAEvento(int $imagenId, int $eventoId): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM imagenes_evento WHERE id = ? AND evento_id = ?
        ");
        $stmt->execute([$imagenId, $eventoId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function agregarImagen(int $eventoId, string $ruta): bool {
        if ($this->contarImagenes($eventoId) >= self::MAX_IMAGENES) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            SELECT COALESCE(MAX(orden), 0) + 1 
            FROM imagenes_evento 
            WHERE evento_id = ? AND eliminacion = 0
        ");
        $stmt->execute([$eventoId]);
        $siguienteOrden = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("
            INSERT INTO imagenes_evento (evento_id, ruta, orden, eliminacion)
            VALUES (:evento_id, :ruta, :orden, 0)
        ");
        return $stmt->execute([
            ':evento_id' => $eventoId,
            ':ruta'      => $ruta,
            ':orden'     => $siguienteOrden,
        ]);
    }

    public function marcarImagenParaEliminacion(int $imagenId, int $eventoId): bool {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                UPDATE imagenes_evento 
                SET eliminacion = 1, orden = -1 * id 
                WHERE id = ? AND evento_id = ?
            ");
            $stmt->execute([$imagenId, $eventoId]);

            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }

            $activas = $this->getImagenes($eventoId);
            
            foreach ($activas as $i => $img) {
                $this->actualizarOrden((int)$img['id'], $eventoId, -1 * ($i + 1) - 1000);
            }
            foreach ($activas as $i => $img) {
                $this->actualizarOrden((int)$img['id'], $eventoId, $i + 1);
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function desmarcarImagenParaEliminacion(int $imagenId, int $eventoId): bool {
        if ($this->contarImagenes($eventoId) >= self::MAX_IMAGENES) {
            return false; 
        }

        $stmt = $this->pdo->prepare("
            SELECT COALESCE(MAX(orden), 0) + 1 
            FROM imagenes_evento 
            WHERE evento_id = ? AND eliminacion = 0
        ");
        $stmt->execute([$eventoId]);
        $nuevoOrden = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("
            UPDATE imagenes_evento 
            SET eliminacion = 0, orden = :orden 
            WHERE id = :id AND evento_id = :evento_id
        ");
        return $stmt->execute([
            ':orden'     => $nuevoOrden,
            ':id'        => $imagenId,
            ':evento_id' => $eventoId,
        ]);
    }

    private function actualizarOrden(int $imagenId, int $eventoId, int $nuevoOrden): bool {
        $stmt = $this->pdo->prepare("
            UPDATE imagenes_evento SET orden = ? WHERE id = ? AND evento_id = ?
        ");
        return $stmt->execute([$nuevoOrden, $imagenId, $eventoId]);
    }

    public function reordenarImagenes(int $eventoId, array $imagenIds): bool {
        $vigentes = $this->getImagenes($eventoId);
        $idsVigentes = array_column($vigentes, 'id');

        sort($idsVigentes);
        $idsRecibidosOrdenados = $imagenIds;
        sort($idsRecibidosOrdenados);
        if ($idsVigentes !== $idsRecibidosOrdenados) {
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            foreach ($imagenIds as $indice => $imagenId) {
                $this->actualizarOrden((int)$imagenId, $eventoId, -1 * ($indice + 1) - 1000);
            }
            foreach ($imagenIds as $indice => $imagenId) {
                $this->actualizarOrden((int)$imagenId, $eventoId, $indice + 1);
            }
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function limpiarImagenesMarcadas(int $eventoId): array {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, ruta FROM imagenes_evento WHERE evento_id = ? AND eliminacion = 1
            ");
            $stmt->execute([$eventoId]);
            $marcadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($marcadas) {
                $ids = array_column($marcadas, 'id');
                $in = implode(',', array_fill(0, count($ids), '?'));
                $this->pdo->prepare("DELETE FROM imagenes_evento WHERE id IN ($in)")->execute($ids);
            }

            $restantes = $this->getImagenes($eventoId);
            foreach ($restantes as $indice => $img) {
                $this->actualizarOrden((int)$img['id'], $eventoId, -1 * ($indice + 1) - 1000);
            }
            foreach ($restantes as $indice => $img) {
                $this->actualizarOrden((int)$img['id'], $eventoId, $indice + 1);
            }

            $this->pdo->commit();
            return array_column($marcadas, 'ruta');
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}