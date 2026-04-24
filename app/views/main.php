<?php
$usuario  = htmlspecialchars($_SESSION['user']);
$esAdmin  = ($_SESSION['user_rol'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Principal - Sistema de Citas Médicas</title>
  <link rel="stylesheet" href="public/css/style.css">
  <script defer src="public/js/main.js"></script>
</head>

<body>

  <header class="encabezado">
    <img src="/public/img/ccss.png" alt="Logo CCSS" class="logo-ccss">

    <div class="titulo-header">
      <h1>Sistema de Citas Médicas</h1>
      <p>Plataforma de gestión médica digital</p>
    </div>

    <div class="header-usuario">
      <span><?php echo $usuario; ?></span>
      <button id="btnDarkMode" class="btn-darkmode" title="Cambiar tema">🌙</button>
      <a href="/index.php?vista=perfil" class="btn btn-secundario btn-sm">Mi perfil</a>
      <a href="/index.php?accion=logout" class="btn btn-cancelar btn-sm">Cerrar sesión</a>
    </div>
  </header>

  <main class="contenedor">

    <section class="vista">
      <h2>Panel principal</h2>
      <p class="texto-ayuda">Seleccioná el módulo que deseás utilizar.</p>

      <div id="statsGrid" class="stats-grid">
        <div class="spinner-wrap"><div class="spinner"></div></div>
      </div>

      <div class="panel-modulos">

        <a href="/index.php?vista=citas" class="card-modulo">
          <h3>Citas médicas</h3>
          <p>Agendar, cancelar o reprogramar citas</p>
        </a>

        <a href="/index.php?vista=diagnosticos" class="card-modulo">
          <h3>Diagnósticos</h3>
          <p>Registro clínico e historial médico</p>
        </a>

        <a href="/index.php?vista=personalmedico" class="card-modulo">
          <h3>Personal médico</h3>
          <p>Gestión administrativa</p>
        </a>

        <?php if ($esAdmin): ?>
        <a href="/index.php?vista=admin_roles" class="card-modulo card-modulo-admin">
          <h3>Roles y permisos</h3>
          <p>Administración de accesos por usuario</p>
        </a>
        <?php endif; ?>

      </div>
    </section>

  </main>

  <footer class="pie">
    <small>Sistema Médico Digital &bull; Proyecto académico</small>
  </footer>

</body>
</html>
