<?php
declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class SupervisionController {
    use ChecksPermisos;

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('APROBAR_EVENTOS');
        $title = "Supervisión de Eventos";
        require_once __DIR__ . '/../views/supervision/index.php';
    }

    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('APROBAR_EVENTOS');
        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);

        $filtro = strtoupper(trim((string)($_GET['filtro'] ?? 'PENDIENTE')));

        $data = match ($filtro) {
            'APROBADO'   => $model->getAllPorPublicacion('APROBADO'),
            'RECHAZADO'  => $model->getAllPorPublicacion('RECHAZADO'),
            'ELIMINADOS' => $model->getAllInactive(),
            default      => $model->getAllPorPublicacion('PENDIENTE'),
        };

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('APROBAR_EVENTOS');
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido']);
            exit;
        }
        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);
        $evento = $model->getById($id);
        if (!$evento) {
            echo json_encode(['success' => false, 'message' => 'Evento no encontrado']);
            exit;
        }
        $evento['imagenes_papelera'] = $model->getImagenesPapelera($id);
        echo json_encode(['success' => true, 'data' => $evento]);
        exit;
    }

    public function aprobar(PDO $pdo): void {
        $this->requierePermisoApi('APROBAR_EVENTOS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido']); exit;
        }

        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);
        $evento = $model->getById($id);

        if (!$evento || (int)$evento['activo'] !== 1) {
            echo json_encode(['success' => false, 'message' => 'El evento no existe o no está activo.']); exit;
        }
        if ($evento['publicacion'] !== 'PENDIENTE') {
            echo json_encode(['success' => false, 'message' => 'Solo se pueden aprobar eventos en estado PENDIENTE.']); exit;
        }

        $username = (string)$_SESSION['user_name'];

        try {
            // Eventos::aprobar() ya limpia (borra BD) las imágenes marcadas en la papelera.
            // Aquí se eliminan los archivos físicos correspondientes del disco.
            $stmtPapelera = $pdo->prepare("SELECT ruta FROM imagenes_evento WHERE evento_id = ? AND eliminacion = 1");
            $stmtPapelera->execute([$id]);
            $rutasABorrar = $stmtPapelera->fetchAll(PDO::FETCH_COLUMN);

            $resultado = $model->aprobar($id, $username);

            if ($resultado) {
                foreach ($rutasABorrar as $ruta) {
                    $this->borrarArchivoFisico($ruta);
                }
            }

            echo json_encode([
                'success' => (bool)$resultado,
                'message' => $resultado ? 'Evento aprobado correctamente' : 'No se pudo aprobar el evento'
            ]);
        } catch (Throwable $e) {
            error_log('Error en SupervisionController::aprobar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al aprobar el evento']);
        }
        exit;
    }

    public function rechazar(PDO $pdo): void {
        $this->requierePermisoApi('APROBAR_EVENTOS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        $observaciones = trim((string)($_POST['observaciones'] ?? ''));

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido']); exit;
        }
        if ($observaciones === '') {
            echo json_encode(['success' => false, 'message' => 'Debes indicar el motivo del rechazo.']); exit;
        }
        if (mb_strlen($observaciones) > 1000) {
            echo json_encode(['success' => false, 'message' => 'El motivo no puede superar los 1000 caracteres.']); exit;
        }

        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);
        $evento = $model->getById($id);

        if (!$evento || (int)$evento['activo'] !== 1) {
            echo json_encode(['success' => false, 'message' => 'El evento no existe o no está activo.']); exit;
        }
        if ($evento['publicacion'] !== 'PENDIENTE') {
            echo json_encode(['success' => false, 'message' => 'Solo se pueden rechazar eventos en estado PENDIENTE.']); exit;
        }

        $username = (string)$_SESSION['user_name'];
        $resultado = $model->rechazar($id, $username, $observaciones);

        echo json_encode([
            'success' => (bool)$resultado,
            'message' => $resultado ? 'Evento rechazado correctamente' : 'No se pudo rechazar el evento'
        ]);
        exit;
    }

    /**
     * Reenvía un evento RECHAZADO a PENDIENTE. El supervisor se corrige a sí mismo devolviéndolo
     * a la cola de revisión, en vez de aprobarlo directamente sin que nadie más lo revise.
     */
    public function volverAPendiente(PDO $pdo): void {
        $this->requierePermisoApi('APROBAR_EVENTOS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido']); exit;
        }

        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);
        $evento = $model->getById($id);

        if (!$evento || (int)$evento['activo'] !== 1) {
            echo json_encode(['success' => false, 'message' => 'El evento no existe o no está activo.']); exit;
        }
        if ($evento['publicacion'] !== 'RECHAZADO') {
            echo json_encode(['success' => false, 'message' => 'Solo se puede reenviar a pendiente un evento RECHAZADO.']); exit;
        }

        $resultado = $model->volverAPendiente($id);
        echo json_encode([
            'success' => (bool)$resultado,
            'message' => $resultado ? 'Evento reenviado a revisión (PENDIENTE).' : 'No se pudo reenviar el evento.'
        ]);
        exit;
    }

    /**
     * Restaura una imagen desde la papelera durante la revisión (el registrador
     * o el usuario con APROBAR_EVENTOS puede hacerlo).
     */
    public function restaurarImagen(PDO $pdo): void {
        $this->requierePermisoApi('APROBAR_EVENTOS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit;
        }
        $eventoId = (int)($_POST['evento_id'] ?? 0);
        $imagenId = (int)($_POST['imagen_id'] ?? 0);

        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);

        if (!$model->imagenPerteneceAEvento($imagenId, $eventoId)) {
            echo json_encode(['success' => false, 'message' => 'La imagen no pertenece a este evento.']); exit;
        }

        $ok = $model->desmarcarImagenParaEliminacion($imagenId, $eventoId);

        echo json_encode([
            'success'  => $ok,
            'message'  => $ok ? 'Imagen restaurada correctamente' : 'No se pudo restaurar (máximo de 5 imágenes activas alcanzado).',
            'imagenes' => $model->getImagenes($eventoId),
            'papelera' => $model->getImagenesPapelera($eventoId),
        ]);
        exit;
    }

    /**
     * Eliminación DEFINITIVA (Papelera de Eventos): solo para eventos ya en activo = 0.
     * Usa Eventos::delete(), que decide entre borrado físico o mantener
     * activo = 0 si existen registros/relaciones ligadas. Si borra
     * físicamente, elimina del disco los archivos de sus imágenes.
     */
    public function eliminarDefinitivo(PDO $pdo): void {
        $this->requierePermisoApi('APROBAR_EVENTOS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido']); exit;
        }

        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);
        $evento = $model->getById($id);

        if (!$evento) {
            echo json_encode(['success' => false, 'message' => 'El evento no existe.']); exit;
        }
        if ((int)$evento['activo'] !== 0) {
            echo json_encode(['success' => false, 'message' => 'Solo se pueden eliminar definitivamente eventos que ya estén inactivos.']); exit;
        }

        try {
            $resultado = $model->delete($id);
        } catch (Throwable $e) {
            error_log('Error en SupervisionController::eliminarDefinitivo: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al eliminar el evento']);
            exit;
        }

        if (!$resultado['ok']) {
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el evento.']);
            exit;
        }

        if ($resultado['eliminado_fisico']) {
            foreach ($resultado['rutas_imagenes'] as $ruta) {
                $this->borrarArchivoFisico($ruta);
            }
            echo json_encode(['success' => true, 'message' => 'Evento eliminado definitivamente junto con sus imágenes.']);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'El evento tiene registros relacionados y no puede eliminarse físicamente; permanece inactivo.',
            ]);
        }
        exit;
    }

    private function borrarArchivoFisico(string $rutaRelativa): void {
        $rutaAbsoluta = __DIR__ . '/../' . ltrim($rutaRelativa, '/');
        if (is_file($rutaAbsoluta)) {
            @unlink($rutaAbsoluta);
        }
    }
}