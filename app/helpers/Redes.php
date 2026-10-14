<?php
/**
 * Helper central de redes sociales: colores y nombres.
 * Un solo lugar para cambiar la paleta.
 */
class Redes {

    /** Colores oficiales por red (hex). */
    public const PALETA = [
        'facebook'  => '#266CB4', // azul
        'instagram' => '#D72F89', // lila / magenta
        'x'         => '#000000', // negro
        'youtube'   => '#FF0000', // rojo  (cámbialo a #FF7A00 si prefieres naranja)
    ];

    public const NOMBRES = [
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'x'         => 'X',
        'youtube'   => 'YouTube',
    ];

    public static function color(string $red): string {
        return self::PALETA[strtolower($red)] ?? '#888888';
    }

    public static function nombre(string $red): string {
        return self::NOMBRES[strtolower($red)] ?? ucfirst($red);
    }

    /** Lista de redes válidas. */
    public static function todas(): array {
        return array_keys(self::PALETA);
    }
}