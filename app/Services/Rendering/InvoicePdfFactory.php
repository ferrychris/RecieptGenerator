<?php

namespace App\Services\Rendering;

use App\Models\Invoice;
use App\Services\LayoutCatalog;
use App\ViewModels\InvoiceDocumentBuilder;
use Illuminate\Support\Facades\Blade;

/**
 * Turns an Invoice into PDF bytes.
 *
 * Deliberately returns the bytes rather than writing them anywhere: nothing
 * in the app reads a stored PDF (the old `invoices.pdf_url` was written and
 * then immediately read back by the same request, and cleared on every
 * subsequent one, so it cached nothing). Keeping storage out of this path
 * means preview/download work even when object storage is unavailable or
 * misconfigured.
 */
class InvoicePdfFactory
{
    public function __construct(private ReceiptRenderer $renderer) {}

    public function make(Invoice $invoice, ?string $layoutKey = null): string
    {
        $layoutKey = $layoutKey ?: ($invoice->template ?? 'ledger');

        if (! LayoutCatalog::exists($layoutKey)) {
            $layoutKey = 'ledger';
        }

        $document = InvoiceDocumentBuilder::build($invoice, $layoutKey);
        $html = Blade::render("pdf.layouts.{$layoutKey}", $document);

        return $this->renderer->render($html, $document['meta']);
    }
}
