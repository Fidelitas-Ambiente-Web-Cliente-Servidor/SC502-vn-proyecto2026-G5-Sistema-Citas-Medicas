/**
 * usuarios.js - Validacion de Login y Registro 
 * Valida formularios en el navegador antes de enviarlos al servidor:
 * campos vacios, contrasenas que coincidan y longitud minima.
 */
document.addEventListener("DOMContentLoaded", () => {

  /* =========================
     LOGIN
  ========================= */
  const formLogin = document.getElementById("formLogin");
  const msgLogin = document.getElementById("msgLogin");

  if (formLogin) {
    formLogin.addEventListener("submit", (e) => {

      const correo = document.querySelector("input[name='correo']").value.trim();
      const password = document.querySelector("input[name='password']").value.trim();

      msgLogin.classList.add("oculto");

      if (!correo || !password) {
        e.preventDefault();

        msgLogin.innerText = "Todos los campos son obligatorios";
        msgLogin.classList.remove("oculto");
        msgLogin.classList.add("error");
      }
    });
  }

  /* =========================
     REGISTRO
  ========================= */
  const formRegistro = document.getElementById("formRegistro");
  const msgRegistro = document.getElementById("msgRegistro");

  if (formRegistro) {
    formRegistro.addEventListener("submit", (e) => {

      const nombre = document.querySelector("input[name='nombre']").value.trim();
      const apellidos = document.querySelector("input[name='apellidos']").value.trim();
      const correo = document.querySelector("input[name='correo']").value.trim();
      const password = document.querySelector("input[name='password']").value.trim();
      const confirmar = document.getElementById("confirmarClave").value;
      const msg = document.getElementById("msgRegistro");

if (password !== confirmar) {
  e.preventDefault();

  msg.innerText = "Las contraseñas no coinciden";
  msg.classList.remove("oculto");
  msg.classList.add("error");

  return;
}

      msgRegistro.classList.add("oculto");

      if (!nombre || !apellidos || !correo || !password) {
        e.preventDefault();

        msgRegistro.innerText = "Todos los campos son obligatorios";
        msgRegistro.classList.remove("oculto");
        msgRegistro.classList.add("error");
        return;
      }

      if (password.length < 6) {
        e.preventDefault();

        msgRegistro.innerText = "La contraseña debe tener mínimo 6 caracteres";
        msgRegistro.classList.remove("oculto");
        msgRegistro.classList.add("error");
      }
    });
  }

});