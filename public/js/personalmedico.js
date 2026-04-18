/**
 * personalmedico.js - Modulo de Agenda Medica
 * Carga todas las citas del sistema con filtros por fecha, estado
 * y prioridad. Permite confirmar hora y marcar citas como atendidas.
 * Incluye simulador para generar citas de prueba.
 */
document.addEventListener('DOMContentLoaded', () => {

  /* =========================================================
     HELPERS DE FETCH
  ========================================================= */

  async function postForm(url, formData) {
    const r = await fetch(url, { method: 'POST', body: new URLSearchParams(formData) });
    return r.json();
  }

  /* =========================================================
     ELEMENTOS DEL DOM
  ========================================================= */
  const listaAgenda     = document.getElementById('listaAgenda');
  const filtroEstado    = document.getElementById('filtroEstado');
  const filtroFecha     = document.getElementById('filtroFecha');
  const filtroPrioridad = document.getElementById('filtroPrioridad');

  /* Estado local */
  let agendaData = [];

  /* =========================================================
     SPINNER
  ========================================================= */
  function mostrarSpinner(contenedor) {
    contenedor.innerHTML = '<div class="spinner-wrap"><div class="spinner"></div></div>';
  }

  /* =========================================================
     CARGAR AGENDA DESDE EL SERVIDOR
  ========================================================= */
  async function cargarAgenda() {
    mostrarSpinner(listaAgenda);
    try {
      const res  = await fetch('/index.php?api=agenda');
      const json = await res.json();
      if (json.success) {
        agendaData = json.data || [];
      } else {
        agendaData = [];
        listaAgenda.innerHTML = `<p class="mensaje error">${json.mensaje || 'Error al cargar agenda.'}</p>`;
        return;
      }
    } catch (err) {
      agendaData = [];
      listaAgenda.innerHTML = '<p class="mensaje error">Error de red al cargar la agenda. Intentá de nuevo.</p>';
      return;
    }
    renderizarAgenda();
  }

  /* =========================================================
     RENDERIZAR AGENDA (filtros en memoria)
  ========================================================= */
  function renderizarAgenda() {
    const estadoBusqueda = filtroEstado    ? filtroEstado.value    : 'todos';
    const fechaBusqueda  = filtroFecha     ? filtroFecha.value     : '';
    const prioBusqueda   = filtroPrioridad ? filtroPrioridad.value : 'todas';

    const citasFiltradas = agendaData.filter(cita => {
      const coincideEstado    = estadoBusqueda === 'todos'  || cita.estado    === estadoBusqueda;
      const coincideFecha     = !fechaBusqueda             || cita.fecha     === fechaBusqueda;
      const coincidePrioridad = prioBusqueda    === 'todas' || cita.prioridad === prioBusqueda;
      return coincideEstado && coincideFecha && coincidePrioridad;
    });

    listaAgenda.innerHTML = '';

    if (citasFiltradas.length === 0) {
      listaAgenda.innerHTML = '<p class="texto-ayuda">No hay citas que coincidan con los filtros.</p>';
      return;
    }

    citasFiltradas.forEach(cita => {
      const card = document.createElement('div');
      card.className = `tarjeta cita-medica estado-${(cita.estado || '').toLowerCase()}`;

      card.innerHTML = `
        <div class="cita-header">
          <span class="badge badge-estado badge-estado-${(cita.estado || '').toLowerCase()}">${cita.estado || ''}</span>
          ${cita.prioridad ? `<span class="badge badge-prioridad badge-${cita.prioridad.toLowerCase()}">${cita.prioridad}</span>` : ''}
        </div>
        <h3>${cita.paciente || ''}</h3>
        <p><strong>Especialidad:</strong> ${cita.especialidad || ''}</p>
        <p><strong>Fecha:</strong> ${cita.fecha || ''}</p>
        <p><strong>Horario:</strong> ${cita.horario || 'Sin asignar'}</p>
        ${cita.estado === 'Activa' ? `
          <div class="asignar-horario" style="display:flex;gap:10px;align-items:center;margin-top:12px;">
            <input type="time" id="time-${cita.id}" style="width:auto;flex:1;">
            <button class="btn btn-principal btn-sm btn-confirmar-cita" data-id="${cita.id}">Confirmar</button>
          </div>
        ` : cita.estado === 'Confirmada' ? `
          <div style="margin-top:12px;">
            <button class="btn btn-secundario btn-sm btn-atender-cita" data-id="${cita.id}">Marcar Atendida</button>
          </div>
        ` : ''}
      `;

      listaAgenda.appendChild(card);
    });
  }

  /* =========================================================
     CONFIRMAR CITA CON HORA (delegación)
  ========================================================= */
  if (listaAgenda) {
    listaAgenda.addEventListener('click', async (e) => {

      /* CONFIRMAR CITA */
      if (e.target.matches('.btn-confirmar-cita')) {
        const id        = e.target.dataset.id;
        const timeInput = document.getElementById(`time-${id}`);
        const hora      = timeInput ? timeInput.value : '';
        if (!hora) {
          mostrarNotificacion('Por favor seleccioná una hora antes de confirmar.');
          return;
        }
        try {
          const res = await postForm('/index.php?accion=confirmar_cita_hora', { id, hora });
          if (res.success) {
            mostrarNotificacion(`Cita ${id} confirmada para las ${hora}`);
            await cargarAgenda();
          } else {
            mostrarNotificacion(res.mensaje || 'Error al confirmar la cita.');
          }
        } catch (err) {
          mostrarNotificacion('Error de red al confirmar la cita.');
        }
      }

      /* ATENDER CITA */
      if (e.target.matches('.btn-atender-cita')) {
        const id = e.target.dataset.id;
        mostrarModal({
          titulo: 'Marcar como atendida',
          mensaje: '¿Confirmar que esta cita fue atendida?',
          textoConfirmar: 'Sí, atendida',
          textoCancel: 'Cancelar',
          onConfirmar: async () => {
            try {
              const res = await postForm('/index.php?accion=atender_cita', { id });
              if (res.success) {
                mostrarNotificacion('Cita marcada como atendida');
                await cargarAgenda();
              } else {
                mostrarNotificacion(res.mensaje || 'Error al marcar la cita.');
              }
            } catch (err) {
              mostrarNotificacion('Error de red al marcar la cita.');
            }
          }
        });
      }
    });
  }

  /* =========================================================
     SIMULADOR DE SOLICITUD
  ========================================================= */
  const btnSimular = document.getElementById('btnSimular');
  if (btnSimular) {
    btnSimular.addEventListener('click', async () => {
      const nombre = document.getElementById('simNombre')?.value.trim() || '';
      const esp    = document.getElementById('simEspecialidad')?.value || '';
      const fecha  = document.getElementById('simFecha')?.value || '';
      const prio   = document.getElementById('simPrioridad')?.value || 'Media';

      if (!nombre || !fecha) {
        mostrarNotificacion('Completá el nombre y la fecha del simulador.');
        return;
      }

      const fd = new FormData();
      fd.append('nombre',       nombre);
      fd.append('especialidad', esp);
      fd.append('fecha',        fecha);
      fd.append('prioridad',    prio);

      try {
        const res = await postForm('/index.php?accion=simular_solicitud', fd);
        if (res.success) {
          mostrarNotificacion(`Nueva solicitud de ${nombre} (${prio})`);
          const simNombreEl = document.getElementById('simNombre');
          if (simNombreEl) simNombreEl.value = '';
          await cargarAgenda();
        } else {
          mostrarNotificacion(res.mensaje || 'Error en la simulación.');
        }
      } catch (err) {
        mostrarNotificacion('Error de red en la simulación.');
      }
    });
  }

  /* =========================================================
     FILTROS
  ========================================================= */
  if (filtroEstado)    filtroEstado.addEventListener('change',    renderizarAgenda);
  if (filtroFecha)     filtroFecha.addEventListener('change',     renderizarAgenda);
  if (filtroPrioridad) filtroPrioridad.addEventListener('change', renderizarAgenda);

  /* =========================================================
     NOTIFICACIONES DEL PANEL
  ========================================================= */
  function mostrarNotificacion(texto) {
    const panel = document.getElementById('panelNotificaciones');
    if (!panel) return;
    const sinN = panel.querySelector('.sin-notificaciones');
    if (sinN) sinN.remove();

    const notif = document.createElement('div');
    notif.className = 'notificacion';
    notif.innerHTML = `
      <span class="notif-ico">🔔</span>
      <span class="notif-texto">${texto}</span>
      <span class="notif-hora">${new Date().toLocaleTimeString('es-CR')}</span>
    `;
    panel.prepend(notif);

    // Persistir en localStorage también
    const notifs = JSON.parse(localStorage.getItem('notificaciones')) || [];
    notifs.unshift({ texto, tipo: 'info', fecha: new Date().toLocaleString('es-CR') });
    if (notifs.length > 20) notifs.pop();
    localStorage.setItem('notificaciones', JSON.stringify(notifs));
  }

  // Cargar notificaciones del localStorage (compartido con citas.js y diagnosticos.js)
  function cargarNotificacionesExternas() {
    const notifs = JSON.parse(localStorage.getItem('notificaciones')) || [];
    notifs.slice(0, 5).forEach(n => mostrarNotificacion(`${n.texto} — ${n.fecha}`));
  }

  /* =========================================================
     INIT
  ========================================================= */
  // Establecer fecha de hoy en el filtro por defecto
  if (filtroFecha) {
    filtroFecha.value = new Date().toISOString().split('T')[0];
  }

  cargarNotificacionesExternas();
  cargarAgenda();

});
