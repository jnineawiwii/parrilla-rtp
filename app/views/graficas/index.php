<?php
$titulo         = 'Gráficas';
$cargarGraficas = true;
$soloGraficas   = true;
?>

<div class="card">
    <div class="d-flex" style="justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
        <h2 style="margin:0; color:var(--vino);">📊 Estadísticas de redes sociales</h2>
        <form method="GET" action="/graficas" class="d-flex gap-1">
            <label class="sr-only" for="anioGrafica">Año</label>
            <select id="anioGrafica" name="anio" class="form-select" onchange="this.form.submit()">
                <?php for ($y = (int)date('Y'); $y >= 2023; $y--): ?>
                    <option value="<?= $y ?>" <?= $y === (int)$anio ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
</div>

<!-- FILTROS -->
<div class="card graph-filter-card">
    <form id="filtrosGraficas" class="row" style="align-items:flex-end; gap:.5rem;">
        <div class="col" style="flex:1 1 140px;">
            <label class="form-label" for="fRed">Red</label>
            <select id="fRed" name="red" class="form-select">
                <option value="">Todas</option>
                <?php foreach ($redes as $r): ?>
                    <option value="<?= htmlspecialchars($r) ?>"><?= Redes::nombre($r) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col" style="flex:1 1 160px;">
            <label class="form-label" for="fPeriodo">Periodo</label>
            <select id="fPeriodo" name="periodo" class="form-select">
                <option value="anio">Año completo</option>
                <option value="mes">Mes actual</option>
                <option value="15d">Últimos 15 días</option>
                <option value="dia">Día específico</option>
                <option value="rango">Rango</option>
            </select>
        </div>

        <div class="col" style="flex:1 1 140px;">
            <label class="form-label" for="fDesde">Desde</label>
            <input type="date" id="fDesde" name="desde" class="form-control">
        </div>

        <div class="col" style="flex:1 1 140px;">
            <label class="form-label" for="fHasta">Hasta</label>
            <input type="date" id="fHasta" name="hasta" class="form-control">
        </div>

        <div class="col" style="flex:1 1 180px;">
            <label class="form-label" for="fTema">Tema</label>
            <select id="fTema" name="tema_id" class="form-select">
                <option value="">Todos</option>
                <?php foreach (($temas ?? []) as $t): ?>
                    <option value="<?= (int)$t['id_tema'] ?>">
                        <?= htmlspecialchars($t['nombre_tema']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col" style="flex:0 0 auto;">
            <button type="submit" class="btn btn-primary">Aplicar</button>
        </div>
    </form>
</div>

<!-- GRÁFICAS -->
<div class="graficas-grid">

    <div class="grafica-card">
        <h3>🥧 Distribución de interacción por red</h3>
        <canvas id="grafPastel"></canvas>
    </div>

    <div class="grafica-card">
        <h3>🏢 Interacción por periodo (apilada por red)</h3>
        <canvas id="grafEdificio"></canvas>
        <p class="text-muted" style="font-size:.8rem; text-align:center; margin-top:.5rem;">
            Año → 12 meses · Otros periodos → días.
        </p>
    </div>

    <div class="grafica-card">
        <h3>📈 Totales por red</h3>
        <canvas id="grafTotales"></canvas>
    </div>

    <div class="grafica-card">
        <h3>🏆 Top 10 publicaciones por interacción</h3>
        <canvas id="grafTop"></canvas>
    </div>

    <div class="grafica-card">
        <h3>📣 Distribución por campaña</h3>
        <canvas id="grafCampanas"></canvas>
    </div>

    <div class="grafica-card" style="grid-column:1 / -1;">
        <h3>📈 Evolución de alcance mensual por red</h3>
        <canvas id="grafAlcanceMes"></canvas>
        <p class="text-muted" style="font-size:.8rem; text-align:center; margin-top:.5rem;">
            Interacción + reacciones + comentarios + compartidos + vistas, por mes.
        </p>
    </div>

    <div class="grafica-card" style="grid-column:1 / -1;">
        <h3>🎬 Rendimiento por formato multimedia</h3>
        <canvas id="grafFormato"></canvas>
        <p class="text-muted" style="font-size:.8rem; text-align:center; margin-top:.5rem;">
            Interacciones totales por tipo de formato.
        </p>
    </div>

    <div class="grafica-card" style="grid-column:1 / -1;">
        <h3>🔥 Mapa de calor: publicaciones por mes × campaña</h3>
        <div id="heatmapContainer" style="overflow-x:auto;"></div>
        <p class="text-muted" style="font-size:.8rem; text-align:center; margin-top:.5rem;">
            Intensidad = número de publicaciones.
        </p>
    </div>

</div>

<script>
    window.GRAFICAS_ANIO = <?= (int)$anio ?>;
</script>