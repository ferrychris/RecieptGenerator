<?php

namespace App\Services;

class DefaultTheme
{
    public static function forLayout(string $layoutKey): array
    {
        $themes = [
            'ledger' => [
                'accent' => '#1d2d44',
                'accent_ink' => '#ffffff',
                'text' => '#22201c',
                'muted' => '#6f6a5f',
                'rule' => '#c9c2b4',
                'font_display' => 'libre-baskerville',
                'font_body' => 'lato',
                'density' => 'normal',
                'radius' => 0,
                'logo_mode' => 'image',
                'number_style' => 'comma',
                'footer_text' => 'Thank you for your business.',
            ],
            'bold' => [
                'accent' => '#111111',
                'accent_ink' => '#ffffff',
                'text' => '#111111',
                'muted' => '#6b6b6b',
                'rule' => '#e5e5e5',
                'font_display' => 'archivo-black',
                'font_body' => 'inter',
                'density' => 'normal',
                'radius' => 0,
                'logo_mode' => 'image',
                'number_style' => 'comma',
                'footer_text' => 'Thanks for the business.',
            ],
            'swiss' => [
                'accent' => '#e4002b',
                'accent_ink' => '#ffffff',
                'text' => '#1a1a1a',
                'muted' => '#8a8a8a',
                'rule' => '#dedede',
                'font_display' => 'helvetica',
                'font_body' => 'helvetica',
                'density' => 'compact',
                'radius' => 0,
                'logo_mode' => 'image',
                'number_style' => 'thin-space',
                'footer_text' => '',
            ],
            'thermal' => [
                'accent' => '#000000',
                'accent_ink' => '#ffffff',
                'text' => '#000000',
                'muted' => '#444444',
                'rule' => '#000000',
                'font_display' => 'ibm-plex-mono',
                'font_body' => 'ibm-plex-mono',
                'density' => 'compact',
                'radius' => 0,
                'logo_mode' => 'image',
                'number_style' => 'comma',
                'footer_text' => 'Thank you!',
            ],
            'friendly' => [
                'accent' => '#f97362',
                'accent_ink' => '#ffffff',
                'text' => '#3a2e2a',
                'muted' => '#9b8c85',
                'rule' => '#f0e4e0',
                'font_display' => 'nunito',
                'font_body' => 'nunito',
                'density' => 'relaxed',
                'radius' => 16,
                'logo_mode' => 'image',
                'number_style' => 'comma',
                'footer_text' => 'Thanks so much for your business — see you again soon!',
            ],
            'sidebar' => [
                'accent' => '#2f3e46',
                'accent_ink' => '#ffffff',
                'text' => '#22201c',
                'muted' => '#7a7a7a',
                'rule' => '#e3e3e3',
                'font_display' => 'fraunces',
                'font_body' => 'public-sans',
                'density' => 'normal',
                'radius' => 0,
                'logo_mode' => 'image',
                'number_style' => 'comma',
                'footer_text' => 'Thank you for your business.',
            ],
            'premium' => [
                'accent' => '#7a1620',
                'accent_ink' => '#ffffff',
                'text' => '#1c140a',
                'muted' => '#8a7a5c',
                'rule' => '#c9a227',
                'page_bg' => '#f6ecd9',
                'font_display' => 'archivo-black',
                'font_body' => 'public-sans',
                'density' => 'normal',
                'radius' => 0,
                'logo_mode' => 'image',
                'number_style' => 'comma',
                'footer_text' => 'Thank You for Your Payment!',
            ],
        ];

        return $themes[$layoutKey] ?? $themes['ledger'];
    }

    public static function defaultOptions(): array
    {
        return [
            'show_tax_column' => true,
            'show_discount_row' => false,
            'show_payment_details' => false,
            'show_notes' => true,
            'show_qr' => false,
            'item_details_line' => false,
            'labels_locale' => 'en',
            'date_format' => 'M j, Y',
        ];
    }
}
