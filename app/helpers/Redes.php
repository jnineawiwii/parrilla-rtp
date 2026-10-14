<?php
/**
 * Helper central de redes sociales: colores y nombres.
 */
class Redes {

    public const PALETA = [
        'facebook'  => '#266CB4', // azul
        'instagram' => '#D72F89', // lila / magenta
        'x'         => '#000000', // negro
        'youtube'   => '#FF0000', // rojo (naranja: #FF7A00)
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

    public static function todas(): array {
        return array_keys(self::PALETA);
    }
}