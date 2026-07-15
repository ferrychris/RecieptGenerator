<?php

namespace App\Services;

class FontStacks
{
    private const STACKS = [
        'libre-baskerville' => "'Libre Baskerville', Georgia, 'Times New Roman', serif",
        'lato' => "'Lato', Helvetica, Arial, sans-serif",
        'archivo-black' => "'Archivo Black', Arial, sans-serif",
        'inter' => "'Inter', Helvetica, Arial, sans-serif",
        'helvetica' => "Helvetica, Arial, sans-serif",
        'ibm-plex-mono' => "'IBM Plex Mono', 'Courier New', monospace",
        'nunito' => "'Nunito', 'Segoe UI', Arial, sans-serif",
        'fraunces' => "'Fraunces', Georgia, 'Times New Roman', serif",
        'public-sans' => "'Public Sans', Helvetica, Arial, sans-serif",
    ];

    public static function get(string $key): string
    {
        return self::STACKS[$key] ?? self::STACKS['helvetica'];
    }
}
