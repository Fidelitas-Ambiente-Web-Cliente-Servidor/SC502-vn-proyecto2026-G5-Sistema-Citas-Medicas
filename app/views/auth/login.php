<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Sistema de Citas Médicas</title>
  <link rel="stylesheet" href="public/css/style.css">
  <script src="public/js/usuarios.js" defer></script>
</head>

<body>

  <header class="encabezado">
    <div class="titulo-header">
      <h1>Sistema de Citas Médicas</h1>
      <p>Iniciá sesión o registrate para continuar</p>
    </div>
  </header>

  <main class="contenedor">

    <section class="vista">

      <form id="formLogin" method="POST" action="/index.php?vista=login" class="tarjeta">

        <h2>Inicio de sesión</h2>

        <p>Ingresá tu correo electrónico y contraseña para acceder al sistema.</p>

        <?php if (isset($_GET['error'])): ?>
          <p id="msgLogin" class="mensaje error">Correo o contraseña incorrectos.</p>
        <?php else: ?>
          <p id="msgLogin" class="mensaje oculto"></p>
        <?php endif; ?>

        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" required placeholder="usuario@correo.com">

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">

        <input type="hidden" name="accion" value="login">

        <div class="acciones">
          <button type="submit" class="btn btn-principal">Entrar</button>
          <a href="/index.php?vista=registro" class="btn btn-secundario">Crear cuenta</a>
        </div>

      </form>

    </section>

  </main>

  <footer class="pie">
    <small>Atención médica digital segura y confiable</small>
  </footer>

</body>

</html>
