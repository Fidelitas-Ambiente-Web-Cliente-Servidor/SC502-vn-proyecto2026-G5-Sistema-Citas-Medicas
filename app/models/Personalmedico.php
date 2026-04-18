<?php
/**
 * Personalmedico.php — Modelo de Personal Médico (capa Modelo del patrón MVC)
 *
 * Este archivo representa la capa "Modelo" del patrón MVC para la
 * gestión del personal médico registrado en el sistema. Se encarga
 * de consultar la tabla `g5_personalmedico` de la base de datos.
 *
 * Su información se usa tanto para mostrar el directorio de médicos
 * disponibles como para calcular estadísticas en el panel principal
 * de la aplicación.
 *
 * Proyecto: Sistema de Citas Médicas CCSS
 * Curso: SC-502 Ambiente Web Cliente Servidor
 * Universidad Fidélitas — Grupo 5
 */

/**
 * Personalmedico
 *
 * Clase que agrupa las consultas de base de datos sobre el personal
 * médico del sistema. Permite obtener el listado completo de médicos
 * y contar cuántos hay registrados en total.
 */
class Personalmedico
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
       Obtener todo el personal médico
    ===================================================== */

    /**
     * getAll — Devuelve el listado completo del personal médico
     *
     * Consulta todos los registros de la tabla `g5_personalmedico`
     * ordenados alfabéticamente por nombre. Esta lista se muestra
     * en la vista de directorio médico de la aplicación.
     *
     * @return array Arreglo de filas; cada una representa un médico con sus datos
     */
    public function getAll(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM g5_personalmedico ORDER BY nombre"
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
       Contar personal médico
    ===================================================== */

    /**
     * getCount — Cuenta el total de médicos registrados en el sistema
     *
     * Devuelve el número total de registros en la tabla `g5_personalmedico`.
     * Este dato se usa en el panel de estadísticas para mostrar cuántos
     * médicos están disponibles en la clínica.
     *
     * @return int Número total de médicos registrados
     */
    public function getCount(): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total FROM g5_personalmedico"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }
}
