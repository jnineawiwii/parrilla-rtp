<?php
class GraficaController {

    public function index(): void {
        Auth::require('metricas.ver');
        $anio = (int)($_GET['anio'] ?? date('Y'));
        $publicaciones = Metrica::publicacionesConMetricas($anio);
        $publicacionId = max(0, (int)($_GET['publicacion'] ?? 0));
        if ($publicacionId === 0 && !empty($publicaciones)) {
            $publicacionId = (int)$publicaciones[0]['id_publicacion'];
        }
        $cargarGraficas = true;
        Response::view('graficas/index', compact('anio', 'publicaciones', 'publicacionId', 'cargarGraficas'));
    }

    public function datos(): void {
        Auth::require('metricas.ver');
        $anio = (int)($_GET['anio'] ?? date('Y'));
        $publicacionId = max(0, (int)($_GET['publicacion'] ?? 0));

        Response::json([
            'anio'    => $anio,
            'resumen' => Metrica::resumenPorRed($anio),
            'mensual' => Metrica::serieMensual($anio),
            'publicacion' => $publicacionId > 0 ? Metrica::detallePublicacion($publicacionId) : [],
        ]);
    }
}