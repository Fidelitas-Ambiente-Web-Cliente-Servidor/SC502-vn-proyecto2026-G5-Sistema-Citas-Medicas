<?php
/**
 * ============================================================
 * index.php — Punto de entrada principal (Front Controller)
 * ============================================================
 *
 * Este archivo es el ÚNICO punto de entrada de la aplicación.
 * Toda solicitud HTTP pasa por aquí primero.
 *
 * Patrón MVC aplicado:
 *   - Este archivo actúa como el "Router" (enrutador).
 *   - Según los parámetros de la URL decide qué controlador
 *     invocar o qué vista mostrar.
 *
 * Tipos de solicitud que maneja:
 *   1. ?api=X       → Devuelve datos al JavaScript del frontend
 *   2. POST ?accion  → Ejecuta una acción (guardar, cancelar, login, etc.)
 *   3. ?vista=X     → Muestra una página HTML al usuario
 *
 * Proyecto: Sistema de Citas Médicas Inteligentes - CCSS
 * Curso: SC-502 Ambiente Web Cliente Servidor
 * Universidad Fidélitas — Grupo 5
 * ============================================================
 */

session_start();

// ── Cargar todos los controladores ──────────────────────────
require './app/controllers/UserController.php';
require './app/controllers/CitasController.php';
require './app/controllers/DiagnosticoController.php';
require './app/controllers/PersonalmedicoController.php';

// ── Determinar vista solicitada (por defecto: panel principal)
$vista   = $_GET['vista'] ?? 'main';
$usuario = new UserController();


/* ============================================================
   1. CERRAR SESIÓN
   Llega por URL: ?accion=logout
   Destruye la sesión y redirige al login.
============================================================ */
if (isset($_GET['accion']) && $_GET['accion'] === 'logout') {
    $usuario->logout();
    exit;
}


/* ============================================================
   2. ENDPOINTS API (responden datos al JavaScript)
   Llegan por URL: ?api=nombre
   Solo accesibles si el usuario tiene sesión activa.

   El JavaScript del frontend usa fetch() para llamar estos
   endpoints y mostrar los datos dinámicamente sin recargar.
============================================================ */
if (isset($_GET['api'])) {

    // Verificar sesión antes de responder
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'No autorizado']);
        exit;
    }

    header('Content-Type: application/json');

    $api        = $_GET['api'];
    $citasCtrl  = new CitasController();
    $diagCtrl   = new DiagnosticoController();
    $pmCtrl     = new PersonalmedicoController();

    switch ($api) {
        // Citas del usuario logueado
        case 'citas':     $citasCtrl->apiListar(); break;

        // Historial médico del usuario logueado
        case 'historial': $diagCtrl->apiListar();  break;

        // Todas las citas (agenda médica completa)
        case 'agenda':    $citasCtrl->apiAgenda(); break;

        // Estadísticas del panel principal
        case 'stats':     $citasCtrl->apiStats();  break;

        // Lista de personal médico
        case 'personal':  $pmCtrl->apiListar();    break;

        default:
            echo json_encode(['success' => false, 'error' => 'Endpoint no encontrado']);
    }
    exit;
}


/* ============================================================
   3. ACCIONES POST (formularios y botones)
   El frontend envía los datos como formulario estándar
   (application/x-www-form-urlencoded)
      
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // La acción puede venir en la URL (?accion=X) o en el cuerpo del formulario
    $accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

    // -- Autenticación --
    if ($accion === 'login')    { $usuario->login();    exit; }
    if ($accion === 'registro') { $usuario->registro(); exit; }

    // -- Módulo de Citas --
    if ($accion === 'agregar_cita')        { header('Content-Type: application/json'); (new CitasController())->agregar();          exit; }
    if ($accion === 'cancelar_cita')       { header('Content-Type: application/json'); (new CitasController())->cancelar();         exit; }
    if ($accion === 'reprogramar_cita')    { header('Content-Type: application/json'); (new CitasController())->reprogramar();      exit; }
    if ($accion === 'confirmar_cita_hora') { header('Content-Type: application/json'); (new CitasController())->confirmarHora();    exit; }
    if ($accion === 'atender_cita')        { header('Content-Type: application/json'); (new CitasController())->atender();          exit; }
    if ($accion === 'simular_solicitud')   { header('Content-Type: application/json'); (new CitasController())->simularSolicitud(); exit; }

    // -- Módulo de Diagnósticos e Historial --
    if ($accion === 'guardar_diagnostico')  { header('Content-Type: application/json'); (new DiagnosticoController())->guardar();    exit; }
    if ($accion === 'eliminar_diagnostico') { header('Content-Type: application/json'); (new DiagnosticoController())->eliminar();   exit; }
    if ($accion === 'borrar_historial')     { header('Content-Type: application/json'); (new DiagnosticoController())->borrarTodo(); exit; }

    // -- Módulo de Perfil de usuario --
    if ($accion === 'actualizar_perfil') { header('Content-Type: application/json'); $usuario->actualizarPerfil(); exit; }
    if ($accion === 'cambiar_password')  { header('Content-Type: application/json'); $usuario->cambiarPassword();  exit; }
}


/* ============================================================
   4. VISTAS (páginas HTML)
   Se muestran según el parámetro ?vista=X de la URL.
   Las vistas protegidas requieren sesión activa.
============================================================ */
switch ($vista) {

    // -- Páginas públicas (no requieren sesión) --
    case 'login':
        require './app/views/auth/login.php';
        break;

    case 'registro':
        require './app/views/auth/registro.php';
        break;

    // -- Páginas protegidas (requieren sesión) --
    case 'perfil':
        if (!isset($_SESSION['user'])) { header("Location: /index.php?vista=login"); exit; }
        $usuario->perfil();
        break;

    case 'citas':
    case 'diagnosticos':
    case 'personalmedico':
    case 'main':
        if (!isset($_SESSION['user'])) { header("Location: /index.php?vista=login"); exit; }

        if      ($vista === 'citas')          require './app/views/citas/citas.php';
        elseif  ($vista === 'diagnosticos')   require './app/views/diagnosticos/diagnosticos.php';
        elseif  ($vista === 'personalmedico') require './app/views/personalmedico/personalmedico.php';
        else                                  require './app/views/main.php';
        break;

    // -- Ruta no encontrada --
    default:
        require './app/views/404.php';
        break;
}
