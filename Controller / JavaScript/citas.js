document.addEventListener("DOMContentLoaded", () => {

    /* ================================
       VARIABLES PRINCIPALES
    =================================*/

    const citas = [];

    const lista = document.getElementById("listaCitas");
    const btnNueva = document.getElementById("btnNuevaCita");
    const formularioVista = document.getElementById("formularioCita");
    const form = document.getElementById("formCita");
    const mensaje = document.getElementById("mensajeCita");
    const btnCancelar = document.getElementById("btnCancelar");



    /* ================================
       MOSTRAR / OCULTAR FORMULARIO
    =================================*/

    btnNueva.addEventListener("click", () => {
        formularioVista.classList.remove("oculto");
    });

    btnCancelar.addEventListener("click", () => {
        formularioVista.classList.add("oculto");
        limpiarMensaje();
    });



    /* ================================
       ENVÍO DEL FORMULARIO
    =================================*/

    form.addEventListener("submit", (e) => {

        e.preventDefault();

        const especialidad = document.getElementById("especialidad").value.trim();
        const motivo = document.getElementById("motivo").value.trim();
        const fecha = document.getElementById("fecha").value;
        const hora = document.getElementById("hora").value;
        const prioridad = document.getElementById("prioridad").value;


        /* ================================
           VALIDACIÓN
        =================================*/

        if (!especialidad || !motivo || !fecha || !hora) {
            mostrarError("Todos los campos son obligatorios");
            return;
        }

        const hoy = new Date().toISOString().split("T")[0];

        if (fecha < hoy) {
            mostrarError("La fecha no puede ser pasada");
            return;
        }


        /* ================================
           CREAR CITA
        =================================*/

        const cita = {

            especialidad,
            motivo,
            fecha,
            hora,
            prioridad,
            estado: "Activa"

        };

        citas.push(cita);


        /* ================================
           LIMPIAR Y ACTUALIZAR
        =================================*/

        form.reset();
        formularioVista.classList.add("oculto");
        limpiarMensaje();

        mostrarCitas();

    });



    /* ================================
       MOSTRAR CITAS
    =================================*/

    function mostrarCitas() {

        lista.innerHTML = "";

        citas.forEach((cita, index) => {

            const card = document.createElement("div");

            card.className = "cita-card";

            card.innerHTML = `
                <p><strong>Especialidad:</strong> ${cita.especialidad}</p>
                <p><strong>Motivo:</strong> ${cita.motivo}</p>
                <p><strong>Fecha:</strong> ${cita.fecha}</p>
                <p><strong>Hora:</strong> ${cita.hora}</p>
                <p><strong>Prioridad:</strong> ${cita.prioridad}</p>
                <p><strong>Estado:</strong> ${cita.estado}</p>

                <div class="cita-acciones">
                    <button class="btn btn-cancelar" data-index="${index}">
                        Cancelar
                    </button>
                </div>
            `;

            lista.appendChild(card);

        });

    }



    /* ================================
       CANCELAR CITA
    =================================*/

    lista.addEventListener("click", (e) => {

        if (e.target.matches(".btn-cancelar")) {

            const index = e.target.dataset.index;

            citas[index].estado = "Cancelada";

            mostrarCitas();

        }

    });



    /* ================================
       MENSAJES
    =================================*/

    function mostrarError(texto) {

        mensaje.innerText = texto;

        mensaje.classList.remove("oculto");

        mensaje.classList.add("error");

    }

    function limpiarMensaje() {

        mensaje.innerText = "";

        mensaje.classList.add("oculto");

        mensaje.classList.remove("error");

    }

});