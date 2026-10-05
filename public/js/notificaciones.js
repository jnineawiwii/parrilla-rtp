/**
 * Campanita de notificaciones
 * - Consulta /api/notificaciones al cargar, cada 60s y al abrir el dropdown.
 */
(function () {
    'use strict';

    const campanita = document.getElementById('campanita');
    const lista     = document.getElementById('notif-list');
    const contador  = document.getElementById('notif-count');

    if (!campanita) {
        console.warn('[Notif] No se encontró #campanita en el DOM.');
        return;
    }

    // ---------- Abrir / cerrar ----------
    campanita.addEventListener('click', function (e) {
        e.preventDefault();
        const padre = campanita.closest('.dropdown-notif');
        if (!padre) return;
        padre.classList.toggle('abierto');
        if (padre.classList.contains('abierto')) {
            cargarNotifs();   // refrescar al abrir
        }
    });

    document.addEventListener('click', function (e) {
        const padre = campanita.closest('.dropdown-notif');
        if (padre && !padre.contains(e.target)) {
            padre.classList.remove('abierto');
        }
    });

    // ---------- Helpers ----------
    function escapar(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function normalizarFecha(iso) {
        if (!iso) return null;
        // 'YYYY-MM-DD HH:MM:SS' → 'YYYY-MM-DDTHH:MM:SS'
        const s = String(iso).replace(' ', 'T');
        const d = new Date(s);
        return isNaN(d.getTime()) ? null : d;
    }

    function fechaRelativa(iso) {
        const fecha = normalizarFecha(iso);
        if (!fecha) return '';

        const diff = Math.floor((Date.now() - fecha.getTime()) / 1000);
        if (diff < 0)      return 'ahora';
        if (diff < 60)     return 'hace unos segundos';
        if (diff < 3600)   return 'hace ' + Math.floor(diff / 60) + ' min';
        if (diff < 86400)  return 'hace ' + Math.floor(diff / 3600) + ' h';
        if (diff < 604800) return 'hace ' + Math.floor(diff / 86400) + ' días';

        return fecha.toLocaleDateString('es-MX', {
            day: '2-digit', month: 'short', year: 'numeric'
        });
    }

    function etiquetaTipo(tipo) {
        switch (tipo) {
            case 'recordatorio_hoy':    return '📅 HOY';
            case 'recordatorio_manana': return '⏰ MAÑANA';
            case 'mensaje':             return '💬 Mensaje';
            case 'sistema':             return '⚙️ Sistema';
            default:                    return '🔔 Notificación';
        }
    }

    // ---------- Cargar ----------
    let cargando = false;

    async function cargarNotifs() {
        if (cargando) return;
        cargando = true;

        try {
            const r = await fetch('/api/notificaciones', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            if (!r.ok) {
                console.warn('[Notif] HTTP', r.status);
                return;
            }

            const j = await r.json();

            // Contador
            if (contador) {
                const n = Number(j.no_leidas) || 0;
                contador.textContent = n;
                contador.style.display = (n > 0) ? 'inline-block' : 'none';
                contador.classList.toggle('pulso', n > 0);
            }

            if (!lista) return;

            const header =
                '<div class="header-notif">' +
                '<span>🔔 Notificaciones</span>' +
                '<a href="/notificaciones">Ver todas</a>' +
                '</div>';

            const items = Array.isArray(j.items) ? j.items : [];

            if (items.length === 0) {
                lista.innerHTML = header + '<div class="vacio">Sin notificaciones</div>';
                return;
            }

            const cuerpo = items.map(n => {
                const id       = parseInt(n.id, 10) || 0;
                const tipo     = n.titulo || 'sistema';
                const etiqueta = etiquetaTipo(tipo);
                const clase    = n.leida ? '' : 'no-leida';
                const fecha    = fechaRelativa(n.created_at);

                return `
                    <a href="/notificaciones/abrir/${id}"
                       class="item ${clase}"
                       data-tipo="${escapar(tipo)}">
                        <div class="titulo-notif">${etiqueta}</div>
                        <div class="texto-notif">${escapar(n.mensaje)}</div>
                        <span class="fecha-notif">${fecha}</span>
                    </a>
                `;
            }).join('');

            lista.innerHTML = header + cuerpo;

        } catch (e) {
            console.error('[Notif] Error:', e);
        } finally {
            cargando = false;
        }
    }

    // ---------- Disparadores ----------
    cargarNotifs();
    setInterval(cargarNotifs, 60000);

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') cargarNotifs();
    });
})();