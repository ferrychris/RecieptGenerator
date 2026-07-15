<?php

namespace App\Http\Controllers;

use App\Jobs\RenderInvoicePdfJob;
use App\Models\Invoice;
use App\Services\LayoutCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = $request->user()->business->invoices()->with('customer:id,name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest('issue_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Invoices/Create', [
            'customers' => $request->user()->business->customers()->orderBy('name')->get(['id', 'name']),
            'defaultCurrency' => $request->user()->business->default_currency,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $business = $request->user()->business;

        $invoice = DB::transaction(function () use ($data, $business) {
            $initialStatus = !empty($data['paid_in_full']) ? 'paid' : ($data['status'] ?? 'unpaid');
            $amountPaid = $initialStatus === 'paid' ? 0 : ($data['amount_paid'] ?? 0);

            $invoice = $business->invoices()->create([
                'customer_id' => $data['customer_id'],
                'number' => $business->nextInvoiceNumber(),
                'currency' => $data['currency'],
                'template' => $business->template,
                'status' => $initialStatus,
                'amount_paid' => $amountPaid,
                'completed_at' => $initialStatus === 'paid' ? now() : null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['issue_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $position => $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'line_total' => round($item['qty'] * $item['unit_price'], 2),
                    'position' => $position,
                ]);
            }

            $invoice->load('items');
            $invoice->recalculateTotals();

            if ($initialStatus === 'paid') {
                $invoice->update(['amount_paid' => $invoice->total]);
            }

            $invoice->transactions()->create([
                'old_status' => null,
                'new_status' => $initialStatus,
                'amount' => $invoice->amount_paid,
                'note' => 'Invoice created',
            ]);

            return $invoice;
        });

        return redirect()->route('invoices.edit', $invoice)->with('success', 'Receipt created successfully.');
    }

    public function edit(Invoice $invoice): Response|\Illuminate\Http\RedirectResponse
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.index')->with('error', 'Paid receipts cannot be edited.');
        }

        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice->load('items', 'customer:id,name', 'transactions'),
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.index')->with('error', 'Paid receipts cannot be edited.');
        }

        $data = $this->validated($request);

        DB::transaction(function () use ($data, $invoice) {
            $oldStatus = $invoice->status;
            $oldAmountPaid = $invoice->amount_paid;
            
            $newStatus = !empty($data['paid_in_full']) ? 'paid' : ($data['status'] ?? $oldStatus);
            $newAmountPaid = $data['amount_paid'] ?? $oldAmountPaid;

            $completedAt = $invoice->completed_at;
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                $completedAt = now();
            } elseif ($newStatus !== 'paid') {
                $completedAt = null;
            }

            $invoice->update([
                'customer_id' => $data['customer_id'],
                'currency' => $data['currency'],
                'status' => $newStatus,
                'amount_paid' => $newAmountPaid,
                'completed_at' => $completedAt,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['issue_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->items()->delete();

            foreach ($data['items'] as $position => $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'line_total' => round($item['qty'] * $item['unit_price'], 2),
                    'position' => $position,
                ]);
            }

            $invoice->load('items');
            $invoice->recalculateTotals();

            if ($newStatus === 'paid' && $invoice->amount_paid != $invoice->total) {
                $invoice->update(['amount_paid' => $invoice->total]);
                $newAmountPaid = $invoice->total;
            }

            if ($newStatus !== $oldStatus || $newAmountPaid != $oldAmountPaid) {
                $invoice->transactions()->create([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'amount' => $newAmountPaid,
                    'note' => ($newStatus !== $oldStatus) ? 'Status updated manually' : 'Amount paid updated',
                ]);
            }
        });

        return redirect()->route('invoices.edit', $invoice)->with('success', 'Receipt updated successfully.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.index')->with('error', 'Paid receipts cannot be deleted.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Receipt deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', Rule::exists('invoices', 'id')->where('business_id', $request->user()->business_id)],
        ]);

        $hasPaid = Invoice::whereIn('id', $request->ids)->where('status', 'paid')->exists();
        if ($hasPaid) {
            return redirect()->route('invoices.index')->with('error', 'Paid receipts cannot be deleted.');
        }

        Invoice::whereIn('id', $request->ids)->delete();

        return redirect()->route('invoices.index')->with('success', count($request->ids) . ' receipts deleted successfully.');
    }

    public function exportTransactions(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $query = $request->user()->business->invoices()
            ->with(['customer:id,name', 'transactions'])
            ->whereHas('transactions');

        if ($request->filled('from')) {
            $query->whereDate('issue_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('issue_date', '<=', $request->to);
        }

        $invoices = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="transactions-export.csv"',
        ];

        $callback = function () use ($invoices): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Receipt Number', 'Customer', 'Date', 'Status', 'Amount', 'Note']);

            foreach ($invoices as $invoice) {
                foreach ($invoice->transactions as $transaction) {
                    fputcsv($handle, [
                        $invoice->number,
                        $invoice->customer?->name ?? '—',
                        $invoice->issue_date?->format('Y-m-d') ?? '',
                        $transaction->new_status,
                        $transaction->amount,
                        $transaction->note ?? '',
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadPdf(Request $request, Invoice $invoice): StreamedResponse
    {
        $path = $this->renderPdfAndWait($request, $invoice);

        return Storage::disk(config('receipts.storage_disk'))->download($path, "{$invoice->number}.pdf");
    }

    public function previewPdf(Request $request, Invoice $invoice): StreamedResponse
    {
        $path = $this->renderPdfAndWait($request, $invoice);

        return Storage::disk(config('receipts.storage_disk'))->response($path, "{$invoice->number}.pdf", [
            'Content-Disposition' => "inline; filename=\"{$invoice->number}.pdf\"",
        ]);
    }

    private function renderPdfAndWait(Request $request, Invoice $invoice): string
    {
        // Rendering shells out to headless Chrome, which is dispatched to the
        // queue worker rather than run inline: PHP's built-in dev server
        // (`artisan serve`) cannot reliably spawn the Chrome subprocess
        // itself, and production is expected to run a persistent queue
        // worker anyway (see plan.md's rendering architecture).
        $layoutKey = $request->query('layout', $invoice->template ?? 'ledger');

        $invoice->update(['pdf_url' => null]);
        RenderInvoicePdfJob::dispatch($invoice, $layoutKey);

        $deadline = microtime(true) + 15;
        while (microtime(true) < $deadline) {
            usleep(250_000);
            $invoice->refresh();
            if ($invoice->pdf_url) {
                break;
            }
        }

        abort_if(! $invoice->pdf_url, 504, 'The receipt is taking longer than expected to generate. Please try again.');

        // Storage::download()/response() stream the file's bytes rather than
        // needing a local filesystem path, so this works identically whether
        // the PDF lives on the local disk or S3 (config('receipts.storage_disk')).
        return $invoice->pdf_url;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('business_id', $request->user()->business_id),
            ],
            'currency' => ['required', 'string', 'size:3'],
            'issue_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', Rule::in(['unpaid', 'part_payment', 'paid'])],
            'paid_in_full' => 'nullable|boolean',
            'amount_paid' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
    }
}
