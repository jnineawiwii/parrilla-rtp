<?php
class GraficaController {

    /** Página principal de gráficas. */
    public function index(): void {
        Auth::require('metricas.ver');

        $titulo       = 'Gráficas';
        $soloGraficas = true;
        $anio         = (int)($_GET['anio'] ?? date('Y'));
        $redes        = Redes::todas();

        // Métodos reales de tus modelos
        $temas    = Tema::listar();       // ✅ antes: Tema::todos()
        $campanas = Campana::listar();    // ✅ antes: Campana::todas()

        $cargarGraficas = true;

        Response::view('graficas/index',
            compact('anio', 'redes', 'temas', 'campanas', 'cargarGraficas', 'soloGraficas', 'titulo'));
    }

    /** Endpoint AJAX: /api/graficas */
    public function datos(): void {
        Auth::require('metricas.ver');

        $f = self::leerFiltros($_GET);

        Response::json([
            'filtros'  => $f,
            'resumen'  => Metrica::resumenPorRedFiltrado($f),
            'mensual'  => Metrica::serieMensualFiltrada($f),
            'diaria'   => Metrica::serieDiariaFiltrada($f),
            'top'      => Metrica::topPublicaciones($f, 10),
            'campanas' => Metrica::porCampana($f),
            'alcanceMes' => Metrica::alcanceMensualPorRed($f),
            'porFormato' => Metrica::rendimientoPorFormato($f),
            'heatmap'    => Metrica::heatmapMesCampana($f),
            'colores'  => Redes::PALETA,
            'nombres'  => Redes::NOMBRES,
        ]);
    }

    /** Normaliza los filtros del query string. */
    private static function leerFiltros(array $q): array {
        $periodo = $q['periodo'] ?? 'anio';
        $anio    = (int)($q['anio'] ?? date('Y'));
        $hoy     = new DateTime('today');

        $desde = null;
        $hasta = null;

        switch ($periodo) {
            case 'mes':
                $desde = (clone $hoy)->modify('first day of this month');
                $hasta = (clone $hoy)->modify('last day of this month');
                break;

            case '15d':
                $hasta = clone $hoy;
                $desde = (clone $hoy)->modify('-14 days');
                break;

            case 'dia':
                $dia   = $q['desde'] ?? $hoy->format('Y-m-d');
                $desde = new DateTime($dia);
                $hasta = clone $desde;
                break;

            case 'rango':
                if (!empty($q['desde'])) $desde = new DateTime($q['desde']);
                if (!empty($q['hasta'])) $hasta = new DateTime($q['hasta']);
                break;

            case 'anio':
            default:
                $desde = new DateTime("$anio-01-01");
                $hasta = new DateTime("$anio-12-31");
        }

        return [
            'periodo' => $periodo,
            'anio'    => $anio,
            'red'     => $q['red']     ?? '',
            'tema_id' => $q['tema_id'] ?? '',
            'desde'   => $desde?->format('Y-m-d'),
            'hasta'   => $hasta?->format('Y-m-d'),
        ];
    }
}