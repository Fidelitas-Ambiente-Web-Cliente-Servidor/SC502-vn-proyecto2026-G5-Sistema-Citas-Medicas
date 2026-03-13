document.addEventListener("DOMContentLoaded", () => {
    // Datos de ejemplo iniciales
    let agenda = [
        { id: 1, paciente: "Juan Pérez", especialidad: "Medicina General", fecha: "2026-03-13", estado: "Pendiente", horario: null },
        { id: 2, paciente: "María López", especialidad: "Odontología", fecha: "2026-03-13", estado: "Confirmada", horario: "08:30 AM" }
    ];

    const listaAgenda = document.getElementById("listaAgenda");
    const filtroEstado = document.getElementById("filtroEstado");
    const filtroFecha = document.getElementById("filtroFecha");
    
    // Establecer fecha de hoy en el filtro por defecto
    const hoy = new Date().toISOString().split('T')[0];
    filtroFecha.value = hoy;

    function renderizarAgenda() {
        const estadoBusqueda = filtroEstado.value;
        const fechaBusqueda = filtroFecha.value;

        const citasFiltradas = agenda.filter(cita => {
            const coincideEstado = estadoBusqueda === "todos" || cita.estado === estadoBusqueda;
            const coincideFecha = !fechaBusqueda || cita.fecha === fechaBusqueda;
            return coincideEstado && coincideFecha;
        });

        listaAgenda.innerHTML = "";

        if (citasFiltradas.length === 0) {
            listaAgenda.innerHTML = `<p class="texto-ayuda">No hay citas que coincidan con los filtros.</p>`;
            return;
        }

        citasFiltradas.forEach(cita => {
            const card = document.createElement("div");
            card.className = `tarjeta cita-medica estado-${cita.estado.toLowerCase()}`;
            
            card.innerHTML = `
                <span class="badge badge-${cita.estado.toLowerCase()}">${cita.estado}</span>
                <h3>${cita.paciente}</h3>
                <p><strong>Especialidad:</strong> ${cita.especialidad}</p>
                <p><strong>Fecha:</strong> ${cita.fecha}</p>
                <p><strong>Horario:</strong> ${cita.horario || "Sin asignar"}</p>
                
                ${cita.estado === 'Pendiente' ? `
                    <div class="asignar-horario">
                        <input type="time" id="time-${cita.id}">
                        <button class="btn btn-principal btn-sm" onclick="confirmarCita(${cita.id})">Confirmar</button>
                    </div>
                ` : cita.estado === 'Confirmada' ? `
                    <button class="btn btn-secundario btn-sm" onclick="finalizarCita(${cita.id})">Marcar Atendida</button>
                ` : ''}
            `;
            listaAgenda.appendChild(card);
        });
    }

    // Funciones globales para los botones
    window.confirmarCita = (id) => {
        const timeInput = document.getElementById(`time-${id}`);
        const hora = timeInput.value;
        if (!hora) return alert("Por favor selecciona una hora");

        const cita = agenda.find(c => c.id === id);
        cita.estado = "Confirmada";
        cita.horario = hora;
        renderizarAgenda();
    };

    window.finalizarCita = (id) => {
        const cita = agenda.find(c => c.id === id);
        cita.estado = "Atendida";
        renderizarAgenda();
    };

    // Simulador
    document.getElementById("btnSimular").addEventListener("click", () => {
        const nombre = document.getElementById("simNombre").value;
        const esp = document.getElementById("simEspecialidad").value;
        const fecha = document.getElementById("simFecha").value;

        if (!nombre || !fecha) return alert("Completa los datos del simulador");

        const nuevaCita = {
            id: Date.now(),
            paciente: nombre,
            especialidad: esp,
            fecha: fecha,
            estado: "Pendiente",
            horario: null
        };

        agenda.push(nuevaCita);
        renderizarAgenda();
        alert("Nueva solicitud recibida");
    });

    filtroEstado.addEventListener("change", renderizarAgenda);
    filtroFecha.addEventListener("change", renderizarAgenda);

    renderizarAgenda();
});