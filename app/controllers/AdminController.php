<?php
/**
 * AdminController.php — Controlador del panel de administración
 *
 * Gestiona las acciones exclusivas del rol 'admin':
 *   - Listar todos los usuarios del sistema
 *   - Crear un nuevo usuario con cualquier rol
 *   - Editar datos y rol de un usuario existente
 *   - Cambiar el rol de un usuario
 *
 * Proyecto: Sistema de Citas Médicas CCSS
 * Curso: SC-502 Ambiente Web Cliente Servidor
 * Universidad Fidélitas — Grupo 5
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AdminController
{
    private $model;

    private const ROLES_VALIDOS = ['paciente', 'medico', 'admin'];

    public function __construct()
    {
        $database    = new Database();
        $db          = $database->connect();
        $this->model = new User($db);
    }

    private function soloAdmin(): bool
    {
        return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin';
    }

    /* =====================================================
       API — lista todos los usuarios
    ===================================================== */

    public function apiListarUsuarios(): void
    {
        if (!$this->soloAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
            return;
        }

        $usuarios = $this->model->getAll();
        echo json_encode(['success' => true, 'data' => $usuarios]);
    }

    /* =====================================================
       POST — crear usuario (admin puede asignar cualquier rol)
    ===================================================== */

    public function crearUsuario(): void
    {
        if (!$this->soloAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
            return;
        }

        $nombre    = trim($_POST['nombre']    ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $correo    = trim($_POST['correo']    ?? '');
        $password  = $_POST['password']       ?? '';
        $rol       = trim($_POST['rol']       ?? 'paciente');

        if (!$nombre || !$apellidos || !$correo || !$password) {
            echo json_encode(['success' => false, 'error' => 'Todos los campos son requeridos']);
            return;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Correo electrónico inválido']);
            return;
        }

        if (!in_array($rol, self::ROLES_VALIDOS, true)) {
            echo json_encode(['success' => false, 'error' => 'Rol inválido']);
            return;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres']);
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ok   = $this->model->register($nombre, $apellidos, $correo, $hash, $rol);

        if (!$ok) {
            echo json_encode(['success' => false, 'error' => 'No se pudo crear el usuario. El correo podría ya estar en uso.']);
            return;
        }

        echo json_encode(['success' => true]);
    }

    /* =====================================================
       POST — editar datos y rol de un usuario
    ===================================================== */

    public function editarUsuario(): void
    {
        if (!$this->soloAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
            return;
        }

        $id        = (int) ($_POST['id']        ?? 0);
        $nombre    = trim($_POST['nombre']       ?? '');
        $apellidos = trim($_POST['apellidos']    ?? '');
        $correo    = trim($_POST['correo']       ?? '');
        $rol       = trim($_POST['rol']          ?? '');

        if (!$id || !$nombre || !$apellidos || !$correo || !$rol) {
            echo json_encode(['success' => false, 'error' => 'Todos los campos son requeridos']);
            return;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Correo electrónico inválido']);
            return;
        }

        if (!in_array($rol, self::ROLES_VALIDOS, true)) {
            echo json_encode(['success' => false, 'error' => 'Rol inválido']);
            return;
        }

        // No permitir que el admin se cambie su propio rol por seguridad
        $miId = (int) ($_SESSION['user_id'] ?? 0);
        if ($id === $miId) {
            $rolActual = $this->model->getById($id)['rol'] ?? 'admin';
            if ($rol !== $rolActual) {
                echo json_encode(['success' => false, 'error' => 'No podés cambiar tu propio rol']);
                return;
            }
        }

        $ok = $this->model->updatePerfilAdmin($id, $nombre, $apellidos, $correo, $rol);
        echo json_encode(['success' => $ok]);
    }

    /* =====================================================
       POST — actualizar solo el rol (acceso rápido desde tabla)
    ===================================================== */

    public function actualizarRol(): void
    {
        if (!$this->soloAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
            return;
        }

        $id  = (int) ($_POST['id']  ?? 0);
        $rol = trim($_POST['rol']   ?? '');

        if (!$id || !in_array($rol, self::ROLES_VALIDOS, true)) {
            echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
            return;
        }

        if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
            echo json_encode(['success' => false, 'error' => 'No podés cambiar tu propio rol']);
            return;
        }

        $ok = $this->model->updateRol($id, $rol);
        echo json_encode(['success' => $ok]);
    }
}
