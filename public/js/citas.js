/**
 * citas.js - Modulo de Gestion de Citas
 * Carga las citas del usuario via API, las renderiza con filtros
 * de prioridad y estado. Acciones: agendar, cancelar, reprogramar.
 * Calcula la prioridad sugerida en tiempo real segun el motivo.
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
     PRIORIDAD EN TIEMPO REAL
  ========================================================= */
  function calcularPrioridadJS(motivo) {
    const t = (motivo || '').toLowerCase();
    const alta  = ['pecho','respirar','dificultad','emergencia','urgente','sangrado','accidente','pérdida','desmayo','fractura','infarto','apendicitis','convulsión','parálisis','inconsciente'];
    const media = ['fiebre','dolor','náuseas','vómito','mareo','infección','inflamación','herida','alergia'];
    if (alta.some(k  => t.includes(k))) return 'Alta';
    if (media.some(k => t.includes(k))) return 'Media';
    return 'Baja';
  }

  /* =========================================================
     ELEMENTOS DEL DOM
  ========================================================= */
  const lista           = document.getElementById('listaCitas');
  const btnNueva        = document.getElementById('btnNuevaCita');
  const btnVerTodas     = document.getElementById('btnVerTodas');
  const formularioVista = document.getElementById('formularioCita');
  const form            = document.getElementById('formCita');
  const tituloForm      = document.getElementById('tituloFormCita');
  const mensajeEl       = document.getElementById('mensajeCita');
  const btnCancelarForm = document.getElementById('btnCancelarForm');
  const filtroPrioridad = document.getElementById('filtroPrioridad');
  const filtroEstado    = document.getElementById('filtroEstado');
  const indexEdicion    = document.getElementById('indexEdicion');
  const motivoInput     = document.getElementById('motivo');
  const hintPrioridad   = document.getElementById('hintPrioridad');

  /* Estado local: array de citas cargadas desde el servidor */
  let citasData = [];

  /* =========================================================
     SPINNER HELPERS
  ========================================================= */
  function mostrarSpinner(contenedor) {
    contenedor.innerHTML = '<div class="spinner-wrap"><div class="spinner"></div></div>';
  }

  /* =========================================================
     MENSAJES
  ========================================================= */
  function mostrarMensaje(texto, tipo) {
    mensajeEl.textContent = texto;
    mensajeEl.className   = `mensaje ${tipo}`;
  }

  function limpiarMensaje() {
    mensajeEl.textContent = '';
    mensajeEl.className   = 'mensaje oculto';
  }

  /* =========================================================
     HINT DE PRIORIDAD EN TIEMPO REAL
  ========================================================= */
  if (motivoInput && hintPrioridad) {
    motivoInput.addEventListener('input', () => {
      const valor = motivoInput.value.trim();
      if (!valor) {
        hintPrioridad.style.display = 'none';
        return;
      }
      const prio = calcularPrioridadJS(valor);
      const claseMap = { Alta: 'hint-alta', Media: 'hint-media', Baja: 'hint-baja' };
      const iconoMap = { Alta: '🔴', Media: '🟡', Baja: '🟢' };
      hintPrioridad.className = `hint-prioridad ${claseMap[prio]}`;
      hintPrioridad.textContent = `Prioridad sugerida: ${iconoMap[prio]} ${prio}`;
      hintPrioridad.style.display = 'inline-block';
    });
  }

  /* =========================================================
     CARGAR CITAS DESDE EL SERVIDOR
  ========================================================= */
  async function cargarCitas() {
    mostrarSpinner(lista);
    try {
      const res = await fetch('/index.php?api=citas');
      const json = await res.json();
      if (json.success) {
        citasData = json.data || [];
      } else {
        citasData = [];
        lista.innerHTML = `<p class="mensaje error">${json.mensaje || 'Error al cargar citas.'}</p>`;
        return;
      }
    } catch (err) {
      citasData = [];
      lista.innerHTML = '<p class="mensaje error">Error de red al cargar las citas. Intentá de nuevo.</p>';
      return;
    }
    renderizarCitas();
  }

  /* =========================================================
     RENDERIZAR CITAS (con filtros en memoria)
  ========================================================= */
  function renderizarCitas() {
    const prioFiltro   = filtroPrioridad ? filtroPrioridad.value : 'todas';
    const estadoFiltro = filtroEstado    ? filtroEstado.value    : 'todos';

    const filtradas = citasData.filter(c => {
      const okPrio   = prioFiltro   === 'todas' || c.prioridad === prioFiltro;
      const okEstado = estadoFiltro === 'todos'  || c.estado    === estadoFiltro;
      return okPrio && okEstado;
    });

    lista.innerHTML = '';

    if (filtradas.length === 0) {
      lista.innerHTML = '<p class="texto-ayuda">No hay citas que coincidan con los filtros.</p>';
      return;
    }

    filtradas.forEach(cita => {
      const card = document.createElement('div');
      card.className = 'cita-card';

      card.innerHTML = `
        <div class="cita-header">
          <span class="badge badge-prioridad badge-${(cita.prioridad || '').toLowerCase()}">${cita.prioridad || ''}</span>
          <span class="badge badge-estado badge-estado-${(cita.estado || '').toLowerCase()}">${cita.estado || ''}</span>
        </div>
        <p><strong>Especialidad:</strong> ${cita.especialidad || ''}</p>
        <p><strong>Motivo:</strong> ${cita.motivo || ''}</p>
        <p><strong>Fecha:</strong> ${formatearFecha(cita.fecha)}</p>
        <p><strong>Hora:</strong> ${cita.hora || ''}</p>
        <div class="cita-acciones">
          ${cita.estado !== 'Cancelada' ? `
            <button class="btn btn-secundario btn-sm btn-reprogramar" data-id="${cita.id}">Reprogramar</button>
            <button class="btn btn-cancelar btn-sm btn-cancelar-cita" data-id="${cita.id}">Cancelar</button>
          ` : '<span class="texto-ayuda">Esta cita fue cancelada.</span>'}
        </div>
      `;

      lista.appendChild(card);
    });
  }

  /* =========================================================
     MOSTRAR / OCULTAR FORMULARIO
  ========================================================= */
  if (btnNueva) {
    btnNueva.addEventListener('click', () => {
      if (indexEdicion) indexEdicion.value = '';
      if (tituloForm) tituloForm.textContent = 'Agendar cita';
      form.reset();
      if (hintPrioridad) hintPrioridad.style.display = 'none';
      limpiarMensaje();
      formularioVista.classList.remove('oculto');
      formularioVista.scrollIntoView({ behavior: 'smooth' });
    });
  }

  if (btnCancelarForm) {
    btnCancelarForm.addEventListener('click', () => {
      formularioVista.classList.add('oculto');
      limpiarMensaje();
    });
  }

  if (btnVerTodas) {
    btnVerTodas.addEventListener('click', () => {
      if (filtroPrioridad) filtroPrioridad.value = 'todas';
      if (filtroEstado)    filtroEstado.value    = 'todos';
      renderizarCitas();
    });
  }

  /* =========================================================
     ENVÍO DEL FORMULARIO (agendar + reprogramar)
  ========================================================= */
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const especialidad = document.getElementById('especialidad')?.value || '';
      const motivo       = document.getElementById('motivo')?.value.trim() || '';
      const fecha        = document.getElementById('fecha')?.value || '';
      const hora         = document.getElementById('hora')?.value || '';
      const prioridad    = document.getElementById('prioridad')?.value || 'Media';

      if (!especialidad || !motivo || !fecha || !hora) {
        mostrarMensaje('Todos los campos son obligatorios.', 'error');
        return;
      }

      const hoy = new Date().toISOString().split('T')[0];
      if (fecha < hoy) {
        mostrarMensaje('La fecha no puede ser pasada.', 'error');
        return;
      }

      const id  = indexEdicion ? indexEdicion.value : '';
      const url = id
        ? '/index.php?accion=reprogramar_cita'
        : '/index.php?accion=agregar_cita';

      const fd = new FormData();
      fd.append('especialidad', especialidad);
      fd.append('motivo', motivo);
      fd.append('fecha', fecha);
      fd.append('hora', hora);
      fd.append('prioridad', prioridad);
      if (id) fd.append('id', id);

      try {
        const res = await postForm(url, fd);
        if (res.success) {
          // Mostrar prioridad calculada si difiere de la manual
          let msgExtra = '';
          if (res.prioridad_calculada && res.prioridad_calculada !== prioridad) {
            msgExtra = ` (Prioridad sugerida por el sistema: ${res.prioridad_calculada})`;
          }
          mostrarMensaje((res.mensaje || 'Cita guardada correctamente.') + msgExtra, 'ok');
          form.reset();
          if (indexEdicion) indexEdicion.value = '';
          if (hintPrioridad) hintPrioridad.style.display = 'none';
          formularioVista.classList.add('oculto');
          notificarSistema(id ? 'Cita reprogramada' : 'Nueva cita creada');
          await cargarCitas();
        } else {
          mostrarMensaje(res.mensaje || 'Error al guardar la cita.', 'error');
        }
      } catch (err) {
        mostrarMensaje('Error de red. Intentá de nuevo.', 'error');
      }
    });
  }

  /* =========================================================
     ACCIONES VIA DELEGACIÓN (cancelar / reprogramar)
  ========================================================= */
  if (lista) {
    lista.addEventListener('click', (e) => {

      /* CANCELAR */
      if (e.target.matches('.btn-cancelar-cita')) {
        const id = e.target.dataset.id;
        mostrarModal({
          titulo: 'Cancelar cita',
          mensaje: '¿Estás seguro de que querés cancelar esta cita? Esta acción no se puede deshacer.',
          textoConfirmar: 'Sí, cancelar',
          textoCancel: 'No, volver',
          onConfirmar: async () => {
            try {
              const res = await postForm('/index.php?accion=cancelar_cita', { id });
              if (res.success) {
                notificarSistema('Cita cancelada');
                await cargarCitas();
              } else {
                lista.insertAdjacentHTML('afterbegin', `<p class="mensaje error">${res.mensaje || 'Error al cancelar.'}</p>`);
              }
            } catch (err) {
              lista.insertAdjacentHTML('afterbegin', '<p class="mensaje error">Error de red al cancelar la cita.</p>');
            }
          }
        });
      }

      /* REPROGRAMAR */
      if (e.target.matches('.btn-reprogramar')) {
        const id   = e.target.dataset.id;
        const cita = citasData.find(c => String(c.id) === String(id));
        if (!cita) return;

        const espEl = document.getElementById('especialidad');
        const motEl = document.getElementById('motivo');
        const fecEl = document.getElementById('fecha');
        const horEl = document.getElementById('hora');
        const priEl = document.getElementById('prioridad');

        if (espEl) espEl.value = cita.especialidad || '';
        if (motEl) motEl.value = cita.motivo || '';
        if (fecEl) fecEl.value = cita.fecha  || '';
        if (horEl) horEl.value = cita.hora   || '';
        if (priEl) priEl.value = cita.prioridad || 'Media';
        if (indexEdicion) indexEdicion.value = id;

        if (tituloForm) tituloForm.textContent = 'Reprogramar cita';
        limpiarMensaje();
        if (hintPrioridad) hintPrioridad.style.display = 'none';
        formularioVista.classList.remove('oculto');
        formularioVista.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }

  /* =========================================================
     FILTROS
  ========================================================= */
  if (filtroPrioridad) filtroPrioridad.addEventListener('change', renderizarCitas);
  if (filtroEstado)    filtroEstado.addEventListener('change',    renderizarCitas);

  /* =========================================================
     NOTIFICACIONES CRUZADAS (shared via localStorage)
  ========================================================= */
  function notificarSistema(texto) {
    const notifs = JSON.parse(localStorage.getItem('notificaciones')) || [];
    notifs.unshift({ texto, fecha: new Date().toLocaleString('es-CR') });
    if (notifs.length > 20) notifs.pop();
    localStorage.setItem('notificaciones', JSON.stringify(notifs));
  }

  /* =========================================================
     HELPERS
  ========================================================= */
  function formatearFecha(iso) {
    if (!iso) return '';
    const [y, m, d] = iso.split('-');
    return `${d}/${m}/${y}`;
  }

  /* =========================================================
     INIT
  ========================================================= */
  cargarCitas();

});
