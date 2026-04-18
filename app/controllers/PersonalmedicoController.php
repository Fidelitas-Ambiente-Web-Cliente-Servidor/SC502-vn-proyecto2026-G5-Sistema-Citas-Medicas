<?php
/**
 * PersonalmedicoController.php — Controlador de Personal Médico
 *
 * Este archivo forma parte de la capa "Controlador" del patrón MVC.
 * Se encarga de manejar las solicitudes relacionadas con el directorio
 * de personal médico registrado en el sistema.
 *
 * Actualmente expone un único endpoint de tipo API:
 *   - GET ?api=personal → Devuelve la lista completa de médicos
 *
 * El JavaScript de la vista de personal médico llama a este endpoint
 * usando fetch() para mostrar el directorio de médicos disponibles
 * sin necesidad de recargar la página.
 *
 * Proyecto: Sistema de Citas Médicas CCSS
 * Curso: SC-502 Ambiente Web Cliente Servidor
 * Universidad Fidélitas — Grupo 5
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Personalmedico.php';

/**
 * PersonalmedicoController
 *
 * Controlador que gestiona las consultas sobre el personal médico
 * disponible en la clínica.  *  */
class PersonalmedicoController
{
    /** @var Personalmedico Modelo para consultar el directorio de personal médico */
    private $model;

    /** @var int|null ID del usuario en sesión; null si no hay sesión activa */
    private $user_id;

    /**
     * __construct — Inicializa la conexión, el modelo y el ID de sesión
     *
     * Al instanciarse, establece la conexión a la base de datos,
     * prepara el modelo Personalmedico y captura el ID del usuario
     * en sesión para la verificación de autenticación.
     */
    public function __construct()
    {
        $database = new Database();
        $db       = $database->connect();

        $this->model   = new Personalmedico($db);
        $this->user_id = $_SESSION['user_id'] ?? null;
    }

    /* =====================================================
       API — lista todo el personal médico
    ===================================================== */

    /**
     * apiListar — Devuelve el directorio completo del personal médico
     *
     * Endpoint de la API que consulta todos los registros de la tabla
     * `g5_personalmedico` y los devuelve ordenados alfabéticamente por
     * nombre. El JavaScript de la vista de personal médico llama a este
     * método con fetch() para mostrar el directorio sin recargar la página.
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

        $personal = $this->model->getAll();
        echo json_encode(['success' => true, 'data' => $personal]);
    }
}
