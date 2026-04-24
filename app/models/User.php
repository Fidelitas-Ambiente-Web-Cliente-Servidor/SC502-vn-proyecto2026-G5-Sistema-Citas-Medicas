<?php
/**
 * User.php — Modelo de Usuario (capa Modelo del patrón MVC)
 *
 * Este archivo representa la capa "Modelo" del patrón de diseño MVC
 * para todo lo relacionado con los usuarios del sistema. Su única
 * responsabilidad es comunicarse con la tabla `g5_users` de la base
 * de datos: buscar, crear y actualizar registros de usuarios.
 *
 * El controlador (UserController) llama a los métodos de esta clase
 * cada vez que necesita información de un usuario o desea modificarla.
 * El modelo no decide qué mostrar ni cómo reaccionar; solo trabaja
 * con los datos.
 *
 * Proyecto: Sistema de Citas Médicas CCSS
 * Curso: SC-502 Ambiente Web Cliente Servidor
 * Universidad Fidélitas — Grupo 5
 */

/**
 * User
 *
 * Clase que agrupa todas las operaciones de base de datos relacionadas
 * con los usuarios: iniciar sesión, registrarse, consultar su perfil
 * y actualizar sus datos personales o contraseña.
 *
 * Usa consultas preparadas (prepare/bind_param) en todos sus métodos.
 * Esto significa que los datos del usuario se envían separados del
 * texto de la consulta SQL, lo que evita el ataque conocido como
 * "inyección SQL", donde un usuario malintencionado podría manipular
 * la base de datos escribiendo código SQL en un formulario.
 */
class User {

    /** @var mysqli Conexión activa a la base de datos */
    private $conn;

    /**
     * __construct — Recibe la conexión a la base de datos
     *
     * Al crear un objeto User se le pasa la conexión ya establecida
     * para que todos sus métodos puedan ejecutar consultas.
     *
     * @param mysqli $db Conexión a la base de datos creada por Database::connect()
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /* =========================
       LOGIN
    ========================= */

    /**
     * login — Busca un usuario por su correo electrónico
     *
     * Consulta la tabla `g5_users` para encontrar al usuario cuyo
     * correo coincida con el proporcionado. Devuelve todos sus datos
     * para que el controlador pueda verificar la contraseña.
     *
     * Nota: este método solo busca por correo. La verificación de la
     * contraseña la realiza el controlador con password_verify().
     *
     * @param string $correo Correo electrónico ingresado en el formulario de login
     * @return array|null Arreglo con los datos del usuario, o null si no existe
     */
    public function login($correo) {

        // Se prepara la consulta con "?" como marcador; el valor real
        // se envía por separado con bind_param para evitar inyección SQL
        $stmt = $this->conn->prepare(
            "SELECT * FROM g5_users WHERE correo = ?"
        );

        $stmt->bind_param("s", $correo);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /* =========================
       REGISTRO
    ========================= */

    /**
     * register — Crea un nuevo usuario en la base de datos
     *
     * Inserta una fila en la tabla `g5_users` con el nombre, apellidos,
     * correo y contraseña del nuevo usuario. La contraseña debe llegar
     * ya cifrada (el controlador se encarga de usar password_hash()
     * antes de llamar a este método).
     *
     * @param string $nombre    Nombre del usuario
     * @param string $apellidos Apellidos del usuario
     * @param string $correo    Correo electrónico (usado como nombre de usuario)
     * @param string $password  Contraseña ya cifrada con password_hash()
     * @return bool true si el registro fue exitoso, false en caso de error
     */
    public function register($nombre, $apellidos, $correo, $password, $rol = 'paciente') {

        $stmt = $this->conn->prepare(
            "INSERT INTO g5_users (nombre, apellidos, correo, password, rol)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("sssss", $nombre, $apellidos, $correo, $password, $rol);

        return $stmt->execute();
    }

    /* =========================
       OBTENER POR ID
    ========================= */

    /**
     * getById — Obtiene los datos completos de un usuario por su ID
     *
     * Se usa principalmente para cargar el perfil del usuario que
     * está en sesión, o para verificar su contraseña actual antes
     * de permitirle cambiarla.
     *
     * @param int $id Identificador único del usuario en la tabla `g5_users`
     * @return array|null Arreglo con todos los campos del usuario, o null si no existe
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM g5_users WHERE id = ?"
        );
        // "i" indica que el parámetro es un número entero (integer)
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    /* =========================
       ACTUALIZAR PERFIL
    ========================= */

    /**
     * updatePerfil — Actualiza el nombre, apellidos y correo del usuario
     *
     * Modifica los datos del perfil del usuario identificado por su ID.
     * Solo actualiza los campos de información personal; la contraseña
     * se maneja por separado con updatePassword().
     *
     * @param int    $id        ID del usuario a actualizar
     * @param string $nombre    Nuevo nombre del usuario
     * @param string $apellidos Nuevos apellidos del usuario
     * @param string $correo    Nuevo correo electrónico del usuario
     * @return bool true si la actualización fue exitosa, false en caso de error
     */
    public function updatePerfil(int $id, string $nombre, string $apellidos, string $correo): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE g5_users
             SET nombre = ?, apellidos = ?, correo = ?
             WHERE id = ?"
        );
        // "sssi": tres cadenas de texto y un entero al final (el ID)
        $stmt->bind_param("sssi", $nombre, $apellidos, $correo, $id);
        return $stmt->execute();
    }

    /* =========================
       ACTUALIZAR PASSWORD
    ========================= */

    /**
     * updatePassword — Guarda una nueva contraseña cifrada para el usuario
     *
     * Reemplaza el hash de contraseña almacenado en la base de datos.
     * Recibe el hash ya generado (el controlador llama a password_hash()
     * antes de invocar este método), por lo que nunca se guarda la
     * contraseña en texto plano.
     *
     * @param int    $id   ID del usuario cuya contraseña se va a actualizar
     * @param string $hash Nueva contraseña cifrada con password_hash()
     * @return bool true si la actualización fue exitosa, false en caso de error
     */
    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE g5_users SET password = ? WHERE id = ?"
        );
        // "si": primero una cadena (el hash) y luego un entero (el ID)
        $stmt->bind_param("si", $hash, $id);
        return $stmt->execute();
    }

    /* =========================
       ACTUALIZAR PERFIL COMPLETO (admin)
    ========================= */

    public function updatePerfilAdmin(int $id, string $nombre, string $apellidos, string $correo, string $rol): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE g5_users SET nombre = ?, apellidos = ?, correo = ?, rol = ? WHERE id = ?"
        );
        $stmt->bind_param("ssssi", $nombre, $apellidos, $correo, $rol, $id);
        return $stmt->execute();
    }

    /* =========================
       LISTAR TODOS LOS USUARIOS (admin)
    ========================= */

    public function getAll(): array
    {
        $result = $this->conn->query(
            "SELECT id, nombre, apellidos, correo, rol FROM g5_users ORDER BY id ASC"
        );
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /* =========================
       ACTUALIZAR ROL (admin)
    ========================= */

    public function updateRol(int $id, string $rol): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE g5_users SET rol = ? WHERE id = ?"
        );
        $stmt->bind_param("si", $rol, $id);
        return $stmt->execute();
    }
}
