document.addEventListener("DOMContentLoaded", function () {

  /* =========================================================
    VARIABLE usuarioGuardado
    Guarda temporalmente la información del usuario registrado.
    Solo existe mientras la página está abierta.
 ========================================================= */
  let usuarioGuardado = null;

  /* =========================================================
      VISTAS DEL SISTEMA
     Cada vista corresponde a una sección del HTML.
     Se muestran u ocultan dinámicamente usando JavaScript.
  ========================================================= */
  let vistaRol = document.getElementById("vista-rol");
  let vistaInicio = document.getElementById("vista-inicio");
  let vistaLogin = document.getElementById("vista-login");
  let vistaRegistro = document.getElementById("vista-registro");
  let vistaPerfil = document.getElementById("vista-perfil");

  /* Subtítulo del encabezado que cambia según la vista */
  let subtitulo = document.getElementById("subtitulo");

  /* =========================================================
     FUNCIÓN mostrarVista()
     Esta función controla qué pantalla se muestra:
     Primero oculta todas las vistas y luego muestra
     solo la vista que se recibe como parámetro.
  ========================================================= */
  function mostrarVista(vista) {

    /* Oculta todas las vistas */
    vistaRol.classList.add("oculto");
    vistaInicio.classList.add("oculto");
    vistaLogin.classList.add("oculto");
    vistaRegistro.classList.add("oculto");
    vistaPerfil.classList.add("oculto");

    /* Muestra la vista solicitada */
    vista.classList.remove("oculto");

    // Mostrar subtítulo solo en la vista de inicio (paciente)
    if (vista === vistaInicio) {
      subtitulo.classList.remove("oculto");
      subtitulo.innerText = "Iniciá sesión o registrate para continuar.";
    } else {
      subtitulo.classList.add("oculto");
    }
  }

  /* =========================================================
       BOTONES DE NAVEGACIÓN
       Cada botón cambia entre las diferentes vistas
       del sistema utilizando la función mostrarVista().
    ========================================================= */
  document.getElementById("btnPaciente").addEventListener("click", function () {
    mostrarVista(vistaInicio);
  });
  document.getElementById("btnIrLogin").addEventListener("click", function () {
    mostrarVista(vistaLogin);
  });

  document.getElementById("btnIrRegistro").addEventListener("click", function () {
    mostrarVista(vistaRegistro);
  });

  document.getElementById("btnVolverInicio1").addEventListener("click", function () {
    mostrarVista(vistaInicio);
  });

  document.getElementById("btnVolverInicio2").addEventListener("click", function () {
    mostrarVista(vistaInicio);
  });

  /* =========================================================
     BOTÓN CERRAR SESIÓN
     Elimina los datos del usuario guardado y regresa
     a la pantalla de inicio.
  ========================================================= */
  document.getElementById("btnCerrarSesion").addEventListener("click", function () {

    /* Se borra el usuario guardado */
    usuarioGuardado = null;

    /* Se devuelve a la pantalla inicial */
    mostrarVista(vistaInicio);
  });

  /* =========================================================
     FUNCIONES PARA MOSTRAR Y OCULTAR MENSAJES
     Se utilizan para mostrar errores o confirmaciones
     en los formularios de login y registro.
  ========================================================= */

  /* Mostrar mensaje */
  function mostrarMensaje(elemento, texto, tipo) {
    elemento.innerText = texto;
    elemento.classList.remove("oculto");
    elemento.classList.remove("error");
    elemento.classList.remove("ok");
    elemento.classList.add(tipo);
  }

  /* Ocultar mensaje */
  function ocultarMensaje(elemento) {
    elemento.classList.add("oculto");
    elemento.classList.remove("error");
    elemento.classList.remove("ok");
  }

  /* =========================================================
    FUNCIÓN correoValido()
 ========================================================= */
  function correoValido(correo) {
    return correo.includes("@") && correo.includes(".");
  }

  /* =========================================================
     SECCIÓN REGISTRO DE USUARIO
  ========================================================= */
  let formRegistro = document.getElementById("formRegistro");
  let msgRegistro = document.getElementById("msgRegistro");

  /* Evento que se ejecuta cuando se envía el formulario */
  formRegistro.addEventListener("submit", function (event) {
    /* Evita que el formulario recargue la página */
    event.preventDefault();
    /* Oculta cualquier mensaje previo */
    ocultarMensaje(msgRegistro);

    /* Obtiene los valores ingresados por el usuario */
    let nombre = document.getElementById("nombre").value.trim();
    let apellidos = document.getElementById("apellidos").value.trim();
    let correo = document.getElementById("correo").value.trim();
    let clave = document.getElementById("clave").value.trim();
    let confirmarClave = document.getElementById("confirmarClave").value.trim();

    /* Validación de campos vacíos */
    if (nombre === "" || apellidos === "" || correo === "" || clave === "" || confirmarClave === "") {
      mostrarMensaje(msgRegistro, "Todos los campos son obligatorios.", "error");
      return;
    }

    /* Validación del formato del correo */
    if (!correoValido(correo)) {
      mostrarMensaje(msgRegistro, "El correo no es válido. Debe incluir @ y .", "error");
      return;
    }

    /* Validación de la contraseña */
    if (clave.length < 6) {
      mostrarMensaje(msgRegistro, "La contraseña debe tener mínimo 6 caracteres.", "error");
      return;
    }

    /* Validación de la confirmación de contraseña */
    if (clave !== confirmarClave) {
      mostrarMensaje(msgRegistro, "Las contraseñas no coinciden.", "error");
      return;
    }

    /* =========================================================
      GUARDAR USUARIO
      Se crea un objeto con los datos del usuario registrado.
   ========================================================= */
    usuarioGuardado = { nombre: nombre, apellidos: apellidos, correo: correo, clave: clave };

    mostrarMensaje(msgRegistro, "Registro exitoso. Ahora puedes iniciar sesión.", "ok");
    /* Limpia los campos del formulario */
    formRegistro.reset();
  });

  /* =========================================================
     SECCIÓN LOGIN
     Es el inicio de sesión del usuario.
  ========================================================= */
  let formLogin = document.getElementById("formLogin");
  let msgLogin = document.getElementById("msgLogin");

  formLogin.addEventListener("submit", function (event) {
    /* Evita recargar la página */
    event.preventDefault();
    /* Oculta cualquier mensaje previo */
    ocultarMensaje(msgLogin);

    /* Obtiene los valores ingresados por el usuario */
    let correoLogin = document.getElementById("correoLogin").value.trim();
    let claveLogin = document.getElementById("claveLogin").value.trim();

    if (correoLogin === "" || claveLogin === "") {
      mostrarMensaje(msgLogin, "Todos los campos son obligatorios.", "error");
      return;
    }

    if (!correoValido(correoLogin)) {
      mostrarMensaje(msgLogin, "Correo inválido. Debe incluir @ y .", "error");
      return;
    }

    if (usuarioGuardado === null) {
      mostrarMensaje(msgLogin, "No hay ningún usuario registrado.", "error");
      return;
    }

    if (correoLogin !== usuarioGuardado.correo || claveLogin !== usuarioGuardado.clave) {
      mostrarMensaje(msgLogin, "Correo o contraseña incorrectos.", "error");
      return;
    }

    /* Limpia los campos del formulario */
    formLogin.reset();

    /* =========================================================
      CARGAR DATOS EN EL PERFIL
      Inserta los datos del usuario en la vista perfil.
   ========================================================= */
    document.getElementById("perfilNombre").innerText = usuarioGuardado.nombre;
    document.getElementById("perfilApellidos").innerText = usuarioGuardado.apellidos;
    document.getElementById("perfilCorreo").innerText = usuarioGuardado.correo;

    mostrarVista(vistaPerfil);
  });

  /* =========================================================
      VISTA INICIAL
      Cuando la página carga por primera vez se muestra
      la pantalla de inicio.
   ========================================================= */
  mostrarVista(vistaRol);
});