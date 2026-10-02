/**
 * Campanita de notificaciones
 * Consulta /api/notificaciones cada 60 segundos.
 */
(function () {
    const campanita = document.getElementById('campanita');
    const lista     = document.getElementById('notif-list');
    const contador  = document.getElementById('notif-count');

    if (!campanita) return;

    // ---------- Abrir / cerrar el dropdown ----------
    campanita.addEventListener('click', function (e) {
        e.preventDefault();
        const padre = campanita.closest('.dropdown-notif');
        if (padre) padre.classList.toggle('abierto');
    });

    // Cerrar al hacer clic fuera
    document.addEventListener('click', function (e) {
        const padre = campanita.closest('.dropdown-notif');
        if (padre && !padre.contains(e.target)) {
            padre.classList.remove('abierto');
        }
    });

    // ---------- Escapar HTML ----------
    function escapar(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    // ---------- Formato de fecha relativo ----------
    function fechaRelativa(iso) {
        const fecha = new Date(iso);
        const ahora = new Date();
        const diff  = Math.floor((ahora - fecha) / 1000); // segundos

        if (diff < 60)        return 'hace unos segundos';
        if (diff < 3600)      return 'hace ' + Math.floor(diff / 60) + ' min';
        if (diff < 86400)     return 'hace ' + Math.floor(diff / 3600) + ' h';
        if (diff < 604800)    return 'hace ' + Math.floor(diff / 86400) + ' días';

        // Más de una semana: fecha completa
        return fecha.toLocaleDateString('es-MX', {
            day: '2-digit', month: 'short', year: 'numeric'
        });
    }

    // ---------- Etiqueta amigable según tipo ----------
    function etiquetaTipo(tipo) {
        switch (tipo) {
            case 'recordatorio_hoy':    return '📅 HOY';
            case 'recordatorio_manana': return '⏰ MAÑANA';
            case 'mensaje':             return '💬 Mensaje';
            case 'sistema':             return '⚙️ Sistema';
            default:                    return '🔔 Notificación';
        }
    }

    // ---------- Cargar notificaciones ----------
    async function cargarNotifs() {
        try {
            const r = await fetch('/api/notificaciones', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            if (!r.ok) {
                console.warn('[Notif] Respuesta no OK:', r.status);
                return;
            }

            const j = await r.json();

            // Contador
            if (contador) {
                const n = j.no_leidas || 0;
                contador.textContent = n;
                contador.style.display = (n > 0) ? 'inline-block' : 'none';

                // Pulso si hay nuevas
                if (n > 0) contador.classList.add('pulso');
                else       contador.classList.remove('pulso');
            }

            // Lista
            if (!lista) return;

            // Conservar encabezado, regenerar solo el cuerpo
            const header = '<div class="header-notif">' +
                           '<span>🔔 Notificaciones</span>' +
                           '<a href="/notificaciones">Ver todas</a>' +
                           '</div>';

            if (!j.items || j.items.length === 0) {
                lista.innerHTML = header + '<div class="vacio">Sin notificaciones</div>';
                return;
            }

            const cuerpo = j.items.map(n => {
                const fecha   = fechaRelativa(n.created_at);
                const tipo    = n.titulo || 'sistema';
                const etiqueta = etiquetaTipo(tipo);
                const clase   = n.leida ? '' : 'no-leida';

                return `
                    <a href="/notificaciones/abrir/${n.id}"
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
        }
    }

    // Cargar al inicio y cada 60 segundos
    cargarNotifs();
    setInterval(cargarNotifs, 60000);
})();