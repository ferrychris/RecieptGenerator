<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    use BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'name',
        'company',
        'email',
        'whatsapp_number',
        'billing_address',
        'tax_number',
        'default_currency',
        'payment_terms',
        'notes',
    ];
}
