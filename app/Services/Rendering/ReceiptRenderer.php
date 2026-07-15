<?php

namespace App\Services\Rendering;

interface ReceiptRenderer
{
    /**
     * Render HTML to PDF bytes.
     */
    public function render(string $html, array $meta = []): string;
}
