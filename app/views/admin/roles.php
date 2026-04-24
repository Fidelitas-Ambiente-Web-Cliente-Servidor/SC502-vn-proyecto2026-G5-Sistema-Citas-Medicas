<?php
$usuario = htmlspecialchars($_SESSION['user']);
$miId    = (int) ($_SESSION['user_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administración de Usuarios - Sistema de Citas Médicas</title>
  <link rel="stylesheet" href="public/css/style.css">
  <script defer src="public/js/admin.js"></script>
</head>

<body>

  <!-- ── ENCABEZADO ─────────────────────────────────────── -->
  <header class="encabezado">
    <img src="/public/img/ccss.png" alt="Logo CCSS" class="logo-ccss">
    <div class="titulo-header">
      <h1>Sistema de Citas Médicas</h1>
      <p>Panel de Administración</p>
    </div>
    <div class="header-usuario">
      <span><?php echo $usuario; ?></span>
      <button id="btnDarkMode" class="btn-darkmode" title="Cambiar tema">🌙</button>
      <a href="index.php?vista=main" class="btn btn-secundario btn-sm">Panel principal</a>
      <a href="index.php?accion=logout" class="btn btn-cancelar btn-sm">Cerrar sesión</a>
    </div>
  </header>

  <!-- ── CONTENIDO ──────────────────────────────────────── -->
  <main class="contenedor">
    <section class="vista">

      <!-- Título + botón nuevo usuario -->
      <div class="admin-header-row">
        <div>
          <h2>Administración de usuarios</h2>
          <p class="texto-ayuda">Creá, editá y asigná roles a los usuarios del sistema.</p>
        </div>
        <button class="btn btn-confirmar" onclick="abrirModalCrear()">+ Nuevo usuario</button>
      </div>

      <!-- Leyenda de roles -->
      <div class="roles-leyenda">
        <div class="rol-item rol-item-admin">
          <span class="badge badge-admin">Admin</span>
          Control total del sistema
        </div>
        <div class="rol-item rol-item-medico">
          <span class="badge badge-medico">Médico</span>
          Confirmar citas y registrar diagnósticos
        </div>
        <div class="rol-item rol-item-paciente">
          <span class="badge badge-paciente">Paciente</span>
          Gestionar sus propias citas
        </div>
      </div>

      <!-- Mensaje de feedback -->
      <div id="msgAdmin" class="mensaje oculto"></div>

      <!-- Tabla de usuarios -->
      <div id="tablaWrap" class="tabla-wrap">
        <div class="spinner-wrap"><div class="spinner"></div></div>
      </div>

    </section>
  </main>

  <!-- ── FOOTER ─────────────────────────────────────────── -->
  <footer class="pie">
    <small>Sistema Médico Digital &bull; Proyecto académico</small>
  </footer>

  <!-- ── MODAL CREAR / EDITAR USUARIO ──────────────────── -->
  <div id="modalOverlay" class="modal-overlay oculto" onclick="cerrarModalSiClick(event)">
    <div class="modal-box">

      <h3 id="modalTitulo">Nuevo usuario</h3>

      <form id="formUsuario" onsubmit="submitUsuario(event)">
        <input type="hidden" id="campoId" name="id" value="">

        <div class="admin-form-grid">
          <div class="campo">
            <label>Nombre</label>
            <input type="text" id="campoNombre" name="nombre" required placeholder="Ej. María">
          </div>
          <div class="campo">
            <label>Apellidos</label>
            <input type="text" id="campoApellidos" name="apellidos" required placeholder="Ej. González Vega">
          </div>
          <div class="campo admin-col-full">
            <label>Correo electrónico</label>
            <input type="email" id="campoCorreo" name="correo" required placeholder="usuario@ccss.cr">
          </div>
          <div class="campo" id="campoPasswordWrap">
            <label>Contraseña <span class="texto-ayuda" id="passHint">(mín. 6 caracteres)</span></label>
            <input type="password" id="campoPassword" name="password" placeholder="Contraseña">
          </div>
          <div class="campo">
            <label>Rol</label>
            <select id="campoRol" name="rol">
              <option value="paciente">Paciente</option>
              <option value="medico">Médico</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>

        <div id="msgModal" class="mensaje oculto" style="margin-top:12px"></div>

        <div class="modal-acciones">
          <button type="button" class="btn btn-secundario" onclick="cerrarModal()">Cancelar</button>
          <button type="submit" id="btnSubmitModal" class="btn btn-confirmar">Guardar</button>
        </div>
      </form>

    </div>
  </div>

  <!-- ID del usuario actual para que JS sepa quién es el admin logueado -->
  <script>const MI_ID = <?php echo $miId; ?>;</script>

</body>
</html>
