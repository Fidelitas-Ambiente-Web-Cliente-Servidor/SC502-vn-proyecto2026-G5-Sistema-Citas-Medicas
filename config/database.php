<?php
/**
 * database.php — Conexión a la base de datos MySQL
 *
 * Este archivo se encarga de establecer la comunicación entre la
 * aplicación PHP y el servidor de base de datos MySQL. Define los
 * datos de acceso (host, nombre de base de datos, usuario y contraseña)
 * y proporciona un único método para obtener esa conexión.
 *
 * Es el punto de partida de toda operación con datos: los modelos
 * reciben la conexión que aquí se crea y la usan para ejecutar
 * consultas SQL.
 *
 * Proyecto: Sistema de Citas Médicas CCSS
 * Curso: SC-502 Ambiente Web Cliente Servidor
 * Universidad Fidélitas — Grupo 5
 */

/**
 * Database
 *
 * Clase encargada de crear y devolver la conexión a la base de datos.
 * Centraliza los datos de configuración para que, si cambia el servidor
 * o la contraseña, solo haya que modificar este archivo.
 */
class Database
{

    /** @var string Dirección del servidor de base de datos (nombre del contenedor Docker) */
    private $host = "db";

    /** @var string Nombre de la base de datos del proyecto */
    private $db = "appdb";

    /** @var string Usuario de la base de datos */
    private $user = "appuser";

    /** @var string Contraseña del usuario de la base de datos */
    private $pass = "apppass";

    /**
     * connect — Abre y devuelve la conexión a MySQL
     *
     * Crea un objeto de conexión usando mysqli, verifica que no haya
     * errores al conectar y configura el juego de caracteres para
     * que los textos con acentos y caracteres especiales (ñ, é, ü, etc.)
     * se almacenen y lean correctamente.
     *
     * set_charset('utf8mb4') le indica a MySQL que use el estándar
     * UTF-8 completo, que soporta prácticamente todos los idiomas y
     * símbolos del mundo, evitando que los textos aparezcan con
     * caracteres extraños o "mojibake".
     *
     * @return mysqli Objeto de conexión listo para ejecutar consultas
     */
    public function connect()
    {

        $conn = new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->db
        );

        if ($conn->connect_error) {
            die("Error conexión: " . $conn->connect_error);
        }

        // Forzar UTF-8 para soportar acentos y caracteres especiales
        $conn->set_charset('utf8mb4');

        return $conn;
    }
}
