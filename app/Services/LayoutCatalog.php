<?php

namespace App\Services;

class LayoutCatalog
{
    private const LAYOUTS = [
        'ledger' => ['name' => 'Ledger', 'page' => 'A4'],
        'bold' => ['name' => 'Bold', 'page' => 'A4'],
        'swiss' => ['name' => 'Swiss', 'page' => 'A4'],
        'thermal' => ['name' => 'Thermal', 'page' => '80mm'],
        'friendly' => ['name' => 'Friendly', 'page' => 'A4'],
        'sidebar' => ['name' => 'Sidebar', 'page' => 'A4'],
        'premium' => ['name' => 'Premium', 'page' => 'A4'],
    ];

    public static function all(): array
    {
        return self::LAYOUTS;
    }

    public static function exists(string $key): bool
    {
        return isset(self::LAYOUTS[$key]);
    }

    public static function page(string $key): string
    {
        return self::LAYOUTS[$key]['page'] ?? 'A4';
    }
}
