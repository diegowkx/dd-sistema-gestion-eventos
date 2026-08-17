<?php
declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class EventosController {
    use ChecksPermisos;

    private const FORMATOS_PERMITIDOS = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_BYTES_ORIGINAL  = 5 * 1024 * 1024; // 5 MB
    private const MAX_DIMENSION       = 4000; // px
    private const CALIDAD_WEBP        = 82;

    private function rutaAssets(): string {
        return __DIR__ . '/../assets/eventos';
    }

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('EVENTOS');

        $title = "Eventos";
        require_once __DIR__ . '/../views/eventos/index.php';
    }

    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('EVENTOS');
        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);
        $estado = strtoupper(trim((string)($_GET['estado'] ?? 'ACTIVO')));
        $data = $estado === 'INACTIVO' ? $model->getAllInactive() : $model->getAll();
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('EVENTOS');
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

    public function guardar(PDO $pdo): void {
        $this->requierePermisoApi('EVENTOS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $esEdicion = !empty($_POST['es_edicion']) && $_POST['es_edicion'] === '1';

        $data = [
            'titulo'       => trim((string)($_POST['titulo'] ?? '')),
            'tema'         => trim((string)($_POST['tema'] ?? '')),
            'descripcion'  => trim((string)($_POST['descripcion'] ?? '')),
            'fecha_evento' => trim((string)($_POST['fecha_evento'] ?? '')),
            'direccion'    => trim((string)($_POST['direccion'] ?? '')),
            'cupos'        => (int)($_POST['cupos'] ?? 0),
            'precio'       => (float)($_POST['precio'] ?? 0),
        ];

        // Validaciones básicas
        if ($data['titulo'] === '' || mb_strlen($data['titulo']) > 150) {
            echo json_encode(['success' => false, 'message' => 'El título es obligatorio (máx. 150 caracteres).']); exit;
        }
        if ($data['tema'] === '' || mb_strlen($data['tema']) > 100) {
            echo json_encode(['success' => false, 'message' => 'El tema es obligatorio (máx. 100 caracteres).']); exit;
        }
        if ($data['fecha_evento'] === '') {
            echo json_encode(['success' => false, 'message' => 'La fecha del evento es obligatoria.']); exit;
        }
        if ($data['direccion'] === '' || mb_strlen($data['direccion']) > 200) {
            echo json_encode(['success' => false, 'message' => 'La dirección es obligatoria (máx. 200 caracteres).']); exit;
        }
        if ($data['cupos'] < 0) {
            echo json_encode(['success' => false, 'message' => 'Los cupos deben ser mayores o iguales a 0.']); exit;
        }
        if ($data['precio'] < 0) {
            echo json_encode(['success' => false, 'message' => 'El precio debe ser mayor o igual a 0.']); exit;
        }
        if ($esEdicion && $id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido para edición.']); exit;
        }

        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);

        $username = (string)$_SESSION['user_name'];
        $puedeAprobar = $this->puedeAprobarEventos();

        try {
            if ($esEdicion) {
                if (!$model->getById($id)) {
                    echo json_encode(['success' => false, 'message' => 'El evento no existe.']); exit;
                }
                $resultado = $model->update($id, $data, $username, $puedeAprobar);
                $mensajeOk = $puedeAprobar
                    ? 'Evento actualizado correctamente'
                    : 'Evento actualizado. Quedó en revisión (PENDIENTE).';
                $mensajeError = 'No se pudo actualizar el evento.';
                $idResultante = $id;
            } else {
                $nuevoId = $model->create($data, $username);
                $resultado = $nuevoId !== false;
                $mensajeOk = 'Evento creado correctamente. Ahora puedes agregar imágenes.';
                $mensajeError = 'No se pudo crear el evento.';
                $idResultante = $resultado ? $nuevoId : 0;
            }

            echo json_encode([
                'success' => (bool)$resultado,
                'message' => $resultado ? $mensajeOk : $mensajeError,
                'id'      => $idResultante,
            ]);
        } catch (PDOException $e) {
            error_log('Error en EventosController::guardar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno en la base de datos']);
        }
        exit;
    }

    public function eliminar(PDO $pdo): void {
        $this->requierePermisoApi('EVENTOS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido']); exit;
        }
        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);
        if (!$model->getById($id)) {
            echo json_encode(['success' => false, 'message' => 'El evento no existe.']); exit;
        }

        $resultado = $model->eliminarLogico($id);
        echo json_encode([
            'success' => (bool)$resultado,
            'message' => $resultado ? 'Evento eliminado correctamente' : 'No se pudo eliminar el evento'
        ]);
        exit;
    }

    public function restaurar(PDO $pdo): void {
        $this->requierePermisoApi('EVENTOS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido']); exit;
        }
        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);
        if (!$model->getById($id)) {
            echo json_encode(['success' => false, 'message' => 'El evento no existe.']); exit;
        }
        $resultado = $model->restore($id);
        echo json_encode([
            'success' => (bool)$resultado,
            'message' => $resultado ? 'Evento restaurado correctamente' : 'No se pudo restaurar el evento'
        ]);
        exit;
    }

    // ==========================================================
    // IMÁGENES
    // ==========================================================

    public function subirImagen(PDO $pdo): void {
        $this->requierePermisoApi('EVENTOS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit;
        }

        $eventoId = (int)($_POST['evento_id'] ?? 0);
        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);

        if ($eventoId <= 0 || !$model->existeEventoActivo($eventoId)) {
            echo json_encode(['success' => false, 'message' => 'Evento no válido.']); exit;
        }

        if ($model->contarImagenes($eventoId) >= Eventos::MAX_IMAGENES) {
            echo json_encode(['success' => false, 'message' => 'Ya se alcanzó el máximo de 5 imágenes activas.']); exit;
        }

        if (empty($_FILES['imagen']) || ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo válido.']); exit;
        }

        try {
            $procesado = $this->procesarYGuardarImagen($_FILES['imagen']);
        } catch (Throwable $e) {
            error_log('Error procesando imagen en EventosController::subirImagen: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno al procesar la imagen.']); exit;
        }

        if (!$procesado['ok']) {
            echo json_encode(['success' => false, 'message' => $procesado['error']]); exit;
        }

        $ok = $model->agregarImagen($eventoId, $procesado['ruta']);
        if (!$ok) {
            @unlink($this->rutaAssets() . '/' . basename($procesado['ruta']));
            echo json_encode(['success' => false, 'message' => 'No se pudo guardar la imagen (límite alcanzado).']); exit;
        }

        $this->marcarModificacionSiCorresponde($model, $eventoId);

        echo json_encode([
            'success'   => true,
            'message'   => 'Imagen agregada correctamente',
            'ruta'      => $procesado['ruta'],
            'imagenes'  => $model->getImagenes($eventoId),
        ]);
        exit;
    }

    public function eliminarImagen(PDO $pdo): void {
        $this->requierePermisoApi('EVENTOS');
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

        $ok = $model->marcarImagenParaEliminacion($imagenId, $eventoId);
        if ($ok) {
            $this->marcarModificacionSiCorresponde($model, $eventoId);
        }

        echo json_encode([
            'success'  => $ok,
            'message'  => $ok ? 'Imagen enviada a la papelera' : 'No se pudo enviar la imagen a la papelera',
            'imagenes' => $model->getImagenes($eventoId),
            'papelera' => $model->getImagenesPapelera($eventoId),
        ]);
        exit;
    }

    public function restaurarImagen(PDO $pdo): void {
        $this->requierePermisoApi('EVENTOS');
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
        if ($ok) {
            $this->marcarModificacionSiCorresponde($model, $eventoId);
        }

        echo json_encode([
            'success'  => $ok,
            'message'  => $ok ? 'Imagen restaurada correctamente' : 'No se pudo restaurar (máximo de 5 imágenes activas alcanzado).',
            'imagenes' => $model->getImagenes($eventoId),
            'papelera' => $model->getImagenesPapelera($eventoId),
        ]);
        exit;
    }

    public function reordenarImagenes(PDO $pdo): void {
        $this->requierePermisoApi('EVENTOS');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit;
        }
        $eventoId = (int)($_POST['evento_id'] ?? 0);
        $idsRaw = $_POST['orden'] ?? [];

        if (!is_array($idsRaw) || empty($idsRaw)) {
            echo json_encode(['success' => false, 'message' => 'Orden no válido.']); exit;
        }
        $ids = array_map('intval', $idsRaw);

        require_once __DIR__ . '/../models/Eventos.php';
        $model = new Eventos($pdo);

        $ok = $model->reordenarImagenes($eventoId, $ids);
        if ($ok) {
            $this->marcarModificacionSiCorresponde($model, $eventoId);
        }

        echo json_encode([
            'success'  => $ok,
            'message'  => $ok ? 'Orden actualizado correctamente' : 'No se pudo reordenar (las imágenes no coinciden con las del evento).',
            'imagenes' => $model->getImagenes($eventoId),
        ]);
        exit;
    }

    private function marcarModificacionSiCorresponde(Eventos $model, int $eventoId): void {
        if (!$this->puedeAprobarEventos()) {
            $model->marcarModificacionPendiente($eventoId, (string)$_SESSION['user_name']);
        }
    }

    private function puedeAprobarEventos(): bool {
        if (method_exists($this, 'tienePermiso')) {
            return (bool)$this->tienePermiso('APROBAR_EVENTOS');
        }
        return false;
    }

    private function procesarYGuardarImagen(array $file): array {
        if ($file['size'] > self::MAX_BYTES_ORIGINAL) {
            return ['ok' => false, 'error' => 'La imagen supera el límite de 5 MB.'];
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false || empty($info['mime'])) {
            return ['ok' => false, 'error' => 'El archivo no es una imagen válida.'];
        }
        $mime = $info['mime'];

        if (!in_array($mime, self::FORMATOS_PERMITIDOS, true)) {
            return ['ok' => false, 'error' => 'Formato no permitido. Usa JPG, PNG o WEBP.'];
        }

        if (!function_exists('imagewebp')) {
            return ['ok' => false, 'error' => 'El servidor no tiene soporte de WebP habilitado en GD.'];
        }
        if ($mime === 'image/webp' && !function_exists('imagecreatefromwebp')) {
            return ['ok' => false, 'error' => 'El servidor no tiene soporte de WebP habilitado en GD.'];
        }

        $origen = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'image/png'  => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => @imagecreatefromwebp($file['tmp_name']),
            default      => false,
        };

        if ($origen === false) {
            return ['ok' => false, 'error' => 'No se pudo procesar la imagen.'];
        }

        imagepalettetotruecolor($origen);
        imagealphablending($origen, true);
        imagesavealpha($origen, true);

        $anchoOriginal = imagesx($origen);
        $altoOriginal  = imagesy($origen);

        $anchoFinal = $anchoOriginal;
        $altoFinal  = $altoOriginal;

        if ($anchoOriginal > self::MAX_DIMENSION || $altoOriginal > self::MAX_DIMENSION) {
            $ratio = min(self::MAX_DIMENSION / $anchoOriginal, self::MAX_DIMENSION / $altoOriginal);
            $anchoFinal = (int)round($anchoOriginal * $ratio);
            $altoFinal  = (int)round($altoOriginal * $ratio);
        }

        if ($anchoFinal !== $anchoOriginal || $altoFinal !== $altoOriginal) {
            $destino = imagecreatetruecolor($anchoFinal, $altoFinal);
            imagealphablending($destino, false);
            imagesavealpha($destino, true);
            imagecopyresampled($destino, $origen, 0, 0, 0, 0, $anchoFinal, $altoFinal, $anchoOriginal, $altoOriginal);
            imagedestroy($origen);
        } else {
            $destino = $origen;
        }

        $carpeta = $this->rutaAssets();
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }

        $nombreArchivo = uniqid('evt_', true) . '.webp';
        $rutaAbsoluta = $carpeta . '/' . $nombreArchivo;

        $guardado = imagewebp($destino, $rutaAbsoluta, self::CALIDAD_WEBP);
        imagedestroy($destino);

        if (!$guardado) {
            return ['ok' => false, 'error' => 'No se pudo guardar la imagen procesada.'];
        }

        return ['ok' => true, 'ruta' => 'assets/eventos/' . $nombreArchivo, 'error' => null];
    }
}