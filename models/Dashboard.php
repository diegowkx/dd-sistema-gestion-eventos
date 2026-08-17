<?php

declare(strict_types=1);

class Dashboard {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
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