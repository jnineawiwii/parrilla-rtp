<?php
class GraficaController {

    public function index(): void {
        header('Location: /graficas/externas');
        exit;
    }

    public function externas(): void {
        Auth::require('metricas.ver');
        $titulo       = 'Gráficas Externas';
        $soloGraficas = true;
        $anio         = (int)($_GET['anio'] ?? date('Y'));
        $redes        = Redes::todas();
        $temas        = Tema::listar();       
        $campanas     = Campana::listar();    
        $cargarGraficas = true;

        Response::view('graficas/externas',
            compact('anio', 'redes', 'temas', 'campanas', 'cargarGraficas', 'soloGraficas', 'titulo'));
    }

    public function internas(): void {
        Auth::require('metricas.ver');
        $titulo       = 'Gráficas Internas';
        $soloGraficas = true;
        $anio         = (int)($_GET['anio'] ?? date('Y'));
        Response::view('graficas/internas', compact('anio', 'titulo', 'soloGraficas'));
    }

    public function apiExternas(): void {
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

    public function apiInternas(): void {
        Auth::require('metricas.ver');
        $db = db(); 
        
        // 1. RESPONSABLES
        try {
            $stmt = $db->prepare("SELECT COALESCE(u.nombre_completo, 'Sin asignar') AS nombre, COUNT(p.id_publicacion)::int AS total FROM gestion_publicaciones p LEFT JOIN gestion_usuarios u ON p.id_usuario_responsable = u.id_usuario GROUP BY u.nombre_completo ORDER BY total DESC");
            $stmt->execute();
            $responsables = $stmt->fetchAll();
            if(!$responsables) $responsables = [['nombre'=>'Sin datos', 'total'=>0]];
        } catch(Exception $e) {
            $responsables = [['nombre'=>'Error SQL', 'total'=>1]];
        }

        // 2. ESTADOS
        try {
            $stmt = $db->prepare("SELECT estado AS nombre, COUNT(id_publicacion)::int AS total FROM gestion_publicaciones GROUP BY estado ORDER BY total DESC");
            $stmt->execute();
            $estados = $stmt->fetchAll();
            if(!$estados) $estados = [['nombre'=>'Sin datos', 'total'=>0]];
        } catch(Exception $e) {
            $estados = [['nombre'=>'Error SQL', 'total'=>1]];
        }

        // 3. SEGUIDORES (Datos manuales)
        $seguidores = [
            ['mes' => '01', 'total' => 102823], ['mes' => '02', 'total' => 105491],
            ['mes' => '03', 'total' => 109343], ['mes' => '04', 'total' => 111294],
            ['mes' => '05', 'total' => 113961], ['mes' => '06', 'total' => 119074],
            ['mes' => '07', 'total' => 124036], ['mes' => '08', 'total' => 127842],
            ['mes' => '09', 'total' => 131692], ['mes' => '10', 'total' => 134561],
            ['mes' => '11', 'total' => 139611], ['mes' => '12', 'total' => 142308]
        ];

        // 4. ENGAGEMENT RATE (Datos manuales)
        $engagement = [
            ['nombre' => 'Instagram', 'total' => 12.02],
            ['nombre' => 'Facebook', 'total' => 2.82],
            ['nombre' => 'Twitter', 'total' => 1.50]
        ];

        Response::json([
            'responsables' => $responsables,
            'estados'      => $estados,
            'seguidores'   => $seguidores,
            'engagement'   => $engagement
        ]);
    }

    private static function leerFiltros(array $q): array {
        $periodo = $q['periodo'] ?? 'anio';
        $anio    = (int)($q['anio'] ?? date('Y'));
        $hoy     = new DateTime('today');
        $desde = null; $hasta = null;

        switch ($periodo) {
            case 'mes': $desde = (clone $hoy)->modify('first day of this month'); $hasta = (clone $hoy)->modify('last day of this month'); break;
            case '15d': $hasta = clone $hoy; $desde = (clone $hoy)->modify('-14 days'); break;
            case 'dia': $dia = $q['desde'] ?? $hoy->format('Y-m-d'); $desde = new DateTime($dia); $hasta = clone $desde; break;
            case 'rango':
                if (!empty($q['desde'])) $desde = new DateTime($q['desde']);
                if (!empty($q['hasta'])) $hasta = new DateTime($q['hasta']);
                break;
            case 'anio':
            default: $desde = new DateTime("$anio-01-01"); $hasta = new DateTime("$anio-12-31");
        }

        return [
            'periodo' => $periodo, 'anio' => $anio, 'red' => $q['red'] ?? '',
            'tema_id' => $q['tema_id'] ?? '', 'desde' => $desde?->format('Y-m-d'), 'hasta' => $hasta?->format('Y-m-d'),
        ];
    }
}