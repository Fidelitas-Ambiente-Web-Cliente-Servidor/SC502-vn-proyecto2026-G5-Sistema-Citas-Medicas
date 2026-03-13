document.addEventListener("DOMContentLoaded", () => {

    let historial = JSON.parse(localStorage.getItem("historial")) || [];

    const form = document.getElementById("formDiagnostico");
    const historialDiv = document.getElementById("historialMedico");
    const mensaje = document.getElementById("mensajeDiag");
    const buscador = document.getElementById("buscadorHistorial");
    const panelNotificaciones = document.getElementById("panelNotificaciones");



    /* =========================
       GUARDAR DIAGNOSTICO
    ========================= */

    form.addEventListener("submit", (e) => {

        e.preventDefault();

        const sintomas = document.getElementById("sintomas").value.trim();
        const diagnostico = document.getElementById("diagnostico").value.trim();
        const tratamiento = document.getElementById("tratamiento").value.trim();
        const notas = document.getElementById("notas").value.trim();

        if (!sintomas || !diagnostico || !tratamiento) {

            mostrarMensaje("Todos los campos principales son obligatorios", "error");

            return;

        }

        const nuevo = {

            fecha: new Date().toLocaleDateString(),
            sintomas,
            diagnostico,
            tratamiento,
            notas

        };

        historial.push(nuevo);

        localStorage.setItem("historial", JSON.stringify(historial));

        form.reset();

        mostrarMensaje("Diagnóstico registrado correctamente", "ok");

        crearNotificacion("Diagnóstico registrado");

        mostrarHistorial();

    });



    /* =========================
       MOSTRAR HISTORIAL
    ========================= */

    function mostrarHistorial(lista = historial) {

        historialDiv.innerHTML = "";

        if (lista.length === 0) {

            historialDiv.innerHTML = "<p>No hay registros médicos.</p>";
            return;

        }

        lista.forEach(item => {

            const card = document.createElement("div");

            card.className = "historial-card";

            card.innerHTML = `
<p class="historial-fecha">${item.fecha}</p>

<p><strong>Síntomas:</strong> ${item.sintomas}</p>

<p><strong>Diagnóstico:</strong> ${item.diagnostico}</p>

<p><strong>Tratamiento:</strong> ${item.tratamiento}</p>

<p><strong>Notas:</strong> ${item.notas}</p>
`;

            historialDiv.appendChild(card);

        });

    }



    /* =========================
       BUSCADOR HISTORIAL
    ========================= */

    buscador.addEventListener("input", () => {

        const texto = buscador.value.toLowerCase();

        const filtrado = historial.filter(item =>

            item.diagnostico.toLowerCase().includes(texto) ||
            item.fecha.includes(texto)

        );

        mostrarHistorial(filtrado);

    });



    /* =========================
       NOTIFICACIONES
    ========================= */

    function crearNotificacion(texto) {

        const notif = document.createElement("div");

        notif.className = "notificacion";

        notif.innerText = texto;

        panelNotificaciones.prepend(notif);

    }



    /* =========================
       MENSAJES
    ========================= */

    function mostrarMensaje(texto, tipo) {

        mensaje.innerText = texto;

        mensaje.classList.remove("oculto", "error", "ok");

        mensaje.classList.add(tipo);

    }



    mostrarHistorial();



    /* NOTIFICACIONES DE EJEMPLO */

    crearNotificacion("Nueva cita creada");
    crearNotificacion("Cita reprogramada");

}); 