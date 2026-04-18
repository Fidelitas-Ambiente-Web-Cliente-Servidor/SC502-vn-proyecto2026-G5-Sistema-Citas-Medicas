<?php
/**
 * Cita.php — Modelo de Citas Médicas (capa Modelo del patrón MVC)
 *
 * Este archivo representa la capa "Modelo" del patrón MVC para todo
 * lo relacionado con las citas médicas. Se encarga exclusivamente de
 * comunicarse con la tabla `g5_citas` de la base de datos: consultar,
 * insertar y actualizar registros de citas.
 *
 * El controlador CitasController usa esta clase para responder a las
 * acciones del usuario (agendar, cancelar, reprogramar, confirmar y
 * marcar como atendida una cita) sin mezclar la lógica de presentación
 * con el acceso a datos.
 *
 * Proyecto: Sistema de Citas Médicas CCSS
 * Curso: SC-502 Ambiente Web Cliente Servidor
 * Universidad Fidélitas — Grupo 5
 */

/**
 * Cita
 *
 * Clase que agrupa todas las operaciones de base de datos sobre la
 * tabla `g5_citas`. Permite obtener citas de un usuario específico,
 * listar la agenda completa, crear nuevas citas, cambiar su estado
 * y actualizar todos sus campos.
 */
class Cita
{
    /** @var mysqli Conexión activa a la base de datos */
    private $conn;

    /**
     * __construct — Recibe la conexión a la base de datos
     *
     * @param mysqli $db Conexión creada por Database::connect()
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /* =====================================================
       Citas del usuario autenticado
    ===================================================== */

    /**
     * getByUsuario — Obtiene todas las citas de un usuario específico
     *
     * Devuelve la lista de citas registradas para el usuario indicado,
     * ordenadas por fecha y hora de forma ascendente (las más próximas
     * primero). Esta información se muestra en el panel personal del
     * paciente.
     *
     * @param int $id_usuario ID del usuario cuyas citas se quieren consultar
     * @return array Arreglo de filas; cada fila es un arreglo con los campos de la cita
     */
    public function getByUsuario(int $id_usuario): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM g5_citas
             WHERE id_usuario = ?
             ORDER BY fecha, hora"
        );
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /* =====================================================
       Todas las citas (agenda médica)
    ===================================================== */

    /**
     * getAll — Obtiene la agenda completa de citas con datos del paciente
     *
     * Consulta todas las citas registradas en el sistema, uniendo la
     * tabla de citas con la de usuarios para incluir el nombre y
     * apellidos del paciente. Esta vista la usa el personal médico o
     * administrador para ver la agenda general.
     *
     * @return array Arreglo de filas con los campos de la cita y el nombre del paciente
     */
    public function getAll(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT c.*, u.nombre, u.apellidos
             FROM g5_citas c
             JOIN g5_users u ON c.id_usuario = u.id
             ORDER BY c.fecha, c.hora"
        );
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /* =====================================================
       Insertar nueva cita — retorna lastInsertId
    ===================================================== */

    /**
     * insert — Crea una nueva cita en la base de datos
     *
     * Inserta una fila en la tabla `g5_citas` con todos los datos de
     * la cita. El estado inicial siempre se establece como 'Activa'.
     * Devuelve el ID generado automáticamente por la base de datos para
     * que el controlador pueda confirmárselo al usuario.
     *
     * @param array $data Arreglo asociativo con los campos:
     *                    'id_usuario', 'especialidad', 'motivo',
     *                    'fecha', 'hora', 'prioridad'
     * @return int ID de la cita recién creada (0 si hubo un error)
     */
    public function insert(array $data): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO g5_citas
                (id_usuario, especialidad, motivo, fecha, hora, prioridad, estado)
             VALUES (?, ?, ?, ?, ?, ?, 'Activa')"
        );

        // "i" = entero (id_usuario), "sssss" = cinco cadenas de texto
        $stmt->bind_param(
            "isssss",
            $data['id_usuario'],
            $data['especialidad'],
            $data['motivo'],
            $data['fecha'],
            $data['hora'],
            $data['prioridad']
        );

        $stmt->execute();
        return (int) $this->conn->insert_id;
    }

    /* =====================================================
       Actualizar solo el estado
    ===================================================== */

    /**
     * updateEstado — Cambia únicamente el estado de una cita
     *
     * Permite pasar una cita a estados como 'Cancelada' o 'Atendida'
     * sin tocar los demás campos. Es la operación más simple de
     * actualización y la usan los métodos cancelar() y atender()
     * del controlador.
     *
     * @param int    $id     ID de la cita a modificar
     * @param string $estado Nuevo estado: 'Activa', 'Confirmada', 'Reprogramada',
     *                       'Cancelada' o 'Atendida'
     * @return bool true si se actualizó correctamente, false en caso de error
     */
    public function updateEstado(int $id, string $estado): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE g5_citas SET estado = ? WHERE id = ?"
        );
        $stmt->bind_param("si", $estado, $id);
        return $stmt->execute();
    }

    /* =====================================================
       Actualizar datos completos de la cita
    ===================================================== */

    /**
     * update — Actualiza todos los campos de una cita existente
     *
     * Se usa cuando se reprograma una cita (cambia fecha, hora y estado)
     * o cuando el médico confirma la hora exacta. Requiere que el
     * arreglo $data contenga todos los campos necesarios para no
     * perder información previa.
     *
     * @param array $data Arreglo asociativo con los campos:
     *                    'id', 'fecha', 'hora', 'especialidad',
     *                    'motivo', 'prioridad', 'estado'
     * @return bool true si la actualización fue exitosa, false en caso de error
     */
    public function update(array $data): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE g5_citas
             SET fecha = ?, hora = ?, especialidad = ?,
                 motivo = ?, prioridad = ?, estado = ?
             WHERE id = ?"
        );
        // "ssssss" = seis cadenas de texto; "i" = el ID al final (entero)
        $stmt->bind_param(
            "ssssssi",
            $data['fecha'],
            $data['hora'],
            $data['especialidad'],
            $data['motivo'],
            $data['prioridad'],
            $data['estado'],
            $data['id']
        );
        return $stmt->execute();
    }

    /* =====================================================
       Obtener cita por ID
    ===================================================== */

    /**
     * getById — Busca y devuelve una cita específica por su ID
     *
     * Se usa antes de actualizar una cita para recuperar los datos
     * actuales y conservar los campos que no se van a modificar
     * (por ejemplo, mantener la especialidad si solo se cambia la hora).
     *
     * @param int $id ID de la cita que se desea consultar
     * @return array|null Arreglo con todos los campos de la cita, o null si no existe
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM g5_citas WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    /* =====================================================
       Contar citas activas de un usuario
    ===================================================== */

    /**
     * countActivas — Cuenta las citas vigentes de un usuario
     *
     * Devuelve la cantidad de citas que están en estado 'Activa',
     * 'Confirmada' o 'Reprogramada' para el usuario indicado. Este
     * número se muestra en el panel de estadísticas del paciente
     * como indicador de sus citas pendientes.
     *
     * @param int $id_usuario ID del usuario del que se quieren contar las citas activas
     * @return int Número total de citas vigentes del usuario
     */
    public function countActivas(int $id_usuario): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM g5_citas
             WHERE id_usuario = ?
               AND estado IN ('Activa','Confirmada','Reprogramada')"
        );
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    /* =====================================================
       Contar citas de prioridad Alta (stats globales)
    ===================================================== */

    /**
     * countAlta — Cuenta el total de citas con prioridad Alta en todo el sistema
     *
     * Esta cifra se incluye en el panel de estadísticas globales para
     * que el personal médico tenga visibilidad rápida de cuántas citas
     * urgentes están registradas actualmente.
     *
     * @return int Número total de citas con prioridad 'Alta' en la base de datos
     */
    public function countAlta(): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM g5_citas
             WHERE prioridad = 'Alta'"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }
}
