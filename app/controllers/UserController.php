<?php
/**
 * UserController.php — Controlador de Usuario (capa Controlador del patrón MVC)
 *
 * Este archivo representa la capa "Controlador" del patrón MVC para
 * las acciones relacionadas con los usuarios del sistema. Recibe las
 * peticiones del navegador (formularios de registro, login, perfil,
 * cambio de contraseña), aplica la lógica de negocio necesaria y
 * luego llama al Modelo (User) para leer o guardar datos en la base
 * de datos.
 *
 * El controlador también gestiona la sesión PHP: al iniciar sesión
 * guarda los datos del usuario en $_SESSION, y al cerrar sesión los
 * destruye para proteger la cuenta.
 *
 * Seguridad de contraseñas: las contraseñas NUNCA se guardan en texto
 * plano. Se usa password_hash() al registrar o cambiar la contraseña,
 * que genera un hash (texto cifrado irreversible). Al verificar el
 * login se usa password_verify() para comparar sin revelar la clave.
 *
 * Proyecto: Sistema de Citas Médicas CCSS
 * Curso: SC-502 Ambiente Web Cliente Servidor
 * Universidad Fidélitas — Grupo 5
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';

/**
 * UserController
 *
 * Controlador que gestiona el ciclo de vida del usuario dentro de la
 * aplicación: registro, inicio de sesión, cierre de sesión, consulta
 * de perfil, actualización de datos personales y cambio de contraseña.
 */
class UserController
{

    /** @var User Instancia del modelo User para acceder a la base de datos */
    private $model;

    /**
     * __construct — Inicializa la conexión y el modelo de usuario
     *
     * Al instanciarse, este controlador crea la conexión a la base de
     * datos y prepara el modelo User que usarán todos sus métodos.
     */
    public function __construct()
    {
        $database = new Database();
        $db = $database->connect();

        $this->model = new User($db);
    }

    /* =========================
       REGISTRO
    ========================= */

    /**
     * registro — Procesa el formulario de registro de un nuevo usuario
     *
     * Recoge los datos enviados por el formulario (nombre, apellidos,
     * correo y contraseña), cifra la contraseña usando password_hash()
     * con el algoritmo seguro por defecto de PHP, y llama al modelo
     * para insertar el nuevo usuario en la base de datos.
     *
     * Una vez completado el registro, redirige al usuario a la
     * pantalla de inicio de sesión.
     *
     * @return void Redirige al login tras registrar al usuario
     */
    public function registro()
    {

        $nombre    = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];
        $correo    = $_POST['correo'];

        // password_hash() convierte la contraseña en un hash cifrado.
        // Nunca se guarda la clave original en la base de datos.
        $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $this->model->register($nombre, $apellidos, $correo, $password);

        header("Location: /index.php?vista=login");
        exit;
    }

    /* =========================
       LOGIN
    ========================= */

    /**
     * login — Verifica las credenciales e inicia la sesión del usuario
     *
     * Busca al usuario por su correo electrónico y compara la contraseña
     * ingresada con el hash almacenado usando password_verify(). Si las
     * credenciales son correctas, guarda los datos en la sesión PHP y
     * redirige al panel principal. Si son incorrectas, redirige de vuelta
     * al login con un indicador de error.
     *
     * @return void Redirige al panel principal o al login con error
     */
    public function login()
    {

        $correo   = $_POST['correo'];
        $password = $_POST['password'];

        $user = $this->model->login($correo);

        // password_verify() compara la contraseña ingresada con el hash
        // guardado en la base de datos sin necesidad de descifrarlo
        if ($user && password_verify($password, $user['password'])) {

            // Se guardan datos básicos del usuario en la sesión para
            // identificarlo en las siguientes páginas sin consultar la BD
            $_SESSION['user']        = $user['correo'];
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];

            header("Location: /index.php?vista=main");
            exit;
        } else {
            header("Location: /index.php?vista=login&error=1");
            exit;
        }
    }

    /* =========================
       LOGOUT
    ========================= */

    /**
     * logout — Cierra la sesión del usuario y lo redirige al login
     *
     * Destruye todos los datos de la sesión PHP activa, lo que desconecta
     * al usuario del sistema de forma segura. Luego redirige a la
     * pantalla de inicio de sesión.
     *
     * @return void Redirige al login tras cerrar la sesión
     */
    public function logout()
    {
        session_destroy();

        header("Location: /index.php?vista=login");
        exit;
    }

    /* =========================
       PERFIL — cargar vista
    ========================= */

    /**
     * perfil — Carga la vista del perfil del usuario en sesión
     *
     * Verifica que haya un usuario autenticado (revisando la sesión),
     * consulta sus datos actuales en la base de datos y carga la vista
     * de perfil pasándole esos datos para mostrarlos en pantalla.
     *
     * Si no hay sesión activa, redirige al login.
     *
     * @return void Carga la vista perfil.php o redirige al login
     */
    public function perfil()
    {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            header("Location: /index.php?vista=login");
            exit;
        }

        $userData = $this->model->getById((int) $user_id);
        require __DIR__ . '/../../app/views/perfil.php';
    }

    /* =========================
       ACTUALIZAR PERFIL
    ========================= */

    /**
     * actualizarPerfil — Guarda los cambios de nombre, apellidos y correo
     *
     * Recibe los nuevos datos del formulario de perfil, valida que los
     * campos obligatorios estén completos y llama al modelo para
     * actualizar la base de datos. Si la actualización es exitosa,
     * también actualiza la sesión para que el nombre mostrado en la
     * interfaz refleje el cambio de inmediato.
     *
     *      *
     *  void
     */
    public function actualizarPerfil()
    {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $nombre    = trim($_POST['nombre']    ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $correo    = trim($_POST['correo']    ?? '');

        if (!$nombre || !$apellidos || !$correo) {
            echo json_encode(['success' => false, 'error' => 'Campos requeridos incompletos']);
            return;
        }

        $ok = $this->model->updatePerfil((int) $user_id, $nombre, $apellidos, $correo);

        if ($ok) {
            // Actualizar datos en sesión para que el cambio sea inmediato en la UI
            $_SESSION['user']        = $correo;
            $_SESSION['user_nombre'] = $nombre;
        }

        echo json_encode(['success' => $ok]);
    }

    /* =========================
       CAMBIAR CONTRASEÑA
    ========================= */

    /**
     * cambiarPassword — Procesa el cambio de contraseña del usuario en sesión
     *
     * Realiza tres verificaciones antes de permitir el cambio:
     *   1. Que el usuario esté autenticado (sesión activa).
     *   2. Que la contraseña actual ingresada coincida con la guardada.
     *   3. Que la contraseña nueva y su confirmación sean idénticas.
     *
     * Si todas las verificaciones pasan, cifra la nueva contraseña con
     * password_hash() y la guarda en la base de datos.
     *
     *      *      *
     *  void
     */
    public function cambiarPassword()
    {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }

        $password_actual  = $_POST['password_actual']  ?? '';
        $password_nueva   = $_POST['password_nueva']   ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if (!$password_actual || !$password_nueva || !$password_confirm) {
            echo json_encode(['success' => false, 'error' => 'Todos los campos son requeridos']);
            return;
        }

        if ($password_nueva !== $password_confirm) {
            echo json_encode(['success' => false, 'error' => 'Las contraseñas nuevas no coinciden']);
            return;
        }

        // Se obtienen los datos actuales del usuario para verificar la contraseña
        $userData = $this->model->getById((int) $user_id);
        if (!$userData || !password_verify($password_actual, $userData['password'])) {
            echo json_encode(['success' => false, 'error' => 'La contraseña actual es incorrecta']);
            return;
        }

        // Se cifra la nueva contraseña antes de guardarla
        $hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        $ok   = $this->model->updatePassword((int) $user_id, $hash);

        echo json_encode(['success' => $ok]);
    }
}
