<?php

declare(strict_types=1);

require_once 'models/LandingPage.php';

class LandingPageController {

    private LandingPage $model;

    public function __construct(?PDO $pdo = null) {
        if ($pdo === null) {
            global $pdo; 
        }
        $this->model = new LandingPage($pdo);
    }

    public function index(): void {
        $proximosEventos = $this->model->getProximosEventos();
        $todosEventos    = $this->model->getTodosEventos();
        require_once 'LandingPage/index.php';
    }

    public function getDetalleAjax(): void {
        header('Content-Type: application/json');

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Identificador no válido.']);
            exit;
        }

        $evento = $this->model->getEventoPorId($id);
        if (!$evento) {
            echo json_encode(['status' => 'error', 'message' => 'El evento solicitado no está disponible.']);
            exit;
        }

        $evento['es_pasado'] = strtotime($evento['fecha_evento']) < time();
        $evento['cupos_disponibles'] = $this->model->getCuposDisponibles((int)$evento['id'], (int)$evento['cupos']);
        $evento['fecha_evento'] = date('d/m/Y h:i A', strtotime($evento['fecha_evento']));

        echo json_encode(['status' => 'success', 'data' => $evento]);
        exit;
    }

    public function procesarInscripcion(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
            exit;
        }

        $eventoId    = isset($_POST['evento_id']) ? (int)$_POST['evento_id'] : 0;
        $documentoId = isset($_POST['documento_id']) ? trim((string)$_POST['documento_id']) : '';
        $nombres     = isset($_POST['nombres']) ? trim((string)$_POST['nombres']) : '';
        $apellidos   = isset($_POST['apellidos']) ? trim((string)$_POST['apellidos']) : '';
        $correo      = isset($_POST['correo']) ? trim((string)$_POST['correo']) : '';
        $telefono    = isset($_POST['telefono']) ? trim((string)$_POST['telefono']) : '';

        if (empty($eventoId) || empty($documentoId) || empty($nombres) || empty($apellidos) || empty($correo)) {
            echo json_encode(['status' => 'error', 'message' => 'Complete todos los campos obligatorios.']);
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Ingresa un correo electrónico válido.']);
            exit;
        }

        if (strlen($documentoId) > 15 || strlen($telefono) > 15) {
            echo json_encode(['status' => 'error', 'message' => 'El documento y el teléfono no deben superar 15 caracteres.']);
            exit;
        }

        $evento = $this->model->getEventoPorId($eventoId);
        if (!$evento) {
            echo json_encode(['status' => 'error', 'message' => 'El evento no existe.']);
            exit;
        }

        if (strtotime($evento['fecha_evento']) < time()) {
            echo json_encode(['status' => 'error', 'message' => 'El evento ya finalizó.']);
            exit;
        }

        if ($this->model->getCuposDisponibles((int)$evento['id'], (int)$evento['cupos']) <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'No quedan cupos disponibles.']);
            exit;
        }

        if ($this->model->estaInscrito($eventoId, $documentoId)) {
            echo json_encode(['status' => 'error', 'message' => 'Ya estás registrado en este evento.']);
            exit;
        }

        $datosPersona = [
            'evento_id'    => $eventoId,
            'documento_id' => $documentoId,
            'nombres'      => $nombres,
            'apellidos'    => $apellidos,
            'correo'       => $correo,
            'telefono'     => $telefono
        ];

        try {
            $exito = $this->model->registrarInscripcion($datosPersona);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                echo json_encode(['status' => 'error', 'message' => 'Ya estás inscrito en este evento.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar la inscripción.']);
            }
            exit;
        }

        if ($exito) {
            echo json_encode(['status' => 'success', 'message' => 'Inscripción confirmada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al registrar la entrada.']);
        }
        exit;
    }
}