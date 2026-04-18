<?php
/**
 * CitasController.php — Controlador de Citas Médicas
 *
 * Este archivo forma parte de la capa "Controlador" del patrón MVC.
 * Recibe todas las solicitudes HTTP relacionadas con las citas médicas
 * (tanto del paciente como del personal médico), valida los datos de
 * entrada y coordina con los modelos para guardar o recuperar
 * información de la base de datos.
 *
 * Endpoints API que expone (
 *   - GET ?api=citas    → Citas del usuario en sesión
 *   - GET ?api=agenda   → Agenda completa (todas las citas del sistema)
 *   - GET ?api=stats    → Estadísticas del panel principal
 *
 * Acciones POST que procesa (
 *   - agregar_cita        → Registra una nueva cita
 *   - cancelar_cita       → Cambia el estado de la cita a "Cancelada"
 *   - reprogramar_cita    → Actualiza fecha, hora y estado a "Reprogramada"
 *   - confirmar_cita_hora → Confirma la hora de una cita pendiente
 *   - atender_cita        → Marca la cita como "Atendida"
 *   - simular_solicitud   → Genera una cita de prueba (uso administrativo)
 *
 * Proyecto: Sistema de Citas Médicas CCSS
 * Curso: SC-502 Ambiente Web Cliente Servidor
 * Universidad Fidélitas — Grupo 5
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Diagnostico.php';
require_once __DIR__ . '/../models/Personalmedico.php';

/**
 * CitasController
 *
 * Controlador principal para el módulo de citas médicas. Agrupa toda
 * la lógica de negocio relacionada con agendar, cancelar, reprogramar
 * y atender citas. Incluye el método privado calcularPrioridad() que
 * determina automáticamente la urgencia de una cita según el motivo
 * escrito por el paciente.
 */
class CitasController
{
    /** @var Cita Modelo para operaciones sobre la tabla de citas */
    private $model;

    /** @var Diagnostico Modelo para acceder al historial médico */
    private $diagModel;

    /** @var Personalmedico Modelo para consultar el personal médico */
    private $pmModel;

    /** @var int|null ID del usuario en sesión; null si no hay sesión activa */
    private $user_id;

    /**
     * __construct — Inicializa la conexión y los tres modelos que usa este controlador
     *
     * Al instanciarse, establece la conexión a la base de datos y prepara
     * los modelos Cita, Diagnostico y Personalmedico. También captura el
     * ID del usuario en sesión para usarlo en todos los métodos.
     */
    public function __construct()
    {
        $database = new Database();
        $db       = $database->connect();

        $this->model     = new Cita($db);
        $this->diagModel = new Diagnostico($db);
        $this->pmModel   = new Personalmedico($db);
        $this->user_id   = $_SESSION['user_id'] ?? null;
    }

    /* =====================================================
       API — lista citas del usuario en sesión
    ===================================================== */

    /**
     * apiListar — Devuelve las citas del usuario autenticado
     *
     * Endpoint de la API que consulta y devuelve todas las citas
     * registradas para el usuario en sesión. La interfaz de usuario
     * llama a este método con JavaScript para mostrar la lista sin
     * recargar la página.
     *
     * Responde con código HTTP 401 (no autorizado) si no hay sesión.
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

        $citas = $this->model->getByUsuario((int) $this->user_id);
        echo json_encode(['success' => true, 'data' => $citas]);
    }

    /* =====================================================
       API — agenda completa (personal médico / admin)
    ===================================================== */

    /**
     * apiAgenda — Devuelve la agenda completa de todas las citas
     *
     * Endpoint de la API destinado al personal médico o administrador.
     * Devuelve todas las citas del sistema (de todos los pacientes),
     * incluyendo el nombre y apellidos de cada paciente. Se usa para
     * la vista de agenda médica general.
     *
     *  void
     */
    public function apiAgenda(): void
    {
        if (!$this->user_id) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $citas = $this->model->getAll();
        echo json_encode(['success' => true, 'data' => $citas]);
    }

    /* =====================================================
       API — estadísticas globales
    ===================================================== */

    /**
     * apiStats — Devuelve las estadísticas del panel principal
     *
     * Reúne en una sola respuesta cuatro cifras clave para el panel
     * de estadísticas (dashboard):
     *   - Citas activas del usuario en sesión
     *   - Diagnósticos registrados del usuario
     *   - Total de médicos en el sistema
     *   - Total de citas con prioridad Alta en toda la clínica
     *
     *  void
     */
    public function apiStats(): void
    {
        if (!$this->user_id) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $citas_activas = $this->model->countActivas((int) $this->user_id);
        $diagnosticos  = $this->diagModel->countByUsuario((int) $this->user_id);
        $personal      = $this->pmModel->getCount();
        $citas_alta    = $this->model->countAlta();

        echo json_encode([
            'success'       => true,
            'citas_activas' => $citas_activas,
            'diagnosticos'  => $diagnosticos,
            'personal'      => $personal,
            'citas_alta'    => $citas_alta,
        ]);
    }

    /* =====================================================
       POST — agregar cita
    ===================================================== */

    /**
     * agregar — Crea una nueva cita médica para el usuario en sesión
     *
     * Recibe los datos del formulario de solicitud de cita (especialidad,
     * motivo, fecha y hora), calcula automáticamente la prioridad
     * analizando el motivo con calcularPrioridad(), y guarda la cita
     * en la base de datos con estado inicial 'Activa'.
     *
     * Si el cliente envía una prioridad manual en el formulario, esta
     * tiene preferencia sobre la calculada automáticamente.
     *
     *  void
     */
    public function agregar(): void
    {
        if (!$this->user_id) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $especialidad = trim($_POST['especialidad'] ?? '');
        $motivo       = trim($_POST['motivo']       ?? '');
        $fecha        = trim($_POST['fecha']        ?? '');
        $hora         = trim($_POST['hora']         ?? '');

        if (!$especialidad || !$motivo || !$fecha) {
            echo json_encode(['success' => false, 'error' => 'Campos requeridos incompletos']);
            return;
        }

        $prioridad_calculada = $this->calcularPrioridad($motivo);
        // Si el cliente envía prioridad manual se usa, de lo contrario la calculada
        $prioridad_final = !empty($_POST['prioridad']) ? $_POST['prioridad'] : $prioridad_calculada;

        $data = [
            'id_usuario'  => (int) $this->user_id,
            'especialidad' => $especialidad,
            'motivo'       => $motivo,
            'fecha'        => $fecha,
            'hora'         => $hora ?: null,
            'prioridad'    => $prioridad_final,
        ];

        $id = $this->model->insert($data);

        echo json_encode([
            'success'            => true,
            'id'                 => $id,
            'prioridad_calculada' => $prioridad_calculada,
            'prioridad_final'    => $prioridad_final,
        ]);
    }

    /* =====================================================
       POST — cancelar cita
    ===================================================== */

    /**
     * cancelar — Cambia el estado de una cita a 'Cancelada'
     *
     * Recibe el ID de la cita por POST y actualiza su estado en la
     * base de datos. La cita permanece en el historial pero ya no se
     * considera vigente ni aparece en la agenda de pendientes.
     *
     *  void
     */
    public function cancelar(): void
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

        $ok = $this->model->updateEstado($id, 'Cancelada');
        echo json_encode(['success' => $ok]);
    }

    /* =====================================================
       POST — reprogramar cita
    ===================================================== */

    /**
     * reprogramar — Actualiza la fecha, hora y datos de una cita existente
     *
     * Permite cambiar la fecha y/o hora de una cita, así como su
     * especialidad, motivo y prioridad. Los campos que no se envíen
     * en el formulario conservan el valor original de la cita para
     * no perder información.
     *
     * Al completarse, el estado de la cita cambia a 'Reprogramada'
     * para que tanto el paciente como el médico lo noten.
     *
     *  void
     */
    public function reprogramar(): void
    {
        if (!$this->user_id) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $id           = (int) trim($_POST['id']           ?? 0);
        $fecha        = trim($_POST['fecha']              ?? '');
        $hora         = trim($_POST['hora']               ?? '');
        $especialidad = trim($_POST['especialidad']       ?? '');
        $motivo       = trim($_POST['motivo']             ?? '');

        if (!$id || !$fecha) {
            echo json_encode(['success' => false, 'error' => 'Datos insuficientes para reprogramar']);
            return;
        }

        // Obtener cita original para conservar campos no enviados
        $cita = $this->model->getById($id);
        if (!$cita) {
            echo json_encode(['success' => false, 'error' => 'Cita no encontrada']);
            return;
        }

        $prioridad = !empty($_POST['prioridad']) ? $_POST['prioridad'] : $cita['prioridad'];

        $data = [
            'id'           => $id,
            'fecha'        => $fecha,
            'hora'         => $hora ?: $cita['hora'],
            'especialidad' => $especialidad ?: $cita['especialidad'],
            'motivo'       => $motivo ?: $cita['motivo'],
            'prioridad'    => $prioridad,
            'estado'       => 'Reprogramada',
        ];

        $ok = $this->model->update($data);
        echo json_encode(['success' => $ok]);
    }

    /* =====================================================
       POST — confirmar hora de cita
    ===================================================== */

    /**
     * confirmarHora — Asigna una hora exacta a la cita y la marca como 'Confirmada'
     *
     * El personal médico usa este método para confirmar la hora definitiva
     * de atención. Se mantienen todos los demás datos de la cita
     * (especialidad, motivo, prioridad) y solo se actualiza la hora
     * y el estado, que pasa a 'Confirmada'.
     *
     *  void
     */
    public function confirmarHora(): void
    {
        if (!$this->user_id) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $id   = (int) trim($_POST['id']   ?? 0);
        $hora = trim($_POST['hora']       ?? '');

        if (!$id || !$hora) {
            echo json_encode(['success' => false, 'error' => 'ID u hora inválidos']);
            return;
        }

        $cita = $this->model->getById($id);
        if (!$cita) {
            echo json_encode(['success' => false, 'error' => 'Cita no encontrada']);
            return;
        }

        $data = [
            'id'           => $id,
            'fecha'        => $cita['fecha'],
            'hora'         => $hora,
            'especialidad' => $cita['especialidad'],
            'motivo'       => $cita['motivo'],
            'prioridad'    => $cita['prioridad'],
            'estado'       => 'Confirmada',
        ];

        $ok = $this->model->update($data);
        echo json_encode(['success' => $ok]);
    }

    /* =====================================================
       POST — marcar cita como Atendida
    ===================================================== */

    /**
     * atender — Marca una cita como 'Atendida' una vez que se completó la consulta
     *
     * El médico o personal clínico usa este método al finalizar la
     * atención del paciente. Cambia el estado a 'Atendida', lo que
     * la saca del listado de citas pendientes y queda como registro
     * histórico en el sistema.
     *
     *  void
     */
    public function atender(): void
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

        $ok = $this->model->updateEstado($id, 'Atendida');
        echo json_encode(['success' => $ok]);
    }

    /* =====================================================
       POST — simular solicitud de cita (admin/médico)
    ===================================================== */

    /**
     * simularSolicitud — Crea una cita de prueba con datos predeterminados
     *
     * Permite al administrador o personal médico generar una cita de
     * demostración para probar el flujo del sistema. Si no se envían
     * datos en el formulario, usa valores por defecto: especialidad
     * "Medicina General", fecha de hoy y motivo genérico.
     *
     * Funciona igual que agregar(), pero todos los campos son opcionales.
     *
     *  void
     */
    public function simularSolicitud(): void
    {
        if (!$this->user_id) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $especialidad = trim($_POST['especialidad'] ?? 'Medicina General');
        $motivo       = trim($_POST['motivo']       ?? 'Consulta general simulada');
        $fecha        = trim($_POST['fecha']        ?? date('Y-m-d'));
        $hora         = trim($_POST['hora']         ?? '');

        $prioridad_calculada = $this->calcularPrioridad($motivo);
        $prioridad_final     = !empty($_POST['prioridad']) ? $_POST['prioridad'] : $prioridad_calculada;

        $data = [
            'id_usuario'   => (int) $this->user_id,
            'especialidad' => $especialidad,
            'motivo'       => $motivo,
            'fecha'        => $fecha,
            'hora'         => $hora ?: null,
            'prioridad'    => $prioridad_final,
        ];

        $id = $this->model->insert($data);

        echo json_encode([
            'success'             => true,
            'id'                  => $id,
            'prioridad_calculada' => $prioridad_calculada,
            'prioridad_final'     => $prioridad_final,
        ]);
    }

    /* =====================================================
       Priorización automática según palabras clave del motivo
    ===================================================== */

    /**
     * calcularPrioridad — Determina el nivel de urgencia de una cita según su motivo
     *
     * Analiza el texto libre del motivo de la cita buscando palabras clave
     * asociadas a distintos niveles de urgencia médica:
     *
     *   - ALTA: palabras que indican emergencia grave, como "pecho",
     *     "respirar", "desmayo", "infarto", "fractura", "inconsciente".
     *     Estas citas deben atenderse de forma prioritaria.
     *
     *   - MEDIA: palabras que indican un problema real pero no inmediato,
     *     como "fiebre", "dolor", "vómito", "infección", "alergia".
     *
     *   - BAJA: cuando el motivo no contiene ninguna de las palabras
     *     anteriores; se trata de consultas de rutina o preventivas.
     *
     * La búsqueda no distingue mayúsculas de minúsculas (todo se convierte
     * a minúsculas antes de comparar) para evitar que "Fiebre" y "fiebre"
     * den resultados distintos.
     *
     * @param string $motivo Texto libre que describe el motivo de la consulta
     * @return string Nivel de prioridad: 'Alta', 'Media' o 'Baja'
     */
    private function calcularPrioridad(string $motivo): string
    {
        $texto = mb_strtolower($motivo);

        $alta = [
            'pecho', 'respirar', 'dificultad', 'emergencia', 'urgente', 'sangrado',
            'accidente', 'pérdida', 'desmayo', 'fractura', 'infarto', 'apendicitis',
            'convulsión', 'parálisis', 'inconsciente',
        ];

        $media = [
            'fiebre', 'dolor', 'náuseas', 'vómito', 'mareo', 'infección',
            'inflamación', 'herida', 'caída', 'alergia',
        ];

        foreach ($alta  as $k) {
            if (str_contains($texto, $k)) return 'Alta';
        }
        foreach ($media as $k) {
            if (str_contains($texto, $k)) return 'Media';
        }

        return 'Baja';
    }
}
