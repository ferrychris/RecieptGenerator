<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\LayoutCatalog;
use App\Services\Rendering\ReceiptRenderer;
use App\ViewModels\InvoiceDocumentBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

class RenderInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $layoutKey = 'ledger',
    ) {
        if (! LayoutCatalog::exists($this->layoutKey)) {
            $this->layoutKey = 'ledger';
        }
    }

    public function handle(ReceiptRenderer $renderer): void
    {
        $document = InvoiceDocumentBuilder::build($this->invoice, $this->layoutKey);
        $html = Blade::render("pdf.layouts.{$this->layoutKey}", $document);
        $pdf = $renderer->render($html, $document['meta']);

        $path = "receipts/{$this->invoice->business_id}/{$this->invoice->id}.pdf";
        Storage::disk(config('receipts.storage_disk'))->put($path, $pdf);
        $this->invoice->update(['pdf_url' => $path]);
    }
}
