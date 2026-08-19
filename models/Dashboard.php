<?php

declare(strict_types=1);

class Dashboard {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getResumenGeneral(?string $fechaDesde = null, ?string $fechaHasta = null): array {
        [$filtroEvento, $paramsEvento] = $this->filtroFechaEvento('e', $fechaDesde, $fechaHasta);
        [$filtroVentas, $paramsVentas] = $this->filtroFechaEvento('ev', $fechaDesde, $fechaHasta, 'ventas');
        [$filtroIngresos, $paramsIngresos] = $this->filtroFechaEvento('ev', $fechaDesde, $fechaHasta, 'ingresos');
        [$filtroAsistentes, $paramsAsistentes] = $this->filtroFechaEvento('ev', $fechaDesde, $fechaHasta, 'asistentes');
        [$filtroCertificados, $paramsCertificados] = $this->filtroFechaEvento('ev', $fechaDesde, $fechaHasta, 'certificados');
        [$filtroPendientesCertificado, $paramsPendientesCertificado] = $this->filtroFechaEvento('ev', $fechaDesde, $fechaHasta, 'pendientes_certificado');
        $params = array_merge($paramsEvento, $paramsVentas, $paramsIngresos, $paramsAsistentes, $paramsCertificados, $paramsPendientesCertificado);

        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) AS eventos_totales,
                COALESCE(SUM(publicacion = 'APROBADO'), 0) AS eventos_publicados,
                COALESCE(SUM(publicacion = 'PENDIENTE'), 0) AS eventos_pendientes,
                COALESCE(SUM(publicacion = 'RECHAZADO'), 0) AS eventos_rechazados,
                (SELECT COUNT(*) FROM entradas_participantes ep INNER JOIN eventos ev ON ev.id = ep.evento_id WHERE ev.activo = 1 {$filtroVentas}) AS ventas_realizadas,
                (SELECT COALESCE(SUM(ev.precio), 0) FROM entradas_participantes ep INNER JOIN eventos ev ON ev.id = ep.evento_id WHERE ev.activo = 1 {$filtroIngresos}) AS ingresos_estimados,
                (SELECT COUNT(*) FROM entradas_participantes ep INNER JOIN eventos ev ON ev.id = ep.evento_id WHERE ev.activo = 1 AND ep.asistencia = 1 {$filtroAsistentes}) AS asistentes,
                (SELECT COUNT(*) FROM entradas_participantes ep INNER JOIN eventos ev ON ev.id = ep.evento_id WHERE ev.activo = 1 AND ep.certificado_emitido = 1 {$filtroCertificados}) AS certificados_emitidos,
                (SELECT COUNT(*) FROM entradas_participantes ep INNER JOIN eventos ev ON ev.id = ep.evento_id WHERE ev.activo = 1 AND ep.certificado_emitido = 0 {$filtroPendientesCertificado}) AS certificados_pendientes,
                (SELECT COUNT(*) FROM usuarios WHERE activo = 1) AS usuarios_activos,
                (SELECT COUNT(*) FROM iphys) AS accesos_registrados
            FROM eventos e
            WHERE e.activo = 1 {$filtroEvento}
        ");
        $stmt->execute($params);

        $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $ventas = (int)($data['ventas_realizadas'] ?? 0);
        $asistentes = (int)($data['asistentes'] ?? 0);

        $data['porcentaje_asistencia'] = $ventas > 0
            ? round(($asistentes / $ventas) * 100, 1)
            : 0.0;

        foreach (['eventos_totales', 'eventos_publicados', 'eventos_pendientes', 'eventos_rechazados', 'ventas_realizadas', 'asistentes', 'certificados_emitidos', 'certificados_pendientes', 'usuarios_activos', 'accesos_registrados'] as $field) {
            $data[$field] = (int)($data[$field] ?? 0);
        }
        $data['ingresos_estimados'] = round((float)($data['ingresos_estimados'] ?? 0), 2);

        return $data;
    }

    public function getOcupacionPorTipo(?string $fechaDesde = null, ?string $fechaHasta = null): array {
        [$filtroEvento, $params] = $this->filtroFechaEvento('e', $fechaDesde, $fechaHasta);
        $stmt = $this->pdo->prepare("
            SELECT publicacion, COUNT(*) AS cantidad
            FROM eventos e
            WHERE e.activo = 1 {$filtroEvento}
            GROUP BY publicacion
        ");
        $stmt->execute($params);

        $result = ['PENDIENTE' => 0, 'APROBADO' => 0, 'RECHAZADO' => 0];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['publicacion']] = (int)$row['cantidad'];
        }
        return $result;
    }

    public function getIngresosUltimos7Dias(?string $fechaDesde = null, ?string $fechaHasta = null): array {
        [$filtroEvento, $params] = $this->filtroFechaEvento('e', $fechaDesde, $fechaHasta);
        $stmt = $this->pdo->prepare("
            SELECT DATE(e.fecha_evento) AS fecha, COUNT(*) AS ventas,
                   COALESCE(SUM(e.precio), 0) AS total
            FROM entradas_participantes ep
            INNER JOIN eventos e ON e.id = ep.evento_id
            WHERE e.activo = 1 {$filtroEvento}
            GROUP BY DATE(e.fecha_evento)
            ORDER BY fecha ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function filtroFechaEvento(string $alias, ?string $fechaDesde, ?string $fechaHasta, ?string $prefijo = null): array {
        $filtros = [];
        $params = [];
        $prefijo ??= $alias;
        if ($fechaDesde !== null && $fechaDesde !== '') {
            $nombreDesde = ':' . $prefijo . '_fecha_desde';
            $filtros[] = "AND {$alias}.fecha_evento >= {$nombreDesde}";
            $params[$nombreDesde] = $fechaDesde . ' 00:00:00';
        }
        if ($fechaHasta !== null && $fechaHasta !== '') {
            $nombreHasta = ':' . $prefijo . '_fecha_hasta';
            $filtros[] = "AND {$alias}.fecha_evento < {$nombreHasta}";
            $params[$nombreHasta] = date('Y-m-d', strtotime($fechaHasta . ' +1 day')) . ' 00:00:00';
        }
        return [implode(' ', $filtros), $params];
    }


    /**
     * Registra el acceso de un usuario con su IP en la tabla iphys.
     */
    public function registrarAccesoIp(string $username, string $rol, string $ip): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO iphys (usuario_username, rol_name, ip_address, fecha_acceso) 
            VALUES (:username, :rol, :ip, NOW())
        ");

        return $stmt->execute([
            ':username' => $username,
            ':rol'      => $rol,
            ':ip'       => $ip
        ]);
    }

    /**
     * Obtiene el historial completo de IPs registradas con el nombre del usuario.
     */
    public function getHistorialIps(): array {
        $stmt = $this->pdo->query("
            SELECT 
                i.id,
                i.usuario_username,
                CONCAT(u.nombres, ' ', u.apellidos) AS usuario_nombre_completo,
                i.rol_name,
                i.ip_address,
                i.fecha_acceso
            FROM iphys i
            INNER JOIN usuarios u ON i.usuario_username = u.username
            ORDER BY i.fecha_acceso DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el detalle específico de un registro de IP.
     */
    public function getIpDetalleById(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT 
                i.id,
                i.usuario_username,
                CONCAT(u.nombres, ' ', u.apellidos) AS usuario_nombre_completo,
                u.correo,
                u.telefono,
                i.rol_name,
                i.ip_address,
                i.fecha_acceso
            FROM iphys i
            INNER JOIN usuarios u ON i.usuario_username = u.username
            WHERE i.id = ?
        ");

        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
}