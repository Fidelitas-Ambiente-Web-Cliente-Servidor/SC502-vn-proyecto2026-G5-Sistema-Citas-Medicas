<?php
/**
 * DiagnosticoController.php — Controlador de Diagnósticos e Historial Médico
 *
 * Este archivo forma parte de la capa "Controlador" del patrón MVC.
 * Recibe las solicitudes del frontend relacionadas con el historial
 * médico del paciente, valida los datos y coordina con el modelo
 * Diagnostico para guardar o recuperar registros de la base de datos.
 *
 * Maneja tres tipos de solicitudes:
 *   - GET  ?api=historial   → Devuelve el historial del usuario en sesión
 *   - POST ?accion=guardar_diagnostico   → Registra un nuevo diagnóstico
 *   - POST ?accion=eliminar_diagnostico  → Elimina un registro por ID
 *   - POST ?accion=borrar_historial      → Borra todo el historial del usuario
 *
 * Proyecto: Sistema de Citas Médicas CCSS
 * Curso: SC-502 Ambiente Web Cliente Servidor
 * Universidad Fidélitas — Grupo 5
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Diagnostico.php';

/**
 * DiagnosticoController
 *
 * Controlador responsable de toda la lógica relacionada con el historial
 * médico de los pacientes. Cada método público corresponde a una acción
 * que el usuario puede realizar desde la vista de diagnósticos.
 *
 *  *  */
class DiagnosticoController
{
    /** @var Diagnostico Modelo para operaciones sobre el historial médico */
    private $model;

    /** @var int|null ID del usuario en sesión; null si no hay sesión activa */
    private $user_id;

    /**
     * __construct — Inicializa la conexión, el modelo y el ID de sesión
     *
     * Al instanciarse, establece la conexión a la base de datos,
     * prepara el modelo Diagnostico y captura el ID del usuario
     * en sesión para usarlo en todos los métodos del controlador.
     */
    public function __construct()
    {
        $database = new Database();
        $db       = $database->connect();

        $this->model   = new Diagnostico($db);
        $this->user_id = $_SESSION['user_id'] ?? null;
    }

    /* =====================================================
       API — lista historial del usuario en sesión
    ===================================================== */

    /**
     * apiListar — Devuelve el historial médico del usuario autenticado
     *
     * Endpoint de la API que consulta todos los registros de diagnóstico
     * del usuario en sesión y los devuelve ordenados del más reciente
     * al más antiguo. La interfaz los muestra en la sección de historial
     * médico del panel personal.
     *
     * Responde con código HTTP 401 (no autorizado) si no hay sesión activa.
     *
     *  void
     */
    public function apiListar(): void
    {
        if (!$this->user_id) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $historial = $this->model->getByUsuario((int) $this->user_id);
        echo json_encode(['success' => true, 'data' => $historial]);
    }

    /* =====================================================
       POST — guardar registro de historial médico
    ===================================================== */

    /**
     * guardar — Crea un nuevo registro en el historial médico del paciente
     *
     * Recibe los datos del formulario clínico (paciente, síntomas,
     * diagnóstico, tratamiento, notas y fecha), valida que los campos
     * obligatorios estén presentes y llama al modelo para insertar
     * el registro en la base de datos.
     *
     * Si no se envía fecha, se usa automáticamente la fecha de hoy.
     *
     *  void
     *              o {'success': false, 'error': '...'} si falla la validación
     */
    public function guardar(): void
    {
        if (!$this->user_id) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $rol = $_SESSION['user_rol'] ?? '';
        if (!in_array($rol, ['medico', 'admin'], true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Solo el personal médico puede registrar diagnósticos']);
            return;
        }

        $paciente    = trim($_POST['paciente']    ?? '');
        $sintomas    = trim($_POST['sintomas']    ?? '');
        $diagnostico = trim($_POST['diagnostico'] ?? '');
        $tratamiento = trim($_POST['tratamiento'] ?? '');
        $notas       = trim($_POST['notas']       ?? '');
        $fecha       = trim($_POST['fecha']       ?? date('Y-m-d'));

        if (!$paciente || !$sintomas || !$diagnostico || !$tratamiento) {
            echo json_encode(['success' => false, 'error' => 'Campos requeridos incompletos (paciente, sintomas, diagnostico, tratamiento)']);
            return;
        }

        $data = [
            'id_usuario'  => (int) $this->user_id,
            'paciente'    => $paciente,
            'sintomas'    => $sintomas,
            'diagnostico' => $diagnostico,
            'tratamiento' => $tratamiento,
            'notas'       => $notas,
            'fecha'       => $fecha,
        ];

        $id = $this->model->insert($data);

        echo json_encode(['success' => true, 'id' => $id]);
    }

    /* =====================================================
       POST — eliminar un registro (debe pertenecer al usuario)
    ===================================================== */

    /**
     * eliminar — Borra un registro específico del historial médico
     *
     * Recibe el ID del registro a eliminar y llama al modelo, que
     * verificará que dicho registro pertenezca al usuario en sesión
     * antes de borrarlo. Esta doble verificación evita que un usuario
     * pueda eliminar registros que no le corresponden.
     *
     *  void
     */
    public function eliminar(): void
    {
        if (!$this->user_id) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            return;
        }

        $ok = $this->model->delete($id, (int) $this->user_id);
        echo json_encode(['success' => $ok]);
    }

    /* =====================================================
       POST — borrar todo el historial del usuario
    ===================================================== */

    /**
     * borrarTodo — Elimina todo el historial médico del usuario en sesión
     *
     * Borra en una sola operación todos los registros de diagnóstico
     * que pertenecen al usuario actualmente en sesión. Se usa cuando
     * el paciente solicita limpiar su historial completo desde el
     * panel de perfil de la aplicación.
     *
     *  void
     */
    public function borrarTodo(): void
    {
        if (!$this->user_id) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $ok = $this->model->deleteAllByUsuario((int) $this->user_id);
        echo json_encode(['success' => $ok]);
    }
}
