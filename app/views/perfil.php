<?php
// Variables pasadas desde UserController::perfil() a través de $userData
$nombre    = htmlspecialchars($userData['nombre']    ?? '');
$apellidos = htmlspecialchars($userData['apellidos'] ?? '');
$correo    = htmlspecialchars($userData['correo']    ?? '');
$inicial   = strtoupper(mb_substr($nombre, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Perfil - Sistema de Citas Médicas</title>
  <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<header class="encabezado">
  <div class="titulo-header">
    <h1>Mi Perfil</h1>
    <p>Gestioná tu información personal</p>
  </div>
  <div class="header-usuario">
    <button id="btnDarkMode" class="btn-darkmode" title="Cambiar tema">🌙</button>
    <a href="/index.php?vista=main" class="btn btn-secundario btn-sm">Panel principal</a>
    <a href="/index.php?accion=logout" class="btn btn-cancelar btn-sm">Cerrar sesión</a>
  </div>
</header>

<main class="contenedor">

  <section class="vista">
    <div class="perfil-avatar"><?php echo $inicial ?: '👤'; ?></div>
    <h2><?php echo $nombre . ' ' . $apellidos; ?></h2>
    <p class="texto-ayuda"><?php echo $correo; ?></p>

    <div class="perfil-grid">

      <!-- Datos personales -->
      <div class="tarjeta" style="margin:0;">
        <h3 style="margin-top:0;">Datos personales</h3>
        <span id="msgPerfil" class="mensaje oculto"></span>
        <form id="formPerfil" class="form-perfil">
          <label for="pNombre">Nombre</label>
          <input type="text" id="pNombre" name="nombre" value="<?php echo $nombre; ?>" required>
          <label for="pApellidos">Apellidos</label>
          <input type="text" id="pApellidos" name="apellidos" value="<?php echo $apellidos; ?>" required>
          <label for="pCorreo">Correo electrónico</label>
          <input type="email" id="pCorreo" name="correo" value="<?php echo $correo; ?>" required>
          <div class="acciones" style="justify-content:flex-start; margin-top:18px;">
            <button type="submit" class="btn btn-principal">Guardar cambios</button>
          </div>
        </form>
      </div>

      <!-- Cambiar contraseña -->
      <div class="tarjeta" style="margin:0;">
        <h3 style="margin-top:0;">Cambiar contraseña</h3>
        <span id="msgPassword" class="mensaje oculto"></span>
        <form id="formPassword" class="form-password">
          <label for="pActual">Contraseña actual</label>
          <input type="password" id="pActual" name="password_actual" required placeholder="••••••••">
          <label for="pNueva">Nueva contraseña</label>
          <input type="password" id="pNueva" name="password_nueva" required placeholder="••••••••">
          <label for="pConfirmar">Confirmar nueva</label>
          <input type="password" id="pConfirmar" name="password_confirm" required placeholder="••••••••">
          <div class="acciones" style="justify-content:flex-start; margin-top:18px;">
            <button type="submit" class="btn btn-principal">Actualizar contraseña</button>
          </div>
        </form>
      </div>

    </div>
  </section>

</main>
<footer class="pie">
  <small>Sistema Médico Digital &bull; Proyecto académico</small>
</footer>
<script>
  /* ── Dark mode ────────────────────────────────────── */
  (function () {
    const on = localStorage.getItem('darkMode') === '1';
    document.body.classList.toggle('dark-mode', on);
    const b = document.getElementById('btnDarkMode');
    if (b) b.textContent = on ? '☀️' : '🌙';
    b && b.addEventListener('click', () => {
      const n = !document.body.classList.contains('dark-mode');
      document.body.classList.toggle('dark-mode', n);
      localStorage.setItem('darkMode', n ? '1' : '0');
      b.textContent = n ? '☀️' : '🌙';
    });
  })();

  /* ── Helper fetch form ────────────────────────────── */
  async function postForm(url, formData) {
    const r = await fetch(url, { method: 'POST', body: new URLSearchParams(formData) });
    return r.json();
  }

  function mostrarMsg(el, texto, tipo) {
    el.textContent = texto;
    el.className = 'mensaje ' + tipo;
    setTimeout(() => { el.textContent = ''; el.className = 'mensaje oculto'; }, 4000);
  }

  /* ── Formulario datos personales ─────────────────── */
  document.getElementById('formPerfil').addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = document.getElementById('msgPerfil');
    try {
      const res = await postForm('/index.php?accion=actualizar_perfil', new FormData(e.target));
      mostrarMsg(msg, res.success ? 'Datos actualizados correctamente.' : (res.error || 'Error al guardar.'), res.success ? 'ok' : 'error');
    } catch {
      mostrarMsg(msg, 'Error de red. Intentá de nuevo.', 'error');
    }
  });

  /* ── Formulario cambiar contraseña ───────────────── */
  document.getElementById('formPassword').addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = document.getElementById('msgPassword');
    const nueva    = document.getElementById('pNueva').value;
    const confirmar = document.getElementById('pConfirmar').value;
    if (nueva !== confirmar) {
      mostrarMsg(msg, 'Las contraseñas nuevas no coinciden.', 'error');
      return;
    }
    try {
      const res = await postForm('/index.php?accion=cambiar_password', new FormData(e.target));
      if (res.success) {
        mostrarMsg(msg, 'Contraseña actualizada correctamente.', 'ok');
        e.target.reset();
      } else {
        mostrarMsg(msg, res.error || 'Error al actualizar la contraseña.', 'error');
      }
    } catch {
      mostrarMsg(msg, 'Error de red. Intentá de nuevo.', 'error');
    }
  });
</script>
</body>
</html>
