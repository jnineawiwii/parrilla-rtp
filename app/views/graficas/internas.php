<?php
$titulo         = 'Gráficas Internas';
$cargarGraficas = false; 
$soloGraficas   = true;
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<!-- Cargamos html2canvas y jsPDF por separado para la descarga múltiple -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<div class="card">
    <div class="d-flex" style="justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
        <h2 style="margin:0; color:var(--vino);">📊 Análisis Interno</h2>
        <div class="d-flex gap-2 align-items-center">
            
            <button class="btn btn-primary" id="btn-descargar-todas" onclick="descargarTodasPDF()">
                ⬇️ Descargar todas
            </button>
            
            <form method="GET" action="/graficas/internas" class="d-flex gap-1 m-0">
                <select id="anioGrafica" name="anio" class="form-select" onchange="this.form.submit()">
                    <?php for ($y = (int)date('Y'); $y >= 2023; $y--): ?>
                        <option value="<?= $y ?>" <?= $y === (int)$anio ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
    </div>
</div>

<div class="card graph-filter-card">
    <form id="filtrosGraficasInternas" class="row" style="align-items:flex-end; gap:.5rem;">
        <div class="col" style="flex:1 1 140px;">
            <label class="form-label">Red</label>
            <select name="red" class="form-select">
                <option value="">Todas</option>
                <option value="facebook">Facebook</option>
                <option value="instagram">Instagram</option>
                <option value="x">Twitter / X</option>
                <option value="youtube">YouTube</option>
            </select>
        </div>
        <div class="col" style="flex:1 1 160px;">
            <label class="form-label">Periodo</label>
            <select name="periodo" class="form-select">
                <option value="anio">Año completo</option>
                <option value="mes">Mes actual</option>
                <option value="15d">Últimos 15 días</option>
            </select>
        </div>
        <div class="col" style="flex:1 1 140px;">
            <label class="form-label">Desde</label>
            <input type="date" name="desde" class="form-control">
        </div>
        <div class="col" style="flex:1 1 140px;">
            <label class="form-label">Hasta</label>
            <input type="date" name="hasta" class="form-control">
        </div>
        <div class="col" style="flex:0 0 auto;">
            <button type="submit" class="btn btn-primary">Aplicar</button>
        </div>
    </form>
</div>

<div class="graficas-grid" style="margin-top: 20px;">
    
    <!-- Gráfica 1: Responsables -->
    <div class="grafica-card seccion-pdf" id="card-responsables" style="background-color: var(--bg-card); padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: relative;">
        <div class="pdf-header" style="display: none; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
                <img src="/img/logo.png" alt="Logo RTP" style="height: 60px;">
                <span style="color: #666; font-size: 16px;">Fecha de reporte: <?= date('d/m/Y') ?></span>
            </div>
            <div style="height: 10px; background-color: #027A35; width: 100%; margin-bottom: 20px; border-radius: 4px;"></div>
            <h2 style="color: #333; margin: 0; font-size: 28px;">👥 Distribución de responsables</h2>
        </div>
        
        <div class="d-flex" style="justify-content:space-between; align-items:center;">
            <h3 class="titulo-grafica" style="color: var(--text-color); margin: 0;">👥 Distribución de responsables</h3>
            <button class="btn btn-sm btn-primary btn-pdf" onclick="descargarPDF('card-responsables', 'Responsables_Contenido')">⬇️ PDF</button>
        </div>
        <div class="canvas-container" style="position: relative; height: 350px; width: 100%; margin-top: 15px;">
            <canvas id="grafResponsables"></canvas>
        </div>
    </div>

    <!-- Gráfica 2: Tasa de Publicación -->
    <div class="grafica-card seccion-pdf" id="card-estados" style="background-color: var(--bg-card); padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: relative;">
        <div class="pdf-header" style="display: none; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
                <img src="/img/logo.png" alt="Logo RTP" style="height: 60px;">
                <span style="color: #666; font-size: 16px;">Fecha de reporte: <?= date('d/m/Y') ?></span>
            </div>
            <div style="height: 10px; background-color: #027A35; width: 100%; margin-bottom: 20px; border-radius: 4px;"></div>
            <h2 style="color: #333; margin: 0; font-size: 28px;">📅 Tasa de publicación (Estatus)</h2>
        </div>

        <div class="d-flex" style="justify-content:space-between; align-items:center;">
            <h3 class="titulo-grafica" style="color: var(--text-color); margin: 0;">📅 Tasa de publicación (Estatus)</h3>
            <button class="btn btn-sm btn-primary btn-pdf" onclick="descargarPDF('card-estados', 'Tasa_Publicacion')">⬇️ PDF</button>
        </div>
        <div class="canvas-container" style="position: relative; height: 350px; width: 100%; margin-top: 15px;">
            <canvas id="grafEstados"></canvas>
        </div>
    </div>

    <!-- Gráfica 3: Evolución de Seguidores -->
    <div class="grafica-card seccion-pdf" id="card-seguidores" style="grid-column: 1 / -1; background-color: var(--bg-card); padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: relative;">
        <div class="pdf-header" style="display: none; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
                <img src="/img/logo.png" alt="Logo RTP" style="height: 60px;">
                <span style="color: #666; font-size: 16px;">Fecha de reporte: <?= date('d/m/Y') ?></span>
            </div>
            <div style="height: 10px; background-color: #027A35; width: 100%; margin-bottom: 20px; border-radius: 4px;"></div>
            <h2 style="color: #333; margin: 0; font-size: 28px;">📈 Evolución de seguidores totales</h2>
        </div>

        <div class="d-flex" style="justify-content:space-between; align-items:center;">
            <h3 class="titulo-grafica" style="color: var(--text-color); margin: 0;">📈 Evolución de seguidores totales</h3>
            <button class="btn btn-sm btn-primary btn-pdf" onclick="descargarPDF('card-seguidores', 'Evolucion_Seguidores')">⬇️ PDF</button>
        </div>
        <div class="canvas-container" style="position: relative; height: 350px; width: 100%; margin-top: 15px;">
            <canvas id="grafSeguidores"></canvas>
        </div>
    </div>

    <!-- Gráfica 4: Engagement Rate -->
    <div class="grafica-card seccion-pdf" id="card-engagement" style="grid-column: 1 / -1; background-color: var(--bg-card); padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: relative;">
        <div class="pdf-header" style="display: none; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
                <img src="/img/logo.png" alt="Logo RTP" style="height: 60px;">
                <span style="color: #666; font-size: 16px;">Fecha de reporte: <?= date('d/m/Y') ?></span>
            </div>
            <div style="height: 10px; background-color: #027A35; width: 100%; margin-bottom: 20px; border-radius: 4px;"></div>
            <h2 style="color: #333; margin: 0; font-size: 28px;">🎯 Comparativa de Engagement Rate por red (%)</h2>
        </div>

        <div class="d-flex" style="justify-content:space-between; align-items:center;">
            <h3 class="titulo-grafica" style="color: var(--text-color); margin: 0;">🎯 Comparativa de Engagement Rate por red (%)</h3>
            <button class="btn btn-sm btn-primary btn-pdf" onclick="descargarPDF('card-engagement', 'Engagement_Rate')">⬇️ PDF</button>
        </div>
        <div class="canvas-container" style="position: relative; height: 350px; width: 100%; margin-top: 15px;">
            <canvas id="grafEngagement"></canvas>
        </div>
    </div>

</div>

<script>
    // CONFIGURACIÓN GENERAL
    const pdfOptions = {
        margin:       0,
        filename:     `Reporte_RTP_${new Date().toISOString().slice(0,10)}.pdf`,
        image:        { type: 'jpeg', quality: 1 },
        html2canvas:  { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
        jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' }
    };

    // 1. FUNCIÓN INDIVIDUAL
    function descargarPDF(idElemento, nombreArchivo) {
        const element = document.getElementById(idElemento);
        const btn = element.querySelector('.btn-pdf');
        const tituloPantalla = element.querySelector('.titulo-grafica');
        const headerPDF = element.querySelector('.pdf-header');
        const canvasContainer = element.querySelector('.canvas-container');

        if (btn) btn.style.display = 'none';
        if (tituloPantalla) tituloPantalla.style.display = 'none';
        if (headerPDF) headerPDF.style.display = 'block';
        
        element.style.padding = '40px 30px';
        element.style.boxShadow = 'none';
        element.style.border = 'none';
        if(canvasContainer) canvasContainer.style.height = '55vh';

        let opt = {...pdfOptions};
        opt.filename = `${nombreArchivo}_${new Date().toISOString().slice(0,10)}.pdf`;

        html2pdf().set(opt).from(element).save().then(() => {
            if (btn) btn.style.display = 'block';
            if (tituloPantalla) tituloPantalla.style.display = 'block';
            if (headerPDF) headerPDF.style.display = 'none';
            element.style.padding = '20px';
            element.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)';
            if(canvasContainer) canvasContainer.style.height = '350px';
        });
    }

    // 2. FUNCIÓN GLOBAL (CORREGIDA USANDO JSPDF Y HTML2CANVAS DIRECTAMENTE)
    async function descargarTodasPDF() {
        const tarjetas = document.querySelectorAll('.seccion-pdf');
        const btnGlobal = document.getElementById('btn-descargar-todas');
        
        btnGlobal.disabled = true;
        btnGlobal.innerHTML = '⏳ Generando PDF...';

        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('portrait', 'mm', 'letter');
        const anchoHoja = pdf.internal.pageSize.getWidth();
        const altoHoja = pdf.internal.pageSize.getHeight();

        for (let i = 0; i < tarjetas.length; i++) {
            const element = tarjetas[i];
            
            const btn = element.querySelector('.btn-pdf');
            const tituloPantalla = element.querySelector('.titulo-grafica');
            const headerPDF = element.querySelector('.pdf-header');
            const canvasContainer = element.querySelector('.canvas-container');

            // Preparar diseño
            if (btn) btn.style.display = 'none';
            if (tituloPantalla) tituloPantalla.style.display = 'none';
            if (headerPDF) headerPDF.style.display = 'block';
            element.style.padding = '40px 30px'; 
            element.style.boxShadow = 'none';
            element.style.border = 'none';
            element.style.height = '100vh'; 
            if(canvasContainer) canvasContainer.style.height = '55vh';

            // Capturar y agregar al PDF
            const canvas = await html2canvas(element, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
            const imgData = canvas.toDataURL('image/jpeg', 0.98);
            
            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = anchoHoja - 20; // Margen de 10mm
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
            
            if (i > 0) pdf.addPage();
            
            const yOffset = (altoHoja - pdfHeight) / 2; // Centrar vertical
            pdf.addImage(imgData, 'JPEG', 10, yOffset, pdfWidth, pdfHeight);

            // Restaurar diseño
            if (btn) btn.style.display = 'block';
            if (tituloPantalla) tituloPantalla.style.display = 'block';
            if (headerPDF) headerPDF.style.display = 'none';
            element.style.padding = '20px';
            element.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)';
            element.style.height = 'auto';
            if(canvasContainer) canvasContainer.style.height = '350px';
        }

        pdf.save(`Reporte_Completo_RTP_${new Date().toISOString().slice(0,10)}.pdf`);
        
        btnGlobal.disabled = false;
        btnGlobal.innerHTML = '⬇️ Descargar todas';
    }

    window.GRAFICAS_ANIO = <?= (int)$anio ?>;
</script>
<script src="/js/graficas_internas.js" defer></script>