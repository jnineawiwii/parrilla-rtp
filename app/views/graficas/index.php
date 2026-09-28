<?php
$titulo = 'Gráficas';
$cargarGraficas = true;
?>

<div class="card">
    <div class="d-flex" style="justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
        <h2 style="margin:0; color:var(--vino);">📊 Estadísticas de redes sociales</h2>
        <form method="GET" action="/graficas" class="d-flex gap-1">
            <select name="anio" class="form-select" onchange="this.form.submit()">
                <?php for ($y = (int)date('Y'); $y >= 2023; $y--): ?>
                    <option value="<?= $y ?>" <?= $y === $anio ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
    <p class="text-muted" style="margin:.5rem 0 0; font-size:.9rem;">
        Año seleccionado: <strong><?= (int)$anio ?></strong>
    </p>
</div>

<div class="graficas-grid">
    <div class="grafica-card">
        <h3>🥧 Distribución de interacción (pastel)</h3>
        <canvas id="grafPastel"></canvas>
        <p class="text-muted" style="font-size:.8rem; text-align:center; margin-top:.5rem;">
            Porcentaje de interacción por red social.
        </p>
    </div>

    <div class="grafica-card">
        <h3>🏢 Interacción mensual por red (edificio)</h3>
        <canvas id="grafEdificio"></canvas>
        <p class="text-muted" style="font-size:.8rem; text-align:center; margin-top:.5rem;">
            Barras verticales apiladas — 12 meses del año.
        </p>
    </div>

    <div class="grafica-card">
        <h3>📈 Alcance por red (barras)</h3>
        <canvas id="grafAlcance"></canvas>
    </div>

    <div class="grafica-card">
        <h3>💬 Sentimiento de comentarios</h3>
        <canvas id="grafSentimiento"></canvas>
    </div>
</div>

<script>
    window.GRAFICAS_ANIO = <?= (int)$anio ?>;
</script>