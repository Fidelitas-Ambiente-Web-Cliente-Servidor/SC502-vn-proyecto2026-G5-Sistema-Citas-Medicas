/**
 * diagnosticos.js - Modulo de Diagnosticos e Historial
 * Guarda diagnosticos, filtra el historial medico del usuario
 * y gestiona las notificaciones internas con campana y badge.
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
  const form              = document.getElementById('formDiagnostico');
  const historialDiv      = document.getElementById('historialMedico');
  const mensajeEl         = document.getElementById('mensajeDiag');
  const buscador          = document.getElementById('buscadorHistorial');
  const filtroFecha       = document.getElementById('filtroFechaHistorial');
  const panelNotif        = document.getElementById('panelNotificaciones');
  const seccionNotif      = document.getElementById('seccionNotificaciones');
  const btnCampana        = document.getElementById('btnCampana');
  const badgeNotif        = document.getElementById('badgeNotif');
  const btnLimpiarNotif   = document.getElementById('btnLimpiarNotif');
  const btnCerrarNotif    = document.getElementById('btnCerrarNotif');
  const btnLimpiarForm    = document.getElementById('btnLimpiarForm');
  const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
  const btnBorrarHistorial= document.getElementById('btnBorrarHistorial');
  const contadorHistorial = document.getElementById('contadorHistorial');

  /* Estado local: array de registros del historial */
  let historialData = [];

  /* =========================================================
     SPINNER
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
    setTimeout(() => limpiarMensaje(), 4000);
  }

  function limpiarMensaje() {
    mensajeEl.textContent = '';
    mensajeEl.className   = 'mensaje oculto';
  }

  /* =========================================================
     CARGAR HISTORIAL DESDE EL SERVIDOR
  ========================================================= */
  async function cargarHistorial() {
    mostrarSpinner(historialDiv);
    if (contadorHistorial) contadorHistorial.textContent = '';
    try {
      const res  = await fetch('/index.php?api=historial');
      const json = await res.json();
      if (json.success) {
        historialData = json.data || [];
      } else {
        historialData = [];
        historialDiv.innerHTML = `<p class="mensaje error">${json.mensaje || 'Error al cargar historial.'}</p>`;
        return;
      }
    } catch (err) {
      historialData = [];
      historialDiv.innerHTML = '<p class="mensaje error">Error de red al cargar el historial. Intentá de nuevo.</p>';
      return;
    }
    renderizarHistorial();
  }

  /* =========================================================
     RENDERIZAR HISTORIAL (filtros en memoria)
  ========================================================= */
  function renderizarHistorial() {
    const texto = buscador ? buscador.value.toLowerCase() : '';
    const fecha = filtroFecha ? filtroFecha.value : '';

    const filtrado = historialData.filter(item => {
      const coincideTexto =
        (item.diagnostico || '').toLowerCase().includes(texto) ||
        (item.sintomas    || '').toLowerCase().includes(texto) ||
        (item.paciente    || '').toLowerCase().includes(texto);
      const coincideFecha = !fecha || item.fechaISO === fecha;
      return coincideTexto && coincideFecha;
    });

    historialDiv.innerHTML = '';

    if (contadorHistorial) {
      contadorHistorial.textContent = filtrado.length === 0
        ? 'No se encontraron registros.'
        : `Mostrando ${filtrado.length} de ${historialData.length} registro(s).`;
    }

    if (filtrado.length === 0) return;

    filtrado.forEach(item => {
      const card = document.createElement('div');
      card.className = 'historial-card';

      card.innerHTML = `
        <div class="historial-card-header">
          <span class="historial-fecha">📅 ${item.fecha || ''}</span>
          <span class="historial-paciente">👤 ${item.paciente || ''}</span>
          <button class="btn-eliminar-historial btn-sm" data-id="${item.id}" title="Eliminar registro">✕</button>
        </div>
        <p><strong>Síntomas:</strong> ${item.sintomas || ''}</p>
        <p><strong>Diagnóstico:</strong> ${item.diagnostico || ''}</p>
        <p><strong>Tratamiento:</strong> ${item.tratamiento || ''}</p>
        ${item.notas ? `<p class="historial-notas"><strong>Notas:</strong> ${item.notas}</p>` : ''}
      `;

      historialDiv.appendChild(card);
    });
  }

  /* =========================================================
     GUARDAR DIAGNÓSTICO
  ========================================================= */
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const paciente    = document.getElementById('paciente')?.value.trim()    || '';
      const sintomas    = document.getElementById('sintomas')?.value.trim()    || '';
      const diagnostico = document.getElementById('diagnostico')?.value.trim() || '';
      const tratamiento = document.getElementById('tratamiento')?.value.trim() || '';
      const notas       = document.getElementById('notas')?.value.trim()       || '';

      if (!paciente || !sintomas || !diagnostico || !tratamiento) {
        mostrarMensaje('Los campos Paciente, Síntomas, Diagnóstico y Tratamiento son obligatorios.', 'error');
        return;
      }

      const fd = new FormData();
      fd.append('paciente',    paciente);
      fd.append('sintomas',    sintomas);
      fd.append('diagnostico', diagnostico);
      fd.append('tratamiento', tratamiento);
      fd.append('notas',       notas);

      try {
        const res = await postForm('/index.php?accion=guardar_diagnostico', fd);
        if (res.success) {
          form.reset();
          mostrarMensaje('Diagnóstico registrado correctamente.', 'ok');
          crearNotificacion('Diagnóstico registrado', 'diagnostico');
          await cargarHistorial();
        } else {
          mostrarMensaje(res.mensaje || 'Error al guardar el diagnóstico.', 'error');
        }
      } catch (err) {
        mostrarMensaje('Error de red. Intentá de nuevo.', 'error');
      }
    });
  }

  /* =========================================================
     LIMPIAR FORMULARIO
  ========================================================= */
  if (btnLimpiarForm) {
    btnLimpiarForm.addEventListener('click', () => {
      form.reset();
      limpiarMensaje();
    });
  }

  /* =========================================================
     ELIMINAR REGISTRO INDIVIDUAL (delegación)
  ========================================================= */
  if (historialDiv) {
    historialDiv.addEventListener('click', (e) => {
      if (e.target.matches('.btn-eliminar-historial')) {
        const id = e.target.dataset.id;
        mostrarModal({
          titulo: 'Eliminar registro',
          mensaje: '¿Estás seguro de que querés eliminar este registro del historial?',
          textoConfirmar: 'Sí, eliminar',
          textoCancel: 'Cancelar',
          onConfirmar: async () => {
            try {
              const res = await postForm('/index.php?accion=eliminar_diagnostico', { id });
              if (res.success) {
                crearNotificacion('Registro médico eliminado', 'info');
                await cargarHistorial();
              } else {
                mostrarMensaje(res.mensaje || 'Error al eliminar.', 'error');
              }
            } catch (err) {
              mostrarMensaje('Error de red al eliminar. Intentá de nuevo.', 'error');
            }
          }
        });
      }
    });
  }

  /* =========================================================
     BUSCADOR + FILTRO FECHA
  ========================================================= */
  if (buscador)    buscador.addEventListener('input',    renderizarHistorial);
  if (filtroFecha) filtroFecha.addEventListener('change', renderizarHistorial);

  if (btnLimpiarFiltros) {
    btnLimpiarFiltros.addEventListener('click', () => {
      if (buscador)    buscador.value    = '';
      if (filtroFecha) filtroFecha.value = '';
      renderizarHistorial();
    });
  }

  /* =========================================================
     BORRAR HISTORIAL COMPLETO
  ========================================================= */
  if (btnBorrarHistorial) {
    btnBorrarHistorial.addEventListener('click', () => {
      mostrarModal({
        titulo: 'Borrar historial',
        mensaje: '¿Estás seguro de que querés borrar todo el historial médico? Esta acción no se puede deshacer.',
        textoConfirmar: 'Sí, borrar todo',
        textoCancel: 'Cancelar',
        onConfirmar: async () => {
          try {
            const res = await postForm('/index.php?accion=borrar_historial', {});
            if (res.success) {
              historialData = [];
              renderizarHistorial();
              crearNotificacion('Historial médico borrado', 'info');
            } else {
              mostrarMensaje(res.mensaje || 'Error al borrar el historial.', 'error');
            }
          } catch (err) {
            mostrarMensaje('Error de red al borrar el historial.', 'error');
          }
        }
      });
    });
  }

  /* =========================================================
     BOTÓN IMPRIMIR
  ========================================================= */
  document.getElementById('btnImprimir')?.addEventListener('click', () => window.print());

  /* =========================================================
     SISTEMA DE NOTIFICACIONES (localStorage)
  ========================================================= */
  const ICONOS = {
    diagnostico: '🩺',
    cita:        '📋',
    info:        'ℹ️',
    default:     '🔔'
  };

  function crearNotificacion(texto, tipo = 'default') {
    const notifs = JSON.parse(localStorage.getItem('notificaciones')) || [];
    notifs.unshift({
      texto,
      tipo,
      fecha: new Date().toLocaleString('es-CR')
    });
    if (notifs.length > 20) notifs.pop();
    localStorage.setItem('notificaciones', JSON.stringify(notifs));

    renderizarNotificaciones();
    parpadeoCampana();
  }

  function renderizarNotificaciones() {
    if (!panelNotif) return;
    const notifs = JSON.parse(localStorage.getItem('notificaciones')) || [];

    panelNotif.innerHTML = '';

    if (notifs.length === 0) {
      panelNotif.innerHTML = '<p class="texto-ayuda sin-notificaciones">No hay notificaciones recientes.</p>';
      if (badgeNotif) badgeNotif.classList.add('oculto');
      return;
    }

    if (badgeNotif) {
      badgeNotif.textContent = notifs.length > 9 ? '9+' : notifs.length;
      badgeNotif.classList.remove('oculto');
    }

    notifs.forEach(n => {
      const el = document.createElement('div');
      el.className = `notificacion notif-tipo-${n.tipo || 'default'}`;
      el.innerHTML = `
        <span class="notif-ico">${ICONOS[n.tipo] || ICONOS.default}</span>
        <span class="notif-texto">${n.texto}</span>
        <span class="notif-hora">${n.fecha}</span>
      `;
      panelNotif.appendChild(el);
    });
  }

  function parpadeoCampana() {
    if (!btnCampana) return;
    btnCampana.classList.add('campana-activa');
    setTimeout(() => btnCampana.classList.remove('campana-activa'), 1000);
  }

  if (btnCampana) {
    btnCampana.addEventListener('click', () => {
      if (!seccionNotif) return;
      seccionNotif.classList.toggle('oculto');
      if (!seccionNotif.classList.contains('oculto')) {
        seccionNotif.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }

  if (btnCerrarNotif) {
    btnCerrarNotif.addEventListener('click', () => {
      seccionNotif?.classList.add('oculto');
    });
  }

  if (btnLimpiarNotif) {
    btnLimpiarNotif.addEventListener('click', () => {
      localStorage.removeItem('notificaciones');
      renderizarNotificaciones();
    });
  }

  /* =========================================================
     INIT
  ========================================================= */
  renderizarNotificaciones();
  cargarHistorial();

  // Notificaciones de ejemplo al cargar (si no hay ninguna)
  const notifsPrevias = JSON.parse(localStorage.getItem('notificaciones')) || [];
  if (notifsPrevias.length === 0) {
    crearNotificacion('Nueva cita creada', 'cita');
    crearNotificacion('Cita reprogramada', 'cita');
  }

});
