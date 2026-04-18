/**
 * main.js - Panel Principal (Dashboard)
 * Aplica el modo oscuro y carga las estadisticas del sistema
 * (citas activas, diagnosticos, personal medico, prioridad alta)
 * desde la API al iniciar la pagina.
 */
document.addEventListener('DOMContentLoaded', () => {

  /* ── Dark mode ─────────────────────────────────────── */
  const applyDark = (on) => {
    document.body.classList.toggle('dark-mode', on);
    const btn = document.getElementById('btnDarkMode');
    if (btn) btn.textContent = on ? '☀️' : '🌙';
  };
  applyDark(localStorage.getItem('darkMode') === '1');

  const btnDark = document.getElementById('btnDarkMode');
  if (btnDark) {
    btnDark.addEventListener('click', () => {
      const on = !document.body.classList.contains('dark-mode');
      applyDark(on);
      localStorage.setItem('darkMode', on ? '1' : '0');
    });
  }

  /* ── Stats ─────────────────────────────────────────── */
  const statsGrid = document.getElementById('statsGrid');
  if (!statsGrid) return;

  fetch('/index.php?api=stats')
    .then(r => r.json())
    .then(res => {
      if (!res.success) { statsGrid.innerHTML = ''; return; }
      statsGrid.innerHTML = `
        <div class="stat-card"><span class="stat-ico">📅</span><span class="stat-numero">${res.citas_activas}</span><span class="stat-label">Citas activas</span></div>
        <div class="stat-card"><span class="stat-ico">🩺</span><span class="stat-numero">${res.diagnosticos}</span><span class="stat-label">Diagnósticos</span></div>
        <div class="stat-card"><span class="stat-ico">👨‍⚕️</span><span class="stat-numero">${res.personal}</span><span class="stat-label">Personal médico</span></div>
        <div class="stat-card"><span class="stat-ico">🔴</span><span class="stat-numero">${res.citas_alta}</span><span class="stat-label">Prioridad alta</span></div>
      `;
    })
    .catch(() => { statsGrid.innerHTML = ''; });
});
