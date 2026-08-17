<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class DashboardController {

    use ChecksPermisos;

    /**
     * Renderiza la vista principal del Dashboard
     */
    public function index(PDO $pdo): void {

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }

        $this->requierePermiso('DASHBOARD');

        $title = "Panel Principal";
        require_once __DIR__ . '/../views/dashboard/index.php';
    }

    /**
     * Endpoint API: tarjetas resumen + ocupación por tipo + ingresos de los últimos 7 días,
     * todo en una sola respuesta para minimizar peticiones desde el dashboard.
     */
    public function resumen(PDO $pdo): void {
        $this->requierePermisoApi('DASHBOARD');
        header('Content-Type: application/json');

        require_once __DIR__ . '/../models/Dashboard.php';
        $dashboardModel = new Dashboard($pdo);

        $ingresosPorFecha = $dashboardModel->getIngresosUltimos7Dias();

        // Completa los días sin cobros con 0, para que el gráfico siempre muestre 7 días
        $indexado = [];
        foreach ($ingresosPorFecha as $fila) {
            $indexado[$fila['fecha']] = (float)$fila['total'];
        }
        $ultimos7Dias = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-{$i} day"));
            $ultimos7Dias[] = [
                'fecha' => $fecha,
                'total' => round($indexado[$fecha] ?? 0.0, 2),
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'resumen'   => $dashboardModel->getResumenGeneral(),
                'ocupacion' => $dashboardModel->getOcupacionPorTipo(),
                'ingresos_7_dias' => $ultimos7Dias,
            ]
        ]);
        exit;
    }

    /**
     * Endpoint API: Listado DataTable de historial de IPs
     */
    public function listarIps(PDO $pdo): void {
        $this->requierePermisoApi('DASHBOARD');
        header('Content-Type: application/json');

        require_once __DIR__ . '/../models/Dashboard.php';
        $dashboardModel = new Dashboard($pdo);

        echo json_encode([
            'success' => true,
            'data'    => $dashboardModel->getHistorialIps()
        ]);
        exit;
    }

    /**
     * Endpoint API: Consulta de detalle por ID de un registro de IP
     */
    public function getIpById(PDO $pdo): void {
        $this->requierePermisoApi('DASHBOARD');
        header('Content-Type: application/json');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de registro no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Dashboard.php';
        $dashboardModel = new Dashboard($pdo);

        $detalle = $dashboardModel->getIpDetalleById($id);

        if ($detalle !== false) {
            echo json_encode(['success' => true, 'data' => $detalle]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registro de IP no encontrado']);
        }
        exit;
    }
}