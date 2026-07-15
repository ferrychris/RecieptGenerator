<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_transactions_can_be_exported_for_a_date_range(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'owner_id' => $user->id,
            'name' => 'Test Business',
            'default_currency' => 'USD',
            'template' => 'ledger',
            'invoice_number_seq' => 1,
        ]);

        $user->business_id = $business->id;
        $user->save();

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Jane Doe',
        ]);

        $invoice = Invoice::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'number' => 'RCT-00001',
            'currency' => 'USD',
            'template' => 'ledger',
            'status' => 'unpaid',
            'issue_date' => '2026-01-10',
            'due_date' => '2026-01-10',
            'subtotal' => 0,
            'tax_total' => 0,
            'total' => 0,
            'amount_paid' => 0,
        ]);

        $invoice->transactions()->create([
            'old_status' => null,
            'new_status' => 'unpaid',
            'amount' => 0,
            'note' => 'Invoice created',
            'created_at' => '2026-01-10 09:00:00',
            'updated_at' => '2026-01-10 09:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/invoices/export-transactions?from=2026-01-01&to=2026-01-15');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition', 'attachment; filename="transactions-export.csv"');
    }
}
