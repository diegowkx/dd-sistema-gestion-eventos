<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class AsistenciasController {

    use ChecksPermisos;

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('ASISTENCIAS');

        require_once __DIR__ . '/../models/Eventos.php';
        $eventosModel = new Eventos($pdo);
        $eventosDisponibles = $eventosModel->getAllPorPublicacion('APROBADO');

        $title = "Control de Asistencias";
        require_once __DIR__ . '/../views/asistencias/index.php';
    }

    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('ASISTENCIAS');
        require_once __DIR__ . '/../models/Asistencias.php';
        $model = new Asistencias($pdo);

        $eventoId = isset($_GET['evento_id']) && $_GET['evento_id'] !== '' && (int)$_GET['evento_id'] > 0
            ? (int)$_GET['evento_id']
            : null;

        $estado = strtoupper(trim((string)($_GET['estado'] ?? $_GET['filtro'] ?? 'TODOS')));
        if (!in_array($estado, ['TODOS', 'ASISTIO', 'PENDIENTE'], true)) {
            $estado = 'TODOS';
        }

        $data = $model->getAll($eventoId, $estado);
        $resumen = $eventoId !== null ? $model->getEstadisticasEvento($eventoId) : null;

        echo json_encode([
            'success' => true,
            'data'    => $data,
            'resumen' => $resumen
        ]);
        exit;
    }

    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('ASISTENCIAS');
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Asistencias.php';
        $model = new Asistencias($pdo);
        $participante = $model->getById($id);

        if (!$participante) {
            echo json_encode(['success' => false, 'message' => 'Participante no encontrado']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $participante]);
        exit;
    }

    public function marcar(PDO $pdo): void {
        $this->requierePermisoApi('ASISTENCIAS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Asistencias.php';
        $model = new Asistencias($pdo);
        $participante = $model->getById($id);

        if (!$participante) {
            echo json_encode(['success' => false, 'message' => 'El participante no existe']);
            exit;
        }

        if ((int)$participante['asistencia'] === 1) {
            echo json_encode(['success' => false, 'message' => 'El participante ya tenía asistencia registrada']);
            exit;
        }

        $username = (string)($_SESSION['user_name'] ?? '');
        if ($username === '') {
            echo json_encode(['success' => false, 'message' => 'Sesión no válida o expirada']);
            exit;
        }

        try {
            $resultado = $model->marcar($id, $username);
            $participanteActualizado = $resultado ? $model->getById($id) : null;

            echo json_encode([
                'success' => (bool)$resultado,
                'message' => $resultado ? 'Asistencia marcada correctamente' : 'No se pudo marcar la asistencia',
                'data'    => $participanteActualizado
            ]);
        } catch (PDOException $e) {
            error_log('Error en AsistenciasController::marcar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno en la base de datos']);
        }
        exit;
    }

    public function desmarcar(PDO $pdo): void {
        $this->requierePermisoApi('ASISTENCIAS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Asistencias.php';
        $model = new Asistencias($pdo);
        $participante = $model->getById($id);

        if (!$participante) {
            echo json_encode(['success' => false, 'message' => 'El participante no existe']);
            exit;
        }

        if ((int)$participante['certificado_emitido'] === 1) {
            echo json_encode([
                'success' => false,
                'message' => 'No se puede desmarcar la asistencia porque ya se emitió el certificado a este participante'
            ]);
            exit;
        }

        if ((int)$participante['asistencia'] === 0) {
            echo json_encode(['success' => false, 'message' => 'El participante no cuenta con asistencia marcada']);
            exit;
        }

        try {
            $resultado = $model->desmarcar($id);
            $participanteActualizado = $resultado ? $model->getById($id) : null;

            echo json_encode([
                'success' => (bool)$resultado,
                'message' => $resultado ? 'Asistencia desmarcada correctamente' : 'No se pudo desmarcar la asistencia',
                'data'    => $participanteActualizado
            ]);
        } catch (PDOException $e) {
            error_log('Error en AsistenciasController::desmarcar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno en la base de datos']);
        }
        exit;
    }

    public function marcarPorDocumento(PDO $pdo): void {
        $this->requierePermisoApi('ASISTENCIAS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $eventoId = (int)($_POST['evento_id'] ?? 0);
        $documentoId = trim((string)($_POST['documento_id'] ?? ''));

        if ($eventoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un evento válido']);
            exit;
        }

        if ($documentoId === '') {
            echo json_encode(['success' => false, 'message' => 'El número de documento es obligatorio']);
            exit;
        }

        $username = (string)($_SESSION['user_name'] ?? '');
        if ($username === '') {
            echo json_encode(['success' => false, 'message' => 'Sesión no válida o expirada']);
            exit;
        }

        require_once __DIR__ . '/../models/Asistencias.php';
        $model = new Asistencias($pdo);

        try {
            $resultado = $model->marcarPorDocumento($eventoId, $documentoId, $username);
            echo json_encode([
                'success' => $resultado['ok'],
                'message' => $resultado['mensaje'],
                'data'    => $resultado['data'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log('Error en AsistenciasController::marcarPorDocumento: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno en la base de datos']);
        }
        exit;
    }

    public function estadisticas(PDO $pdo): void {
        $this->requierePermisoApi('ASISTENCIAS');
        $eventoId = (int)($_GET['evento_id'] ?? 0);
        if ($eventoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador de evento no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Asistencias.php';
        $model = new Asistencias($pdo);
        $stats = $model->getEstadisticasEvento($eventoId);

        echo json_encode(['success' => true, 'data' => $stats]);
        exit;
    }

    public function eventos(PDO $pdo): void {
        $this->requierePermisoApi('ASISTENCIAS');
        require_once __DIR__ . '/../models/Eventos.php';
        $eventosModel = new Eventos($pdo);
        $eventos = $eventosModel->getAllPorPublicacion('APROBADO');

        echo json_encode(['success' => true, 'data' => $eventos]);
        exit;
    }
}
