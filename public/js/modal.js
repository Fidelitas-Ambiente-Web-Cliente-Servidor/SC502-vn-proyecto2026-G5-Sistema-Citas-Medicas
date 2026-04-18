/**
 * Modal de confirmación reutilizable
 * Uso: mostrarModal({ titulo, mensaje, textoConfirmar, onConfirmar })
 */
function mostrarModal({ titulo = 'Confirmar', mensaje = '¿Estás seguro?', textoConfirmar = 'Confirmar', textoCancel = 'Cancelar', onConfirmar }) {
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.innerHTML = `
    <div class="modal-box" role="dialog" aria-modal="true">
      <p class="modal-titulo">${titulo}</p>
      <p class="modal-mensaje">${mensaje}</p>
      <div class="modal-acciones">
        <button class="btn btn-secundario btn-sm" id="modalCancelar">${textoCancel}</button>
        <button class="btn btn-principal btn-sm" id="modalConfirmar">${textoConfirmar}</button>
      </div>
    </div>
  `;
  document.body.appendChild(overlay);

  const cerrar = () => overlay.remove();
  overlay.querySelector('#modalCancelar').addEventListener('click', cerrar);
  overlay.querySelector('#modalConfirmar').addEventListener('click', () => {
    cerrar();
    if (typeof onConfirmar === 'function') onConfirmar();
  });
  // Cerrar al hacer click fuera del box
  overlay.addEventListener('click', (e) => { if (e.target === overlay) cerrar(); });
}
