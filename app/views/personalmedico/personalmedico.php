<?php
$usuario = htmlspecialchars($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Personal Médico - Sistema Médico</title>
  <link rel="stylesheet" href="/public/css/style.css">
  <script defer src="/public/js/modal.js"></script>
  <script defer src="/public/js/personalmedico.js"></script>
</head>
<body>

<header class="encabezado">
  <div class="titulo-header">
    <h1>Gestión de Agenda Médica</h1>
    <p>Panel de control para doctores y personal de salud</p>
  </div>
  <div class="header-usuario">
    <span><?php echo $usuario; ?></span>
    <button id="btnDarkMode" class="btn-darkmode" title="Cambiar tema">🌙</button>
    <a href="/index.php?vista=main" class="btn btn-secundario btn-sm">Panel principal</a>
    <a href="/index.php?accion=logout" class="btn btn-cancelar btn-sm">Cerrar sesión</a>
  </div>
</header>

<main class="contenedor">

  <!-- NOTIFICACIONES -->
  <section class="vista">
    <h2>Notificaciones del sistema</h2>
    <div id="panelNotificaciones" class="panel-notificaciones">
      <p class="sin-notificaciones texto-ayuda">No hay solicitudes nuevas</p>
    </div>
  </section>

  <!-- AGENDA MÉDICA -->
  <section class="vista">
    <h2>Agenda Médica y Gestión de Pacientes</h2>

    <div class="filtros-agenda tarjeta">
      <div class="campo-filtro">
        <label>Filtrar por fecha</label>
        <input type="date" id="filtroFecha">
      </div>
      <div class="campo-filtro">
        <label>Estado</label>
        <select id="filtroEstado">
          <option value="todos">Todos los estados</option>
          <option value="Activa">Activa</option>
          <option value="Confirmada">Confirmada</option>
          <option value="Atendida">Atendida</option>
          <option value="Cancelada">Cancelada</option>
        </select>
      </div>
      <div class="campo-filtro">
        <label>Prioridad</label>
        <select id="filtroPrioridad">
          <option value="todas">Todas</option>
          <option value="Alta">Alta</option>
          <option value="Media">Media</option>
          <option value="Baja">Baja</option>
        </select>
      </div>
    </div>

    <div id="listaAgenda" class="grid-agenda"></div>
  </section>

  <!-- SIMULADOR DE SOLICITUD -->
  <section class="vista">
    <h2>Simulador de Solicitud</h2>
    <p class="texto-ayuda">Usá esto para generar una nueva solicitud de cita y probar la asignación de horarios.</p>

    <div class="tarjeta form-simulador">
      <div class="sim-campos">
        <div>
          <label>Nombre del paciente</label>
          <input type="text" id="simNombre" placeholder="Ej: María González">
        </div>
        <div>
          <label>Especialidad</label>
          <select id="simEspecialidad">
            <option value="Medicina General">Medicina General</option>
            <option value="Odontología">Odontología</option>
            <option value="Pediatría">Pediatría</option>
            <option value="Cardiología">Cardiología</option>
            <option value="Traumatología">Traumatología</option>
          </select>
        </div>
        <div>
          <label>Fecha solicitada</label>
          <input type="date" id="simFecha">
        </div>
        <div>
          <label>Prioridad</label>
          <select id="simPrioridad">
            <option value="Alta">Alta</option>
            <option value="Media" selected>Media</option>
            <option value="Baja">Baja</option>
          </select>
        </div>
      </div>
      <div class="acciones">
        <button id="btnSimular" class="btn btn-principal">Simular solicitud</button>
      </div>
    </div>
  </section>

</main>

<footer class="pie">
  <small>Módulo de Gestión del Personal Médico • Sistema Médico Digital</small>
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
