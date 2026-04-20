<?php
$usuario = htmlspecialchars($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Diagnósticos e Historial - Sistema Médico</title>
  <link rel="stylesheet" href="public/css/style.css">
  <script defer src="public/js/modal.js"></script>
  <script defer src="public/js/diagnosticos.js"></script>
</head>
<body>

<header class="encabezado">
  <div class="titulo-header">
    <h1>Diagnósticos e Historial Médico</h1>
    <p>Registro clínico y seguimiento del paciente</p>
  </div>
  <div class="header-usuario">
    <span><?php echo $usuario; ?></span>
    <button id="btnDarkMode" class="btn-darkmode" title="Cambiar tema">🌙</button>
    <!-- Campana de notificaciones -->
    <button id="btnCampana" class="btn-campana" title="Ver notificaciones">
      🔔 <span id="badgeNotif" class="badge-notif oculto">0</span>
    </button>
    <a href="/index.php?vista=main" class="btn btn-secundario btn-sm">Panel principal</a>
    <a href="/index.php?accion=logout" class="btn btn-cancelar btn-sm">Cerrar sesión</a>
  </div>
</header>

<main class="contenedor">

  <!-- =====================================================
       PANEL DE NOTIFICACIONES (colapsable)
  ====================================================== -->
  <section id="seccionNotificaciones" class="vista oculto">
    <div class="notif-header">
      <h2>Notificaciones del sistema</h2>
      <div class="acciones" style="margin-top:0; justify-content:flex-end;">
        <button id="btnLimpiarNotif" class="btn btn-secundario btn-sm">Limpiar todas</button>
        <button id="btnCerrarNotif"  class="btn btn-secundario btn-sm">Cerrar</button>
      </div>
    </div>
    <div id="panelNotificaciones" class="panel-notificaciones">
      <p class="sin-notificaciones texto-ayuda">No hay notificaciones recientes.</p>
    </div>
  </section>

  <!-- =====================================================
       REGISTRO DE DIAGNÓSTICO
  ====================================================== -->
  <section class="vista">
    <h2>Registrar diagnóstico</h2>
    <p class="texto-ayuda">Completá los datos clínicos del paciente para guardar el diagnóstico.</p>

    <form id="formDiagnostico" class="tarjeta">

      <label for="paciente">Nombre del paciente</label>
      <input type="text" id="paciente" placeholder="Ej: Juan Pérez">

      <label for="sintomas">Síntomas reportados</label>
      <input type="text" id="sintomas" placeholder="Ej: dolor de cabeza, fiebre, náuseas">

      <label for="diagnostico">Diagnóstico</label>
      <input type="text" id="diagnostico" placeholder="Ej: Gripe estacional, Hipertensión">

      <label for="tratamiento">Tratamiento indicado</label>
      <input type="text" id="tratamiento" placeholder="Ej: Paracetamol 500mg cada 8h, reposo">

      <label for="notas">Notas médicas adicionales</label>
      <textarea id="notas" rows="3" placeholder="Observaciones, advertencias o seguimiento recomendado..."></textarea>

      <span id="mensajeDiag" class="mensaje oculto"></span>

      <div class="acciones">
        <button type="submit" class="btn btn-principal">Guardar diagnóstico</button>
        <button type="button" id="btnLimpiarForm" class="btn btn-secundario">Limpiar</button>
      </div>
    </form>
  </section>

  <!-- =====================================================
       HISTORIAL MÉDICO
  ====================================================== -->
  <section class="vista">
    <h2>Historial médico</h2>
    <p class="texto-ayuda">Consultá los diagnósticos registrados. Podés filtrar por fecha, diagnóstico o síntomas.</p>

    <!-- Buscador y filtros -->
    <div class="historial-filtros tarjeta">
      <input type="text" id="buscadorHistorial" placeholder="🔍 Buscar por diagnóstico, síntomas o paciente...">
      <div class="historial-filtros-fila">
        <input type="date" id="filtroFechaHistorial" title="Filtrar por fecha">
        <button id="btnLimpiarFiltros" class="btn btn-secundario btn-sm">Quitar filtros</button>
        <button id="btnBorrarHistorial" class="btn btn-cancelar btn-sm">Borrar historial</button>
        <button id="btnImprimir" class="btn btn-secundario btn-sm">🖨️ Imprimir</button>
      </div>
    </div>

    <!-- Contador -->
    <p id="contadorHistorial" class="texto-ayuda" style="margin-top:10px;"></p>

    <!-- Cards del historial -->
    <div id="historialMedico" class="historial-lista"></div>
  </section>

</main>

<footer class="pie">
  <small>Módulo de Diagnósticos e Historial • Sistema Médico Digital</small>
</footer>
<script>
  (function(){
    const on = localStorage.getItem('darkMode')==='1';
    document.body.classList.toggle('dark-mode',on);
    const b=document.getElementById('btnDarkMode');
    if(b) b.textContent = on?'☀️':'🌙';
    b && b.addEventListener('click',()=>{
      const n=!document.body.classList.contains('dark-mode');
      document.body.classList.toggle('dark-mode',n);
      localStorage.setItem('darkMode',n?'1':'0');
      b.textContent=n?'☀️':'🌙';
    });
  })();
</script>
</body>
</html>
