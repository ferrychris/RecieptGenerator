<?php

namespace App\Services\Rendering;

use App\Models\Invoice;
use App\Services\LayoutCatalog;
use App\ViewModels\InvoiceDocumentBuilder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

/**
 * Builds receipt PDFs and keeps a stored copy in the receipts bucket.
 */
class InvoicePdfFactory
{
    public function __construct(private ReceiptRenderer $renderer) {}

    /**
     * Returns the receipt's PDF bytes, reusing the stored copy when one
     * exists and rendering (then storing) it otherwise.
     *
     * Storage failures are logged but never fatal: rendering already
     * succeeded at that point, so refusing to serve the bytes would turn a
     * caching problem into a broken receipt. The next request simply tries
     * to store again.
     */
    public function fetchOrMake(Invoice $invoice, ?string $layoutKey = null): string
    {
        $disk = Storage::disk(config('receipts.storage_disk'));
        $path = $this->pathFor($invoice);

        try {
            if ($invoice->pdf_url === $path && $disk->exists($path)) {
                if (($cached = $disk->get($path)) !== null) {
                    return $cached;
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $pdf = $this->make($invoice, $layoutKey);

        try {
            if ($disk->put($path, $pdf)) {
                // Only record the path once the object is actually written,
                // so pdf_url never points at something that isn't there.
                $invoice->forceFill(['pdf_url' => $path])->saveQuietly();
            } else {
                report(new \RuntimeException("Could not store receipt PDF at [{$path}] on disk [".config('receipts.storage_disk').'].'));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $pdf;
    }

    public function pathFor(Invoice $invoice): string
    {
        return "receipts/{$invoice->business_id}/{$invoice->number}.pdf";
    }

    /** Renders fresh PDF bytes, without touching storage. */
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
