(function () {
    const PALETA = {
        facebook:  '#266CB4',
        instagram: '#D72F89',
        x:         '#55585A',
        youtube:   '#E5074C',
    };
    const NOMBRES = {
        facebook:  'Facebook',
        instagram: 'Instagram',
        x:         'X',
        youtube:   'YouTube',
    };
    const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const REDES = ['facebook','instagram','x','youtube'];

    async function cargarDatos(anio, publicacionId) {
        const params = new URLSearchParams({ anio, publicacion: publicacionId });
        const r = await fetch('/api/graficas?' + params.toString());
        return await r.json();
    }

    function mostrarSinDatos(ctx, mensaje) {
        if (!ctx) return;
        ctx.parentElement.insertAdjacentHTML('beforeend',
            `<p class="text-muted chart-empty" style="text-align:center;padding:1rem;">${mensaje}</p>`);
    }

    function graficaPublicacion(canvasId, metricas) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        const labels = metricas.map(m => NOMBRES[m.red] || m.red);
        const datasets = [
            { label: 'Reacciones', data: metricas.map(m => Number(m.me_gusta || 0)), backgroundColor: '#a51d4b' },
            { label: 'Comentarios', data: metricas.map(m => Number(m.comentarios || 0)), backgroundColor: '#0878e8' },
            { label: 'Compartidos', data: metricas.map(m => Number(m.compartidos || 0)), backgroundColor: '#27a34a' },
        ];
        if (datasets.every(dataset => dataset.data.every(value => value === 0))) {
            mostrarSinDatos(ctx, 'Sin interacciones registradas para esta publicación.');
            return;
        }
        new Chart(ctx, {
            type: 'bar',
            data: { labels, datasets },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } },
        });
    }

    function graficaReaccionesFacebook(canvasId, metricas) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        const facebook = metricas.find(m => m.red === 'facebook');
        const fields = [
            ['Me gusta', 'fb_me_gusta', '#0878e8'],
            ['Me encanta', 'fb_me_encanta', '#e64969'],
            ['Me entristece', 'fb_me_entristece', '#e2a521'],
            ['Me sorprende', 'fb_me_sorprende', '#e2a521'],
            ['Me enoja', 'fb_me_enoja', '#df572f'],
            ['Me importa', 'fb_me_importa', '#7b4ab5'],
        ];
        const values = fields.map(([, key]) => Number(facebook?.[key] || 0));
        if (!facebook || values.every(value => value === 0)) {
            mostrarSinDatos(ctx, 'Captura las reacciones de Facebook en el formulario de la publicación.');
            return;
        }
        new Chart(ctx, {
            type: 'bar',
            data: { labels: fields.map(([label]) => label), datasets: [{ label: 'Reacciones', data: values, backgroundColor: fields.map(([, , color]) => color) }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
        });
    }

    function graficaSentimientoPublicacion(canvasId, metricas) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        const positives = metricas.reduce((sum, m) => sum + Number(m.coment_positivos || 0), 0);
        const negatives = metricas.reduce((sum, m) => sum + Number(m.coment_negativos || 0), 0);
        if (positives + negatives === 0) {
            mostrarSinDatos(ctx, 'Captura comentarios positivos y negativos en las métricas de la publicación.');
            return;
        }
        new Chart(ctx, {
            type: 'doughnut',
            data: { labels: ['Positivos', 'Negativos'], datasets: [{ data: [positives, negatives], backgroundColor: ['#27a34a', '#e20e17'], borderColor: '#fff', borderWidth: 2 }] },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
        });
    }

    // 🥧 Gráfica de pastel (dona) — distribución % de interacción por red
    function graficaPastel(canvasId, resumen) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        const totales = REDES.map(red => {
            const fila = resumen.find(r => r.red === red);
            return fila ? Number(fila.interaccion || 0) : 0;
        });
        const suma = totales.reduce((a, b) => a + b, 0);

        if (suma === 0) {
            // Sin datos: mostrar mensaje
            ctx.parentElement.insertAdjacentHTML('beforeend',
                '<p class="text-muted" style="text-align:center;padding:1rem;">Sin datos para este año.</p>');
            return;
        }

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: REDES.map(r => NOMBRES[r]),
                datasets: [{
                    data: totales,
                    backgroundColor: REDES.map(r => PALETA[r]),
                    borderColor: '#fff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const v = ctx.parsed;
                                const p = suma ? ((v / suma) * 100).toFixed(1) : 0;
                                return `${ctx.label}: ${v.toLocaleString()} (${p}%)`;
                            },
                        },
                    },
                },
            },
        });
    }

    // 🏢 Gráfica de edificio (barras verticales apiladas) — interacción mensual
    function graficaEdificio(canvasId, mensual) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        // Estructura: { facebook: [12], instagram: [12], x: [12], youtube: [12] }
        const series = {};
        REDES.forEach(r => series[r] = Array(12).fill(0));

        mensual.forEach(fila => {
            const idx = fila.mes - 1;
            if (series[fila.red] && idx >= 0 && idx < 12) {
                series[fila.red][idx] = Number(fila.interaccion || 0);
            }
        });

        const hayDatos = REDES.some(r => series[r].some(v => v > 0));
        if (!hayDatos) {
            ctx.parentElement.insertAdjacentHTML('beforeend',
                '<p class="text-muted" style="text-align:center;padding:1rem;">Sin datos para este año.</p>');
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: MESES,
                datasets: REDES.map(red => ({
                    label: NOMBRES[red],
                    data: series[red],
                    backgroundColor: PALETA[red],
                    borderColor: '#fff',
                    borderWidth: 1,
                })),
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { mode: 'index', intersect: false },
                },
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true },
                },
            },
        });
    }

    // 📈 Alcance (barras horizontales agrupadas, no apiladas)
    function graficaAlcance(canvasId, resumen) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        const valores = REDES.map(red => {
            const fila = resumen.find(r => r.red === red);
            return fila ? Number(fila.alcance || 0) : 0;
        });

        if (valores.every(v => v === 0)) {
            ctx.parentElement.insertAdjacentHTML('beforeend',
                '<p class="text-muted" style="text-align:center;padding:1rem;">Sin datos para este año.</p>');
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: REDES.map(r => NOMBRES[r]),
                datasets: [{
                    label: 'Alcance total',
                    data: valores,
                    backgroundColor: REDES.map(r => PALETA[r]),
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    // 💬 Sentimiento (dona: positivos vs negativos)
    function graficaSentimiento(canvasId, resumen) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        let pos = 0, neg = 0;
        resumen.forEach(r => {
            pos += Number(r.positivos || 0);
            neg += Number(r.negativos || 0);
        });

        if (pos + neg === 0) {
            ctx.parentElement.insertAdjacentHTML('beforeend',
                '<p class="text-muted" style="text-align:center;padding:1rem;">Sin comentarios registrados.</p>');
            return;
        }

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Positivos', 'Negativos'],
                datasets: [{
                    data: [pos, neg],
                    backgroundColor: ['#027A35', '#E5074C'],
                    borderColor: '#fff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }

    window.addEventListener('DOMContentLoaded', async () => {
        const anio = window.GRAFICAS_ANIO || new Date().getFullYear();
        const publicacionId = window.GRAFICAS_PUBLICACION || 0;
        try {
            const datos = await cargarDatos(anio, publicacionId);
            graficaPastel('grafPastel', datos.resumen || []);
            graficaEdificio('grafEdificio', datos.mensual || []);
            graficaAlcance('grafAlcance', datos.resumen || []);
            graficaSentimiento('grafSentimiento', datos.resumen || []);
            const metricasPublicacion = datos.publicacion || [];
            graficaPublicacion('grafPublicacion', metricasPublicacion);
            graficaReaccionesFacebook('grafReaccionesFacebook', metricasPublicacion);
            graficaSentimientoPublicacion('grafSentimientoPublicacion', metricasPublicacion);
        } catch (e) {
            console.error('Error al cargar gráficas:', e);
        }
    });
})();