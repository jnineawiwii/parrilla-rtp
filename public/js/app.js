/* ============================================================
   MENÚ DE USUARIO (avatar)
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('userMenuToggle');
    const panel  = document.getElementById('userMenuPanel');

    if (!toggle || !panel) return;

    toggle.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const abierto = !panel.hidden;
        panel.hidden = abierto;
        toggle.setAttribute('aria-expanded', String(!abierto));
    });

    // Cerrar al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!panel.hidden &&
            !panel.contains(e.target) &&
            !toggle.contains(e.target)) {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    // Cerrar con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !panel.hidden) {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
});