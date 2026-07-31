<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\MoneyFormatter;
use Inertia\Inertia;
use Inertia\Response;

class ReceiptVerificationController extends Controller
{
    /**
     * Public receipt-authenticity page, reached by scanning the QR code
     * printed on a rendered PDF. Only reachable via a validly signed URL
     * (see the `signed` middleware on this route) — there is no other
     * lookup or search surface, so a receipt can only be verified by
     * someone who actually has it in hand (or its PDF).
     */
    public function show(Invoice $invoice): Response
    {
        $invoice->loadMissing('business', 'customer');

        return Inertia::render('Verify/Show', [
            'receipt' => [
                'number' => $invoice->number,
                'status' => $invoice->status,
                'issue_date' => $invoice->issue_date?->format('M j, Y'),
                'total' => MoneyFormatter::format($invoice->total, $invoice->currency),
                'amount_paid' => $invoice->amount_paid > 0
                    ? MoneyFormatter::format($invoice->amount_paid, $invoice->currency)
                    : null,
                'balance_due' => $invoice->amount_paid > 0
                    ? MoneyFormatter::format($invoice->balance_due, $invoice->currency)
                    : null,
                'business_name' => $invoice->business->name,
                'customer_name' => $invoice->customer->name,
            ],
        ]);
    }
}
