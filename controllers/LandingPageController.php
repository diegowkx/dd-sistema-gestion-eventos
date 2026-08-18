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
        $usuario         = $_SESSION['participante_landing'] ?? null;

        require_once 'LandingPage/index.php';
    }

    public function registrarParticipante(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
            exit;
        }

        $documentoId = isset($_POST['documento_id']) ? trim((string)$_POST['documento_id']) : '';
        $nombres     = isset($_POST['nombres']) ? trim((string)$_POST['nombres']) : '';
        $apellidos   = isset($_POST['apellidos']) ? trim((string)$_POST['apellidos']) : '';
        $correo      = isset($_POST['correo']) ? trim((string)$_POST['correo']) : '';
        $telefono    = isset($_POST['telefono']) ? trim((string)$_POST['telefono']) : '';
        $googleId    = !empty($_POST['google_id']) ? trim((string)$_POST['google_id']) : null;

        if (empty($documentoId) || empty($nombres) || empty($apellidos) || empty($correo)) {
            echo json_encode(['status' => 'error', 'message' => 'Todos los campos obligatorios deben ser completados.']);
            exit;
        }

        $data = [
            'documento_id' => $documentoId,
            'nombres'      => $nombres,
            'apellidos'    => $apellidos,
            'correo'       => $correo,
            'telefono'     => $telefono,
            'google_id'    => $googleId
        ];

        try {
            $this->model->guardarOActualizarParticipanteGlobal($data);

            $_SESSION['participante_landing'] = $data;

            echo json_encode(['status' => 'success', 'message' => 'Tus datos se registraron correctamente.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
        exit;
    }

    public function loginGoogle(): void {
        header('Content-Type: application/json');

        $jwt = $_POST['credential'] ?? '';
        if (empty($jwt)) {
            echo json_encode(['status' => 'error', 'message' => 'Token de Google no recibido.']);
            exit;
        }

        // Decodificar JWT enviado por Google
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            echo json_encode(['status' => 'error', 'message' => 'Token de Google no válido.']);
            exit;
        }

        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);

        if (!$payload || empty($payload['email'])) {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo decodificar el perfil del usuario.']);
            exit;
        }

        $correo   = $payload['email'];
        $nombres  = $payload['given_name'] ?? '';
        $apellidos = $payload['family_name'] ?? '';
        $googleId = $payload['sub'] ?? '';

        // Verificar si el usuario ya existe por correo o por google_id
        $existente = $this->model->obtenerParticipantePorCorreoOGoogleId($correo, $googleId);

        if ($existente) {
            $_SESSION['participante_landing'] = $existente;
            echo json_encode(['status' => 'success', 'registrado' => true, 'message' => 'Bienvenido de nuevo, ' . $existente['nombres']]);
        } else {
            // Requiere que ingrese DNI para completar el registro global
            echo json_encode([
                'status'     => 'success',
                'registrado' => false,
                'data'       => [
                    'correo'    => $correo,
                    'nombres'   => $nombres,
                    'apellidos' => $apellidos,
                    'google_id' => $googleId
                ]
            ]);
        }
        exit;
    }

    public function logoutLanding(): void {
        header('Content-Type: application/json');
        unset($_SESSION['participante_landing']);
        echo json_encode(['status' => 'success']);
        exit;
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

        $this->model->guardarOActualizarParticipanteGlobal($datosPersona);
        $exito = $this->model->registrarEntradaInamovible($datosPersona);

        if ($exito) {
            $_SESSION['participante_landing'] = $datosPersona;
            echo json_encode(['status' => 'success', 'message' => 'Inscripción confirmada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al registrar la entrada.']);
        }
        exit;
    }
}