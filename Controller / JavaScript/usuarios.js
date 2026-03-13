document.addEventListener("DOMContentLoaded", () => {

  /* =========================================================
     USUARIO TEMPORAL
  ========================================================= */
  let usuarioGuardado = null;


  /* =========================================================
     VISTAS
  ========================================================= */
  const vistas = {
    rol: document.getElementById("vista-rol"),
    inicio: document.getElementById("vista-inicio"),
    login: document.getElementById("vista-login"),
    registro: document.getElementById("vista-registro"),
    perfil: document.getElementById("vista-perfil")
  };

  const subtitulo = document.getElementById("subtitulo");


  /* =========================================================
     FUNCIÓN MOSTRAR VISTA
  ========================================================= */
  const mostrarVista = (vista) => {

    Object.values(vistas).forEach(v => v.classList.add("oculto"));

    vista.classList.remove("oculto");

    if (vista === vistas.inicio) {
      subtitulo.classList.remove("oculto");
      subtitulo.innerText = "Iniciá sesión o registrate para continuar.";
    } else {
      subtitulo.classList.add("oculto");
    }

  };


  /* =========================================================
     NAVEGACIÓN ENTRE VISTAS
  ========================================================= */

  document.getElementById("btnPaciente")
    .addEventListener("click", () => mostrarVista(vistas.inicio));

  document.getElementById("btnIrLogin")
    .addEventListener("click", () => mostrarVista(vistas.login));

  document.getElementById("btnIrRegistro")
    .addEventListener("click", () => mostrarVista(vistas.registro));

  document.getElementById("btnVolverInicio1")
    .addEventListener("click", () => mostrarVista(vistas.inicio));

  document.getElementById("btnVolverInicio2")
    .addEventListener("click", () => mostrarVista(vistas.inicio));


  /* =========================================================
     CERRAR SESIÓN
  ========================================================= */
  document.getElementById("btnCerrarSesion")
    .addEventListener("click", () => {

      usuarioGuardado = null;

      mostrarVista(vistas.inicio);

    });


  /* =========================================================
     MENSAJES
  ========================================================= */

  const mostrarMensaje = (elemento, texto, tipo) => {

    elemento.innerText = texto;

    elemento.classList.remove("oculto", "error", "ok");

    elemento.classList.add(tipo);

  };

  const ocultarMensaje = (elemento) => {

    elemento.classList.add("oculto");

    elemento.classList.remove("error", "ok");

  };


  /* =========================================================
     VALIDAR CORREO
  ========================================================= */
  const correoValido = (correo) => {
    return correo.includes("@") && correo.includes(".");
  };


  /* =========================================================
     REGISTRO
  ========================================================= */

  const formRegistro = document.getElementById("formRegistro");
  const msgRegistro = document.getElementById("msgRegistro");

  formRegistro.addEventListener("submit", (event) => {

    event.preventDefault();

    ocultarMensaje(msgRegistro);

    const nombre = document.getElementById("nombre").value.trim();
    const apellidos = document.getElementById("apellidos").value.trim();
    const correo = document.getElementById("correo").value.trim();
    const clave = document.getElementById("clave").value.trim();
    const confirmarClave = document.getElementById("confirmarClave").value.trim();


    if (!nombre || !apellidos || !correo || !clave || !confirmarClave) {
      mostrarMensaje(msgRegistro, "Todos los campos son obligatorios.", "error");
      return;
    }

    if (!correoValido(correo)) {
      mostrarMensaje(msgRegistro, "El correo no es válido.", "error");
      return;
    }

    if (clave.length < 6) {
      mostrarMensaje(msgRegistro, "La contraseña debe tener mínimo 6 caracteres.", "error");
      return;
    }

    if (clave !== confirmarClave) {
      mostrarMensaje(msgRegistro, "Las contraseñas no coinciden.", "error");
      return;
    }


    usuarioGuardado = {
      nombre,
      apellidos,
      correo,
      clave
    };


    mostrarMensaje(msgRegistro, "Registro exitoso. Ahora puedes iniciar sesión.", "ok");

    formRegistro.reset();

  });


  /* =========================================================
     LOGIN
  ========================================================= */

  const formLogin = document.getElementById("formLogin");
  const msgLogin = document.getElementById("msgLogin");

  formLogin.addEventListener("submit", (event) => {

    event.preventDefault();

    ocultarMensaje(msgLogin);

    const correoLogin = document.getElementById("correoLogin").value.trim();
    const claveLogin = document.getElementById("claveLogin").value.trim();


    if (!correoLogin || !claveLogin) {
      mostrarMensaje(msgLogin, "Todos los campos son obligatorios.", "error");
      return;
    }

    if (!correoValido(correoLogin)) {
      mostrarMensaje(msgLogin, "Correo inválido.", "error");
      return;
    }

    if (!usuarioGuardado) {
      mostrarMensaje(msgLogin, "No hay ningún usuario registrado.", "error");
      return;
    }

    if (correoLogin !== usuarioGuardado.correo || claveLogin !== usuarioGuardado.clave) {
      mostrarMensaje(msgLogin, "Correo o contraseña incorrectos.", "error");
      return;
    }


    formLogin.reset();


    /* ===============================
       CARGAR PERFIL
    =============================== */

    document.getElementById("perfilNombre").innerText = usuarioGuardado.nombre;
    document.getElementById("perfilApellidos").innerText = usuarioGuardado.apellidos;
    document.getElementById("perfilCorreo").innerText = usuarioGuardado.correo;


    mostrarVista(vistas.perfil);

  });


  /* =========================================================
     VISTA INICIAL
  ========================================================= */

  mostrarVista(vistas.rol);

});