<?php

namespace App\ViewModels;

use App\Models\Invoice;
use App\Services\DefaultTheme;
use App\Services\LayoutCatalog;
use App\Services\MoneyFormatter;
use Illuminate\Support\Facades\Storage;

class InvoiceDocumentBuilder
{
    public static function build(Invoice $invoice, string $layoutKey = 'ledger', ?array $theme = null, ?array $options = null): array
    {
        $invoice->loadMissing('items', 'customer', 'business.owner', 'transactions');

        $business = $invoice->business;
        $customer = $invoice->customer;
        $currency = $invoice->currency;
        $symbol = MoneyFormatter::symbol($currency);

        $items = $invoice->items->map(fn ($item) => [
            'description' => $item->description,
            'details' => null,
            'qty' => rtrim(rtrim(number_format((float) $item->qty, 2), '0'), '.'),
            'unit' => null,
            'unit_price' => MoneyFormatter::format($item->unit_price, $currency),
            'tax_rate' => $item->tax_rate > 0 ? number_format((float) $item->tax_rate, 2).'%' : null,
            'line_total' => MoneyFormatter::format($item->line_total, $currency),
        ])->all();

        $taxBreakdown = $invoice->items
            ->filter(fn ($item) => (float) $item->tax_rate > 0)
            ->groupBy('tax_rate')
            ->map(function ($group, $rate) use ($currency) {
                $amount = $group->sum(fn ($item) => $item->qty * $item->unit_price * $item->tax_rate / 100);

                return [
                    'label' => 'Tax',
                    'rate' => number_format((float) $rate, 2).'%',
                    'amount' => MoneyFormatter::format($amount, $currency),
                ];
            })
            ->values()
            ->all();

        $amountPaid = (float) $invoice->amount_paid;
        $balanceDue = (float) $invoice->balance_due;

        $firstPaidTransaction = $invoice->transactions
            ->filter(fn ($t) => (float) $t->amount > 0)
            ->sortBy('created_at')
            ->first();
        $firstPaymentDate = $firstPaidTransaction?->created_at;

        return [
            'business' => [
                'name' => $business->name,
                'show_name' => (bool) $business->show_name_on_receipt,
                'logo_url' => self::resolveLogoDataUri($business->logo_url),
                'address_lines' => $business->address ? explode("\n", $business->address) : [],
                'tax_id' => $business->tax_id,
                'email' => $business->email ?: $business->owner?->email,
                'phone' => $business->phone,
                'owner_name' => $business->owner?->name,
            ],
            'customer' => [
                'name' => $customer->name,
                'company' => $customer->company,
                'address_lines' => $customer->billing_address ? explode("\n", $customer->billing_address) : [],
                'tax_number' => null,
                'email' => $customer->email,
                'phone' => $customer->whatsapp_number,
            ],
            'invoice' => [
                'number' => $invoice->number,
                'issue_date' => $invoice->issue_date?->format('M j, Y'),
                'due_date' => $invoice->due_date?->format('M j, Y'),
                'created_at' => $invoice->created_at?->format('M j, Y'),
                'currency' => $currency,
                'currency_symbol' => $symbol,
                'status' => $invoice->status,
                'payment_terms' => null,
                'reference' => null,
                'notes' => $invoice->notes,
                'first_payment_date' => $firstPaymentDate?->format('M j, Y'),
            ],
            'items' => $items,
            'totals' => [
                'subtotal' => MoneyFormatter::format($invoice->subtotal, $currency),
                'tax_breakdown' => $taxBreakdown,
                'discount' => null,
                'total' => MoneyFormatter::format($invoice->total, $currency),
                'amount_paid' => $amountPaid > 0 ? MoneyFormatter::format($amountPaid, $currency) : null,
                'balance_due' => $amountPaid > 0 ? MoneyFormatter::format($balanceDue, $currency) : null,
            ],
            'payment' => [
                'method' => null,
                'bank_name' => null,
                'account' => null,
                'routing' => null,
                'iban' => null,
                'swift' => null,
                'instructions' => null,
            ],
            'theme' => array_merge(DefaultTheme::forLayout($layoutKey), $theme ?? []),
            'options' => array_merge(DefaultTheme::defaultOptions(), $options ?? []),
            'meta' => [
                'doc_type' => 'receipt',
                'locale' => 'en',
                'page' => LayoutCatalog::page($layoutKey),
            ],
        ];
    }

    /**
     * PDFs render from a local file:// temp HTML file with no web server
     * involved, so a stored disk path (or even a /storage/... URL) never
     * resolves there. Inline the logo as a base64 data URI instead, which
     * works regardless of origin.
     */
    private static function resolveLogoDataUri(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = Storage::disk(config('receipts.uploads_disk'));

        if (! $disk->exists($path)) {
            return null;
        }

        $mime = $disk->mimeType($path) ?: 'image/png';
        $contents = $disk->get($path);

        return "data:{$mime};base64,".base64_encode($contents);
    }
}
