<?php

declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/ChecksPermisos.php';

/**
 * Controlador para la gestión, emisión y envío por correo de Certificados de Participación.
 */
class CertificadosController {

    use ChecksPermisos;

    /**
     * Muestra la vista principal de la gestión de certificados.
     */
    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('CERTIFICADOS');

        require_once __DIR__ . '/../models/Certificados.php';
        $model = new Certificados($pdo);
        $eventosDisponibles = $model->getEventosFinalizados();

        $title = "Gestión de Certificados";
        require_once __DIR__ . '/../views/certificados/index.php';
    }

    /**
     * Endpoint API JSON: Lista participantes con su estado de emisión y estadísticas del evento.
     */
    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('CERTIFICADOS');
        require_once __DIR__ . '/../models/Certificados.php';
        $model = new Certificados($pdo);

        $eventoId = isset($_GET['evento_id']) && $_GET['evento_id'] !== '' && (int)$_GET['evento_id'] > 0
            ? (int)$_GET['evento_id']
            : null;

        $estado = strtoupper(trim((string)($_GET['estado'] ?? $_GET['filtro'] ?? 'TODOS')));
        if (!in_array($estado, ['TODOS', 'EMITIDO', 'PENDIENTE'], true)) {
            $estado = 'TODOS';
        }

        $data = $model->getAll($eventoId, $estado);
        $resumen = $model->getEstadisticasEvento($eventoId);

        echo json_encode([
            'success' => true,
            'data'    => $data,
            'resumen' => $resumen
        ]);
        exit;
    }

    /**
     * Genera el PDF del certificado en tiempo real y devuelve su representación en Base64
     * para previsualización inmediata en el visor Modal del cliente.
     */
    public function previsualizarPdf(PDO $pdo): void {
        $this->requierePermisoApi('CERTIFICADOS');
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Certificados.php';
        $model = new Certificados($pdo);
        $participante = $model->getById($id);

        if (!$participante) {
            echo json_encode(['success' => false, 'message' => 'Participante no encontrado o sin asistencia confirmada']);
            exit;
        }

        // Cargar Autoload de Composer para Dompdf
        require_once __DIR__ . '/../vendor/autoload.php';

        // 1. Obtención y conversión de la imagen institucional a Base64
        $logoPath = __DIR__ . '/../assets/images/logo-image.png';
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $dataImg = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
        }

        // 2. Renderizado del Buffer HTML desde la plantilla
        ob_start();
        require __DIR__ . '/../views/certificados/pdf_template.php';
        $html = ob_get_clean();

        // 3. Procesamiento del PDF mediante Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $pdfOutput = $dompdf->output();
        $pdfBase64 = base64_encode($pdfOutput);

        echo json_encode([
            'success'  => true,
            'pdf_data' => 'data:application/pdf;base64,' . $pdfBase64,
            'nombre'   => trim(($participante['nombres'] ?? '') . ' ' . ($participante['apellidos'] ?? ''))
        ]);
        exit;
    }

    /**
     * Emisión masiva directa en base de datos para los participantes con asistencia de un evento.
     */
    public function emitirMasivo(PDO $pdo): void {
        $this->requierePermisoApi('CERTIFICADOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $eventoId = (int)($_POST['evento_id'] ?? 0);
        if ($eventoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Seleccione un evento válido para la emisión masiva']);
            exit;
        }

        require_once __DIR__ . '/../models/Certificados.php';

        // Actualización masiva de registros con asistencia confirmada pendiente de emisión
        $stmt = $pdo->prepare("
            UPDATE entradas_participantes 
            SET certificado_emitido = 1, fecha_emision_certificado = NOW()
            WHERE evento_id = :evento_id AND asistencia = 1 AND certificado_emitido = 0
        ");
        $stmt->execute([':evento_id' => $eventoId]);
        $afectados = $stmt->rowCount();

        if ($afectados > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Se han emitido exitosamente {$afectados} certificados en la base de datos."
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No existen certificados pendientes por emitir para este evento.'
            ]);
        }
        exit;
    }

    /**
     * Envío individual por correo electrónico (SMTP PHPMailer) del certificado adjunto en PDF.
     */
    public function enviarIndividual(PDO $pdo): void {
        $this->requierePermisoApi('CERTIFICADOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        // RESPUESTA TEMPORAL HASTA CONFIGURAR CREDENCIALES SMTP:
        echo json_encode([
            'success' => false,
            'message' => 'El envío por correo está inhabilitado. Complete las configuraciones indicadas en el archivo README.md.'
        ]);
        exit;

        /*
        // DESCOMENTAR UNA VEZ CONFIGURADO EL ARCHIVO README.MD Y EL HELPER SMTP:
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identificador no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Certificados.php';
        require_once __DIR__ . '/../helpers/MailerHelper.php';
        require_once __DIR__ . '/../vendor/autoload.php';

        $model = new Certificados($pdo);
        $participante = $model->getById($id);

        if (!$participante) {
            echo json_encode(['success' => false, 'message' => 'Participante no encontrado']);
            exit;
        }

        // 1. Generar el logo en Base64
        $logoPath = __DIR__ . '/../assets/images/logo-image.png';
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $dataImg = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
        }

        // 2. Generar el PDF en memoria mediante Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        ob_start();
        require __DIR__ . '/../views/certificados/pdf_template.php';
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        // 3. Enviar correo SMTP
        $nombreCompleto = trim(($participante['nombres'] ?? '') . ' ' . ($participante['apellidos'] ?? ''));
        $enviado = MailerHelper::enviarCertificado(
            $participante['correo'],
            $nombreCompleto,
            $participante['evento_titulo'],
            $pdfOutput
        );

        if ($enviado) {
            $model->marcarEmitido($id);
            echo json_encode(['success' => true, 'message' => 'Certificado enviado a ' . $participante['correo']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al enviar el correo SMTP']);
        }
        exit;
        */
    }

    /**
     * Envío masivo por correo electrónico (SMTP PHPMailer) para los certificados pendientes de un evento.
     */
    public function enviarMasivo(PDO $pdo): void {
        $this->requierePermisoApi('CERTIFICADOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        // RESPUESTA TEMPORAL HASTA CONFIGURAR CREDENCIALES SMTP:
        echo json_encode([
            'success' => false,
            'message' => 'El envío masivo por correo está inhabilitado. Complete las configuraciones indicadas en el archivo README.md.'
        ]);
        exit;

        /*
        // DESCOMENTAR UNA VEZ CONFIGURADO EL ARCHIVO README.MD Y EL HELPER SMTP:
        $eventoId = (int)($_POST['evento_id'] ?? 0);
        if ($eventoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Seleccione un evento válido para el envío masivo']);
            exit;
        }

        require_once __DIR__ . '/../models/Certificados.php';
        require_once __DIR__ . '/../helpers/MailerHelper.php';
        require_once __DIR__ . '/../vendor/autoload.php';

        $model = new Certificados($pdo);
        $participantes = $model->getAll($eventoId, 'PENDIENTE');

        if (empty($participantes)) {
            echo json_encode(['success' => false, 'message' => 'No hay certificados pendientes por enviar para este evento']);
            exit;
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $logoPath = __DIR__ . '/../assets/images/logo-image.png';
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $dataImg = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
        }

        $enviadosCorrectos = 0;
        $erroresEnvio = 0;

        foreach ($participantes as $participante) {
            $dompdf = new Dompdf($options);
            
            ob_start();
            require __DIR__ . '/../views/certificados/pdf_template.php';
            $html = ob_get_clean();

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $pdfOutput = $dompdf->output();

            $nombreCompleto = trim(($participante['nombres'] ?? '') . ' ' . ($participante['apellidos'] ?? ''));
            $enviado = MailerHelper::enviarCertificado(
                $participante['correo'],
                $nombreCompleto,
                $participante['evento_titulo'],
                $pdfOutput
            );

            if ($enviado) {
                $model->marcarEmitido((int)$participante['id']);
                $enviadosCorrectos++;
            } else {
                $erroresEnvio++;
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Proceso completado: {$enviadosCorrectos} correos enviados con éxito." . ($erroresEnvio > 0 ? " ({$erroresEnvio} fallidos)" : "")
        ]);
        exit;
        */
    }
}