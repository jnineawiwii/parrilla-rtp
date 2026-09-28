<?php
class GraficaController {

    public function index(): void {
        Auth::require('metricas.ver');
        $anio = (int)($_GET['anio'] ?? date('Y'));
        Response::view('graficas/index', compact('anio'));
    }

    public function datos(): void {
        Auth::require('metricas.ver');
        $anio = (int)($_GET['anio'] ?? date('Y'));

        Response::json([
            'anio'    => $anio,
            'resumen' => Metrica::resumenPorRed($anio),
            'mensual' => Metrica::serieMensual($anio),
        ]);
    }
}