/**
 * admin.js — Panel de administración de usuarios (solo admin)
 */

const ROLES = ['paciente', 'medico', 'admin'];
const ETIQUETAS = { paciente: 'Paciente', medico: 'Médico', admin: 'Admin' };

/* ── Estado del modal ─────────────────────────────────── */
let modoEdicion  = false;   // false = crear, true = editar
const usuariosMap = {};     // id → datos de usuario para edición segura

/* ── Init ─────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  const btnDark = document.getElementById('btnDarkMode');
  if (localStorage.getItem('darkMode') === 'true') document.body.classList.add('dark-mode');
  btnDark?.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
  });

  cargarUsuarios();
});

/* ─────────────────────────────────────────────────────────
   TABLA DE USUARIOS
───────────────────────────────────────────────────────── */

async function cargarUsuarios() {
  const wrap = document.getElementById('tablaWrap');
  wrap.innerHTML = '<div class="spinner-wrap"><div class="spinner"></div></div>';

  try {
    const res  = await fetch('/index.php?api=usuarios');
    const json = await res.json();

    if (!json.success) {
      wrap.innerHTML = `<p class="texto-error">${esc(json.error ?? 'Error al cargar usuarios')}</p>`;
      return;
    }

    if (!json.data.length) {
      wrap.innerHTML = '<p class="texto-ayuda">No hay usuarios registrados.</p>';
      return;
    }

    json.data.forEach(u => { usuariosMap[u.id] = u; });
    wrap.innerHTML = construirTabla(json.data);

  } catch {
    wrap.innerHTML = '<p class="texto-error">Error de conexión. Intentá recargar la página.</p>';
  }
}

function construirTabla(usuarios) {
  const filas = usuarios.map(u => {
    const esMiCuenta = u.id == MI_ID;
    const badge = `<span class="badge badge-${u.rol}" id="badge-${u.id}">${ETIQUETAS[u.rol] ?? u.rol}</span>`;

    const opciones = ROLES.map(r =>
      `<option value="${r}" ${u.rol === r ? 'selected' : ''}>${ETIQUETAS[r]}</option>`
    ).join('');

    const selectRol = esMiCuenta
      ? badge   // el admin no puede cambiar su propio rol
      : `<div class="rol-inline">
           <select class="select-rol input-sm" id="sel-${u.id}" onchange="cambioRolInline(${u.id})"
                   data-rol-original="${u.rol}">${opciones}</select>
           <button class="btn btn-confirmar btn-xs" id="btn-rol-${u.id}"
                   onclick="guardarRol(${u.id})" style="display:none">✓</button>
         </div>`;

    return `
      <tr id="fila-${u.id}">
        <td class="col-id">${u.id}</td>
        <td><strong>${esc(u.nombre)}</strong> ${esc(u.apellidos)}
            ${esMiCuenta ? '<span class="badge-yo">Vos</span>' : ''}</td>
        <td class="col-correo">${esc(u.correo)}</td>
        <td>${selectRol}</td>
        <td>
          <button class="btn btn-secundario btn-xs" onclick="abrirModalEditar(${u.id})">
            Editar
          </button>
        </td>
      </tr>`;
  }).join('');

  return `
    <table class="tabla-datos tabla-admin">
      <thead>
        <tr>
          <th class="col-id">#</th>
          <th>Nombre</th>
          <th class="col-correo">Correo</th>
          <th>Rol</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>${filas}</tbody>
    </table>`;
}

/* ─────────────────────────────────────────────────────────
   CAMBIO RÁPIDO DE ROL (en la linea de la tabla)
───────────────────────────────────────────────────────── */

function cambioRolInline(id) {
  const sel    = document.getElementById(`sel-${id}`);
  const btnRol = document.getElementById(`btn-rol-${id}`);
  btnRol.style.display = sel.value !== sel.dataset.rolOriginal ? 'inline-flex' : 'none';
}

async function guardarRol(id) {
  const sel    = document.getElementById(`sel-${id}`);
  const btnRol = document.getElementById(`btn-rol-${id}`);
  const rol    = sel.value;

  btnRol.disabled = true;
  btnRol.textContent = '…';

  try {
    const form = new FormData();
    form.append('id', id);
    form.append('rol', rol);

    const res  = await fetch('/index.php?accion=actualizar_rol', { method: 'POST', body: form });
    const json = await res.json();

    if (json.success) {
      sel.dataset.rolOriginal = rol;
      btnRol.style.display    = 'none';

      // actualizar badge si la fila tiene uno
      const badge = document.getElementById(`badge-${id}`);
      if (badge) { badge.className = `badge badge-${rol}`; badge.textContent = ETIQUETAS[rol]; }

      mostrarMensaje(`Rol de usuario #${id} actualizado a <strong>${ETIQUETAS[rol]}</strong>.`, 'exito');
    } else {
      mostrarMensaje(json.error ?? 'No se pudo actualizar el rol.', 'error');
      sel.value = sel.dataset.rolOriginal;
      btnRol.style.display = 'none';
    }
  } catch {
    mostrarMensaje('Error de conexión.', 'error');
  } finally {
    btnRol.disabled = false;
    btnRol.textContent = '✓';
  }
}

/* ─────────────────────────────────────────────────────────
   MODAL — CREAR USUARIO
───────────────────────────────────────────────────────── */

function abrirModalCrear() {
  modoEdicion = false;
  document.getElementById('modalTitulo').textContent = 'Nuevo usuario';
  document.getElementById('formUsuario').reset();
  document.getElementById('campoId').value = '';

  // Contraseña es requerida al crear
  const passInput = document.getElementById('campoPassword');
  passInput.required = true;
  document.getElementById('passHint').textContent = '(mín. 6 caracteres)';
  document.getElementById('campoPasswordWrap').style.display = '';

  document.getElementById('btnSubmitModal').textContent = 'Crear usuario';
  ocultarMsgModal();
  document.getElementById('modalOverlay').classList.remove('oculto');
  document.getElementById('campoNombre').focus();
}

/* ─────────────────────────────────────────────────────────
   MODAL — EDITAR USUARIO
───────────────────────────────────────────────────────── */

function abrirModalEditar(id) {
  const u = usuariosMap[id];
  if (!u) return;

  modoEdicion = true;
  document.getElementById('modalTitulo').textContent = 'Editar usuario';
  document.getElementById('campoId').value        = u.id;
  document.getElementById('campoNombre').value    = u.nombre;
  document.getElementById('campoApellidos').value = u.apellidos;
  document.getElementById('campoCorreo').value    = u.correo;
  document.getElementById('campoRol').value       = u.rol;

  // Contraseña no se edita aquí
  const passInput = document.getElementById('campoPassword');
  passInput.required = false;
  passInput.value    = '';
  document.getElementById('passHint').textContent = '(dejá vacío para no cambiar)';
  document.getElementById('campoPasswordWrap').style.display = 'none';

  document.getElementById('btnSubmitModal').textContent = 'Guardar cambios';
  ocultarMsgModal();
  document.getElementById('modalOverlay').classList.remove('oculto');
  document.getElementById('campoNombre').focus();
}

function cerrarModal() {
  document.getElementById('modalOverlay').classList.add('oculto');
}

function cerrarModalSiClick(e) {
  if (e.target === document.getElementById('modalOverlay')) cerrarModal();
}

/* ─────────────────────────────────────────────────────────
   SUBMIT DEL MODAL (crear o editar)
───────────────────────────────────────────────────────── */

async function submitUsuario(e) {
  e.preventDefault();

  const btn    = document.getElementById('btnSubmitModal');
  const accion = modoEdicion ? 'editar_usuario' : 'crear_usuario';

  btn.disabled = true;
  btn.textContent = 'Guardando…';
  ocultarMsgModal();

  try {
    const form = new FormData(document.getElementById('formUsuario'));
    const res  = await fetch(`/index.php?accion=${accion}`, { method: 'POST', body: form });
    const json = await res.json();

    if (json.success) {
      cerrarModal();
      mostrarMensaje(
        modoEdicion ? 'Usuario actualizado correctamente.' : 'Usuario creado correctamente.',
        'exito'
      );
      await cargarUsuarios();   // recargar tabla
    } else {
      mostrarMsgModal(json.error ?? 'Ocurrió un error. Intentá de nuevo.', 'error');
    }
  } catch {
    mostrarMsgModal('Error de conexión.', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = modoEdicion ? 'Guardar cambios' : 'Crear usuario';
  }
}

/* ─────────────────────────────────────────────────────────
   MENSAJES DE FEEDBACK
───────────────────────────────────────────────────────── */

function mostrarMensaje(texto, tipo) {
  const div = document.getElementById('msgAdmin');
  div.innerHTML = texto;
  div.className = `mensaje mensaje-${tipo}`;
  div.classList.remove('oculto');
  clearTimeout(div._timer);
  div._timer = setTimeout(() => div.classList.add('oculto'), 5000);
}

function mostrarMsgModal(texto, tipo) {
  const div = document.getElementById('msgModal');
  div.innerHTML = texto;
  div.className = `mensaje mensaje-${tipo}`;
  div.classList.remove('oculto');
}

function ocultarMsgModal() {
  const div = document.getElementById('msgModal');
  div.classList.add('oculto');
}

/* ─────────────────────────────────────────────────────────
   UTILIDADES
───────────────────────────────────────────────────────── */

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}
