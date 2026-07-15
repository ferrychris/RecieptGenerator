<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;
    use BelongsToBusiness;

    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PART_PAYMENT = 'part_payment';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'business_id',
        'customer_id',
        'number',
        'currency',
        'template',
        'status',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_total',
        'total',
        'amount_paid',
        'notes',
        'pdf_url',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('position');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InvoiceTransaction::class)->orderBy('created_at', 'desc');
    }

    protected static function booted()
    {
        static::saving(function ($invoice) {
            $subtotal = collect($invoice->items)->sum(fn ($i) => ($i['qty'] ?? 0) * ($i['unit_price'] ?? 0));
            $taxTotal = collect($invoice->items)->sum(fn ($i) => ($i['qty'] ?? 0) * ($i['unit_price'] ?? 0) * ($i['tax_rate'] ?? 0) / 100);

            $invoice->subtotal = $subtotal;
            $invoice->tax_total = $taxTotal;
            $invoice->total = $subtotal + $taxTotal;
            $invoice->amount_paid = $invoice->amount_paid ?? 0;
        });
    }

    protected function balanceDue(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => max(0, (float)$this->total - (float)$this->amount_paid),
        );
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items->sum(fn (InvoiceItem $item) => $item->qty * $item->unit_price);
        $taxTotal = $this->items->sum(fn (InvoiceItem $item) => $item->qty * $item->unit_price * $item->tax_rate / 100);

        $this->subtotal = round($subtotal, 2);
        $this->tax_total = round($taxTotal, 2);
        $this->total = round($subtotal + $taxTotal, 2);
        $this->save();
    }

}
