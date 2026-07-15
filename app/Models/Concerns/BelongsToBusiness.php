<?php

namespace App\Models\Concerns;

use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope('business', function (Builder $builder) {
            if (auth()->check() && auth()->user()->business_id) {
                $builder->where($builder->getModel()->getTable().'.business_id', auth()->user()->business_id);
            }
        });

        static::creating(function ($model) {
            if (! $model->business_id && auth()->check()) {
                $model->business_id = auth()->user()->business_id;
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
