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

    async function cargarDatos(anio) {
        const r = await fetch('/api/graficas?anio=' + anio);
        return await r.json();
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
        try {
            const datos = await cargarDatos(anio);
            graficaPastel('grafPastel', datos.resumen || []);
            graficaEdificio('grafEdificio', datos.mensual || []);
            graficaAlcance('grafAlcance', datos.resumen || []);
            graficaSentimiento('grafSentimiento', datos.resumen || []);
        } catch (e) {
            console.error('Error al cargar gráficas:', e);
        }
    });
})();