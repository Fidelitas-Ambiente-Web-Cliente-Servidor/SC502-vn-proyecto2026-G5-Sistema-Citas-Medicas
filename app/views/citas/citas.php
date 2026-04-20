<?php
// Protección de sesión ya verificada en index.php
$usuario = htmlspecialchars($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Citas - Sistema Médico</title>
  <link rel="stylesheet" href="public/css/style.css">
  <script defer src="public/js/modal.js"></script>
  <script defer src="public/js/citas.js"></script>
</head>
<body>

<header class="encabezado">
  <div class="titulo-header">
    <h1>Gestión de Citas</h1>
    <p>Agendá, cancelá o reprogramá tus citas médicas</p>
  </div>
  <div class="header-usuario">
    <span><?php echo $usuario; ?></span>
    <button id="btnDarkMode" class="btn-darkmode" title="Cambiar tema">🌙</button>
    <a href="/index.php?vista=perfil" class="btn btn-secundario btn-sm">Mi perfil</a>
    <a href="/index.php?vista=main" class="btn btn-secundario btn-sm">Panel principal</a>
    <a href="/index.php?accion=logout" class="btn btn-cancelar btn-sm">Cerrar sesión</a>
  </div>
</header>

<main class="contenedor">

  <!-- LISTA DE CITAS -->
  <section class="vista">
    <h2>Mis citas</h2>
    <p class="texto-ayuda">Aquí podés ver, cancelar y reprogramar tus citas activas.</p>

    <div class="acciones">
      <button id="btnNuevaCita" class="btn btn-principal">+ Agendar nueva cita</button>
      <button id="btnVerTodas" class="btn btn-secundario">Ver todas</button>
    </div>

    <!-- Filtro rápido -->
    <div class="filtro-citas">
      <select id="filtroPrioridad">
        <option value="todas">Todas las prioridades</option>
        <option value="Alta">Alta</option>
        <option value="Media">Media</option>
        <option value="Baja">Baja</option>
      </select>
      <select id="filtroEstado">
        <option value="todos">Todos los estados</option>
        <option value="Activa">Activa</option>
        <option value="Cancelada">Cancelada</option>
        <option value="Reprogramada">Reprogramada</option>
      </select>
    </div>

    <!-- Lista renderizada por JS -->
    <div id="listaCitas" class="lista-citas"></div>
  </section>

  <!-- FORMULARIO AGENDAR CITA -->
  <section id="formularioCita" class="vista oculto">
    <form id="formCita" class="tarjeta">
      <h2 id="tituloFormCita">Agendar cita</h2>

      <!-- Campo oculto para reprogramar -->
      <input type="hidden" id="indexEdicion" value="">

      <label for="especialidad">Especialidad</label>
      <select id="especialidad">
        <option value="">Seleccioná una especialidad</option>
        <option value="Medicina General">Medicina General</option>
        <option value="Odontología">Odontología</option>
        <option value="Pediatría">Pediatría</option>
        <option value="Cardiología">Cardiología</option>
        <option value="Traumatología">Traumatología</option>
        <option value="Oftalmología">Oftalmología</option>
        <option value="Ginecología">Ginecología</option>
        <option value="Dermatología">Dermatología</option>
      </select>

      <label for="motivo">Motivo de la consulta</label>
      <input type="text" id="motivo" placeholder="Describe brevemente tu motivo">
      <span id="hintPrioridad" class="hint-prioridad hint-baja" style="display:none">Prioridad sugerida: 🟢 Baja</span>

      <label for="fecha">Fecha</label>
      <input type="date" id="fecha">

      <label for="hora">Hora</label>
      <input type="time" id="hora">

      <label for="prioridad">Prioridad</label>
      <select id="prioridad">
        <option value="Alta">🔴 Alta — síntomas graves o urgentes</option>
        <option value="Media" selected>🟡 Media — molestia moderada</option>
        <option value="Baja">🟢 Baja — consulta de rutina</option>
      </select>

      <span id="mensajeCita" class="mensaje oculto"></span>

      <div class="acciones">
        <button type="submit" class="btn btn-principal">Guardar cita</button>
        <button type="button" id="btnCancelarForm" class="btn btn-secundario">Cancelar</button>
      </div>
    </form>
  </section>

</main>

<footer class="pie">
  <small>Módulo de Gestión de Citas • Sistema Médico Digital</small>
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
