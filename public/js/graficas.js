(function () {
    // ============================
    //  CONFIGURACIÓN DE REDES
    // ============================
    const CFG = {
        facebook:  { color: '#266CB4', nombre: 'Facebook'  },
        instagram: { color: '#D72F89', nombre: 'Instagram' },
        x:         { color: '#000000', nombre: 'X'         },
        youtube:   { color: '#FF0000', nombre: 'YouTube'   }, // ← naranja: #FF7A00
    };
    const REDES = Object.keys(CFG);
    const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

    // ============================
    //  UTILIDADES
    // ============================
    function sinDatos(canvas, msg) {
        canvas.parentElement
            .querySelectorAll('.chart-empty')
            .forEach(el => el.remove());
        canvas.parentElement.insertAdjacentHTML(
            'beforeend',
            `<p class="chart-empty text-muted" style="text-align:center;padding:1rem;">${msg}</p>`
        );
    }

    function limpiarMensajes() {
        document.querySelectorAll('.chart-empty').forEach(el => el.remove());
    }

    function destruir(id) {
        const c = document.getElementById(id);
        if (!c) return null;
        const prev = Chart.getChart(c);
        if (prev) prev.destroy();
        return c;
    }

    // ============================
    //  CARGA AJAX
    // ============================
    async function cargar(filtros) {
        const params = new URLSearchParams(filtros);
        const r = await fetch('/api/graficas?' + params.toString(), {
            headers: { 'Accept': 'application/json' },
        });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return await r.json();
    }

    // ============================
    //  1. PASTEL — distribución interacción
    // ============================
    function pintarPastel(resumen) {
        const ctx = destruir('grafPastel');
        if (!ctx) return;

        const totales = REDES.map(r => {
            const f = resumen.find(x => x.red === r);
            return f ? Number(f.interaccion || 0) : 0;
        });
        const suma = totales.reduce((a, b) => a + b, 0);
        if (!suma) return sinDatos(ctx, 'Sin datos en el periodo seleccionado.');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: REDES.map(r => CFG[r].nombre),
                datasets: [{
                    data: totales,
                    backgroundColor: REDES.map(r => CFG[r].color),
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
                            label: c => {
                                const p = ((c.parsed / suma) * 100).toFixed(1);
                                return `${c.label}: ${c.parsed.toLocaleString()} (${p}%)`;
                            },
                        },
                    },
                },
            },
        });
    }

    // ============================
    //  2. EDIFICIO — apilada por red
    // ============================
    function pintarEdificio(mensual, diaria, filtros) {
        const ctx = destruir('grafEdificio');
        if (!ctx) return;

        let labels, series = {};

        if (filtros.periodo === 'anio') {
            labels = MESES;
            REDES.forEach(r => series[r] = Array(12).fill(0));
            mensual.forEach(f => {
                const i = (Number(f.mes) || 0) - 1;
                if (series[f.red] && i >= 0 && i < 12) {
                    series[f.red][i] = Number(f.interaccion || 0);
                }
            });
        } else {
            const dias = Array.from(new Set(diaria.map(d => d.dia))).sort();
            labels = dias.map(d => d.slice(5)); // MM-DD
            REDES.forEach(r => series[r] = dias.map(() => 0));
            diaria.forEach(d => {
                const i = dias.indexOf(d.dia);
                if (series[d.red] && i >= 0) {
                    series[d.red][i] = Number(d.interaccion || 0);
                }
            });
        }

        const hayDatos = REDES.some(r => series[r].some(v => v > 0));
        if (!hayDatos) return sinDatos(ctx, 'Sin datos en el periodo seleccionado.');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: REDES.map(r => ({
                    label: CFG[r].nombre,
                    data: series[r],
                    backgroundColor: CFG[r].color,
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

    // ============================
    //  3. TOTALES POR RED (agrupadas)
    // ============================
    function pintarTotales(resumen) {
        const ctx = destruir('grafTotales');
        if (!ctx) return;

        const metricas = [
            ['Interacción', 'interaccion', '#a51d4b'],
            ['Reacciones',  'reacciones',  '#266CB4'],
            ['Me gusta',    'me_gusta',    '#D72F89'],
            ['Comentarios', 'comentarios', '#027A35'],
            ['Compartidos', 'compartidos', '#e2a521'],
            ['Vistas',      'vistas',      '#FF0000'],
        ];

        const presentes = metricas.filter(([, k]) =>
            resumen.some(f => Number(f[k] || 0) > 0)
        );

        const datasets = presentes.map(([lbl, key, color]) => ({
            label: lbl,
            data: REDES.map(r => {
                const f = resumen.find(x => x.red === r);
                return f ? Number(f[key] || 0) : 0;
            }),
            backgroundColor: color,
        }));

        if (!datasets.length || datasets.every(d => d.data.every(v => v === 0))) {
            return sinDatos(ctx, 'Sin datos en el periodo seleccionado.');
        }

        new Chart(ctx, {
            type: 'bar',
            data: { labels: REDES.map(r => CFG[r].nombre), datasets },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    // ============================
    //  4. TOP 10 PUBLICACIONES
    // ============================
    function pintarTop(top) {
        const ctx = destruir('grafTop');
        if (!ctx) return;
        if (!top.length) return sinDatos(ctx, 'Sin publicaciones en el periodo.');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: top.map(p => p.titulo.length > 40
                    ? p.titulo.slice(0, 37) + '…'
                    : p.titulo),
                datasets: [{
                    label: 'Interacción total',
                    data: top.map(p => Number(p.total || 0)),
                    backgroundColor: '#a51d4b',
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } },
            },
        });
    }

    // ============================
    //  5. CAMPAÑAS
    // ============================
    function pintarCampanas(campanas) {
        const ctx = destruir('grafCampanas');
        if (!ctx) return;
        if (!campanas.length) return sinDatos(ctx, 'Sin campañas en el periodo.');

        const colores = [
            '#a51d4b','#266CB4','#D72F89','#FF0000',
            '#027A35','#e2a521','#7b4ab5','#55585A',
        ];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: campanas.map(c => c.campana),
                datasets: [{
                    data: campanas.map(c => Number(c.publicaciones || 0)),
                    backgroundColor: campanas.map((_, i) => colores[i % colores.length]),
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
                            label: c => {
                                const total = c.dataset.data.reduce((a, b) => a + b, 0);
                                const p = total ? ((c.parsed / total) * 100).toFixed(1) : 0;
                                return `${c.label}: ${c.parsed} (${p}%)`;
                            },
                        },
                    },
                },
            },
        });
    }

    // ============================
    //  ACTUALIZAR TODO
    // ============================
    async function actualizar() {
        limpiarMensajes();

        const form = document.getElementById('filtrosGraficas');
        const data = Object.fromEntries(new FormData(form).entries());
        data.anio = window.GRAFICAS_ANIO || new Date().getFullYear();

        try {
            const r = await cargar(data);
            pintarPastel(r.resumen || []);
            pintarEdificio(r.mensual || [], r.diaria || [], r.filtros || {});
            pintarTotales(r.resumen || []);
            pintarTop(r.top || []);
            pintarCampanas(r.campanas || []);
        } catch (e) {
            console.error('Error al cargar gráficas:', e);
        }
    }

    // ============================
    //  INIT
    // ============================
    window.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('filtrosGraficas');
        if (!form) return;

        // Sincronizar inputs de fecha cuando se cambia el periodo
        const selPeriodo = form.querySelector('[name="periodo"]');
        const inDesde    = form.querySelector('[name="desde"]');
        const inHasta    = form.querySelector('[name="hasta"]');

        selPeriodo?.addEventListener('change', () => {
            const p = selPeriodo.value;
            const hoy = new Date().toISOString().slice(0, 10);
            const bloquear = (p === 'mes' || p === '15d' || p === 'anio');
            inDesde.disabled = bloquear;
            inHasta.disabled = bloquear;
            if (p === 'dia') inDesde.value = hoy;
        });
        selPeriodo?.dispatchEvent(new Event('change'));

        form.addEventListener('submit', e => {
            e.preventDefault();
            actualizar();
        });

        actualizar();
    });
})();