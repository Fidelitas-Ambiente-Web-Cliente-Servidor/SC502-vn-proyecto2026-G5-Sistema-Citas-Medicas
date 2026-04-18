<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro - Sistema de Citas Médicas</title>
  <link rel="stylesheet" href="/public/css/style.css">
  <script src="/public/js/usuarios.js" defer></script>
</head>

<body>

  <header class="encabezado">
    <div class="titulo-header">
      <h1>Sistema de Citas Médicas</h1>
      <p>Creá tu cuenta para continuar</p>
    </div>
  </header>

  <main class="contenedor">

    <section class="vista">

      <form id="formRegistro" method="POST" action="/index.php?vista=registro" class="tarjeta">

        <h2>Registro de usuario</h2>
        <p id="msgRegistro" class="mensaje oculto"></p>

        <p class="texto-ayuda">Completá los campos para crear tu cuenta.</p>

        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan">

        <label for="apellidos">Apellidos</label>
        <input type="text" id="apellidos" name="apellidos" required placeholder="Ej: Pérez Mora">

        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" required placeholder="usuario@correo.com">

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">

        <label for="confirmarClave">Confirmar contraseña</label>
        <input type="password" id="confirmarClave" name="confirmarClave" required placeholder="••••••••">

        <input type="hidden" name="accion" value="registro">

        <div class="acciones">
          <button type="submit" class="btn btn-principal">Crear cuenta</button>
          <a href="/index.php?vista=login" class="btn btn-secundario">Volver al login</a>
        </div>

      </form>

    </section>

  </main>

  <footer class="pie">
    <small>Atención médica digital segura y confiable</small>
  </footer>

</body>
</html>
