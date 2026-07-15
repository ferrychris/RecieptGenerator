<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'logo_url',
        'tagline',
        'address',
        'phone',
        'email',
        'tax_id',
        'default_currency',
        'invoice_number_seq',
        'template',
        'show_name_on_receipt',
    ];

    /**
     * logo_url stores a disk-relative path. Appending the resolved absolute
     * URL means the frontend never has to guess which disk (local /storage
     * symlink vs S3) it lives on.
     */
    protected $appends = ['logo_full_url'];

    protected function casts(): array
    {
        return [
            'show_name_on_receipt' => 'boolean',
        ];
    }

    protected function logoFullUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo_url
                ? Storage::disk(config('receipts.uploads_disk'))->url($this->logo_url)
                : null,
        );
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * All members of this organization (via business_user), regardless of
     * which org is currently active for them.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function nextInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $business = static::query()->lockForUpdate()->findOrFail($this->id);
            $business->increment('invoice_number_seq');

            return sprintf('RCT-%05d', $business->invoice_number_seq);
        });
    }
}
