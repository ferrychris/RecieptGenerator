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
use Illuminate\Support\Str;

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

    /**
     * Browser-usable URL for the logo, for the dashboard's <img> tags.
     *
     * A plain public URL, since the logo bucket is public. Set the disk's
     * `url` (LOGO_BUCKET_URL) to the bucket's public base — an R2 bucket's
     * S3 API endpoint is not publicly readable, so without it these render
     * as silently broken images.
     *
     * Only for on-screen display: PDFs embed the logo as a base64 data URI,
     * since they render with no session or origin.
     */
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

            return sprintf('%s-%05d', static::receiptPrefix($business->name), $business->invoice_number_seq);
        });
    }

    /**
     * The short code that leads every receipt number, e.g. the "FRET" in
     * FRET-00003. Taken from the first four alphanumeric characters of the
     * organization's name.
     *
     * Accents are transliterated rather than stripped so "Café" yields CAFE
     * rather than the three-character CAF.
     */
    public static function receiptPrefix(?string $name): string
    {
        $alphanumeric = preg_replace('/[^A-Za-z0-9]/', '', Str::ascii((string) $name));

        $prefix = mb_strtoupper(mb_substr($alphanumeric, 0, 4));

        // Names made entirely of symbols (or empty) would otherwise produce a
        // bare "-00001", which reads like a bug on a customer's receipt.
        return $prefix !== '' ? $prefix : 'RCT';
    }
}
