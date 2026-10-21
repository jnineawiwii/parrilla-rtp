// Registramos el plugin de etiquetas (porcentajes) a nivel global
Chart.register(ChartDataLabels);

document.addEventListener("DOMContentLoaded", function() {
    const canvasResp = document.getElementById('grafResponsables');
    const canvasEstados = document.getElementById('grafEstados');
    const canvasSeguidores = document.getElementById('grafSeguidores');
    const canvasEngagement = document.getElementById('grafEngagement');
    
    async function cargarGraficasInternas() {
        try {
            const res = await fetch('/api/graficas/internas');
            const data = await res.json();
            
            // 1. RESPONSABLES (CON PORCENTAJES)
            if (canvasResp && data.responsables && data.responsables.length > 0) {
                const prev = Chart.getChart(canvasResp);
                if (prev) prev.destroy();
                
                const coloresResp = ['#a51d4b', '#266CB4', '#D72F89', '#FF0000', '#027A35', '#e2a521', '#7b4ab5', '#55585A'];
                new Chart(canvasResp.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: data.responsables.map(item => item.nombre),
                        datasets: [{ 
                            data: data.responsables.map(item => item.total), 
                            backgroundColor: coloresResp, 
                            borderColor: '#ffffff', 
                            borderWidth: 2 
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { 
                            legend: { position: 'right', labels: { color: '#333333' } },
                            // AQUÍ AGREGAMOS LOS PORCENTAJES
                            datalabels: {
                                color: '#ffffff',
                                font: { weight: 'bold', size: 13 },
                                formatter: (value, ctx) => {
                                    let sum = 0;
                                    let dataArr = ctx.chart.data.datasets[0].data;
                                    dataArr.map(d => { sum += d; });
                                    let percentage = (value * 100 / sum).toFixed(1) + "%";
                                    // Solo mostramos si el porcentaje es mayor al 4% para no encimar textos
                                    return (value * 100 / sum) > 4 ? percentage : '';
                                }
                            }
                        } 
                    },
                    // Registramos el plugin de etiquetas
                    plugins: [ChartDataLabels]
                });
            }

            // 2. ESTADOS
            if (canvasEstados && data.estados && data.estados.length > 0) {
                const prevEst = Chart.getChart(canvasEstados);
                if (prevEst) prevEst.destroy();
                
                const colorMap = { 'Publicado': '#027A35', 'Aprobado': '#266CB4', 'Cancelado': '#FF0000', 'Reprogramar': '#e2a521', 'Borrador': '#7b4ab5', 'Programado': '#00bcd4', 'No publicado': '#55585A' };
                const bgColors = data.estados.map(item => colorMap[item.nombre] || '#55585A');
                
                new Chart(canvasEstados.getContext('2d'), {
                    type: 'bar',
                    data: { labels: data.estados.map(item => item.nombre), datasets: [{ label: 'Cantidad', data: data.estados.map(item => item.total), backgroundColor: bgColors, borderRadius: 5 }] },
                    options: { 
                        responsive: true, maintainAspectRatio: false, 
                        plugins: { 
                            legend: { display: false },
                            datalabels: { display: false } // Ocultamos porcentajes en esta
                        }, 
                        scales: { 
                            y: { beginAtZero: true, ticks: { color: '#333333' }, grid: { color: '#e0e0e0' } }, 
                            x: { ticks: { color: '#333333' }, grid: { display: false } } 
                        } 
                    }
                });
            }

            // 3. SEGUIDORES
            if (canvasSeguidores && data.seguidores && data.seguidores.length > 0) {
                const prevSeg = Chart.getChart(canvasSeguidores);
                if (prevSeg) prevSeg.destroy();
                
                const getMesNombre = (num) => { const m = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic']; return m[parseInt(num) - 1] || num; };
                const ctxSeg = canvasSeguidores.getContext('2d');
                const gradient = ctxSeg.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(233, 30, 99, 0.3)');
                gradient.addColorStop(1, 'rgba(233, 30, 99, 0.0)');
                
                new Chart(ctxSeg, {
                    type: 'line',
                    data: {
                        labels: data.seguidores.map(item => getMesNombre(item.mes)),
                        datasets: [{ label: 'Total de seguidores', data: data.seguidores.map(item => item.total), borderColor: '#e91e63', backgroundColor: gradient, borderWidth: 3, tension: 0.4, fill: true, pointBackgroundColor: '#fff', pointBorderColor: '#e91e63', pointRadius: 5 }]
                    },
                    options: { 
                        responsive: true, maintainAspectRatio: false, 
                        plugins: { 
                            legend: { display: false },
                            datalabels: { display: false } // Ocultamos porcentajes
                        }, 
                        scales: { 
                            y: { beginAtZero: false, ticks: { color: '#333333', callback: function(value) { return value.toLocaleString('en-US'); } }, grid: { color: '#e0e0e0' } }, 
                            x: { ticks: { color: '#333333' }, grid: { display: false } } 
                        } 
                    }
                });
            }

            // 4. ENGAGEMENT RATE
            if (canvasEngagement && data.engagement && data.engagement.length > 0) {
                const prevEng = Chart.getChart(canvasEngagement);
                if (prevEng) prevEng.destroy();

                const colorRed = { 'Facebook': '#266CB4', 'facebook': '#266CB4', 'Instagram': '#D72F89', 'instagram': '#D72F89', 'Twitter': '#000000', 'twitter': '#000000', 'X': '#000000', 'x': '#000000', 'YouTube': '#FF0000', 'youtube': '#FF0000' };
                const bgColorsEng = data.engagement.map(item => colorRed[item.nombre] || '#55585A');

                new Chart(canvasEngagement.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.engagement.map(item => item.nombre),
                        datasets: [{ label: 'Engagement Rate (%)', data: data.engagement.map(item => parseFloat(item.total).toFixed(2)), backgroundColor: bgColorsEng, borderRadius: 5 }]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false }, 
                            tooltip: { callbacks: { label: function(context) { return context.parsed.x + ' %'; } } },
                            datalabels: { display: false } // Ocultamos los porcentajes para que no se dupliquen
                        },
                        scales: { 
                            x: { beginAtZero: true, ticks: { color: '#333333', callback: function(value) { return value + '%'; } }, grid: { color: '#e0e0e0' } }, 
                            y: { ticks: { color: '#333333', font: { size: 14, weight: 'bold' } }, grid: { display: false } } 
                        }
                    }
                });
            }

        } catch (error) {
            console.error("Error al cargar gráficas internas:", error);
        }
    }

    setTimeout(cargarGraficasInternas, 100);
});