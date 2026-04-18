<?php
/**
 * Diagnostico.php — Modelo de Historial Médico (capa Modelo del patrón MVC)
 *
 * Este archivo representa la capa "Modelo" del patrón MVC para el
 * historial médico de los pacientes. Se comunica con la tabla
 * `g5_historial_medico` de la base de datos y ofrece operaciones
 * para consultar, crear y eliminar registros de diagnósticos.
 *
 * El controlador DiagnosticoController delega en esta clase todas
 * las interacciones con la base de datos, manteniendo la separación
 * de responsabilidades propia del patrón MVC.
 *
 * Proyecto: Sistema de Citas Médicas CCSS
 * Curso: SC-502 Ambiente Web Cliente Servidor
 * Universidad Fidélitas — Grupo 5
 */

/**
 * Diagnostico
 *
 * Clase que maneja todas las operaciones de base de datos relacionadas
 * con el historial médico de los usuarios. Permite ver el historial
 * de un paciente, agregar nuevos diagnósticos, eliminar registros
 * individuales o borrar todo el historial de una vez.
 */
class Diagnostico
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
       Historial médico del usuario
    ===================================================== */

    /**
     * getByUsuario — Obtiene el historial médico de un paciente específico
     *
     * Devuelve todos los registros de diagnóstico registrados para el
     * usuario indicado, ordenados del más reciente al más antiguo.
     * Esta lista se muestra en la sección de historial médico del
     * panel del paciente.
     *
     * @param int $id_usuario ID del usuario cuyo historial se desea consultar
     * @return array Arreglo de registros; cada uno contiene los campos del historial
     */
    public function getByUsuario(int $id_usuario): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM g5_historial_medico
             WHERE id_usuario = ?
             ORDER BY fecha DESC"
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
       Insertar registro — retorna lastInsertId
    ===================================================== */

    /**
     * insert — Crea un nuevo registro en el historial médico
     *
     * Agrega una fila en la tabla `g5_historial_medico` con todos
     * los datos clínicos del diagnóstico. Devuelve el ID generado
     * por la base de datos para confirmación.
     *
     * @param array $data Arreglo asociativo con los campos:
     *                    'id_usuario', 'paciente', 'sintomas',
     *                    'diagnostico', 'tratamiento', 'notas', 'fecha'
     * @return int ID del registro recién creado (0 si hubo un error)
     */
    public function insert(array $data): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO g5_historial_medico
                (id_usuario, paciente, sintomas, diagnostico, tratamiento, notas, fecha)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        // "i" = entero (id_usuario); "ssssss" = seis cadenas de texto
        $stmt->bind_param(
            "issssss",
            $data['id_usuario'],
            $data['paciente'],
            $data['sintomas'],
            $data['diagnostico'],
            $data['tratamiento'],
            $data['notas'],
            $data['fecha']
        );

        $stmt->execute();
        return (int) $this->conn->insert_id;
    }

    /* =====================================================
       Eliminar registro — verifica que pertenezca al usuario
    ===================================================== */

    /**
     * delete — Elimina un registro individual del historial médico
     *
     * Borra el registro cuyo ID coincida, pero solo si también pertenece
     * al usuario indicado. Esta doble verificación es una medida de
     * seguridad para que un usuario no pueda borrar registros ajenos.
     *
     * @param int $id         ID del registro de historial a eliminar
     * @param int $id_usuario ID del usuario propietario del registro
     * @return bool true si se eliminó algún registro, false si no se encontró
     */
    public function delete(int $id, int $id_usuario): bool
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM g5_historial_medico
             WHERE id = ? AND id_usuario = ?"
        );
        $stmt->bind_param("ii", $id, $id_usuario);
        $stmt->execute();
        // affected_rows indica cuántas filas fueron afectadas; > 0 significa éxito
        return $stmt->affected_rows > 0;
    }

    /* =====================================================
       Contar registros de un usuario
    ===================================================== */

    /**
     * countByUsuario — Cuenta los registros de historial de un usuario
     *
     * Devuelve el número total de diagnósticos registrados para el
     * usuario indicado. Este dato se muestra en el panel de estadísticas
     * del paciente.
     *
     * @param int $id_usuario ID del usuario del que se quieren contar los registros
     * @return int Cantidad de registros en el historial del usuario
     */
    public function countByUsuario(int $id_usuario): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM g5_historial_medico
             WHERE id_usuario = ?"
        );
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    /* =====================================================
       Contar todos los registros (stats globales)
    ===================================================== */

    /**
     * countAll — Cuenta el total de registros de historial en todo el sistema
     *
     * Devuelve la suma de todos los diagnósticos registrados sin importar
     * a qué usuario pertenezcan. Se usa para mostrar estadísticas globales
     * en el panel administrativo o médico.
     *
     * @return int Número total de registros en la tabla `g5_historial_medico`
     */
    public function countAll(): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total FROM g5_historial_medico"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    /* =====================================================
       Eliminar todo el historial de un usuario
    ===================================================== */

    /**
     * deleteAllByUsuario — Borra todo el historial médico de un usuario
     *
     * Elimina en una sola operación todos los registros de diagnóstico
     * pertenecientes al usuario indicado. Se usa cuando el usuario
     * solicita borrar su historial completo desde el panel de perfil.
     *
     * @param int $id_usuario ID del usuario cuyo historial se desea eliminar por completo
     * @return bool true si la operación fue exitosa, false en caso de error
     */
    public function deleteAllByUsuario(int $id_usuario): bool
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM g5_historial_medico WHERE id_usuario = ?"
        );
        $stmt->bind_param("i", $id_usuario);
        return $stmt->execute();
    }
}
