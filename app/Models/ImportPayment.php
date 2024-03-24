<?php

namespace App\Models;

use App\Casts\Obj;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \bunq\Model\Generated\Endpoint\Payment $original
 */
class ImportPayment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'original_json' => 'json',
            'original' => Obj::class,
        ];
    }

    /**
     * @return BelongsTo<self, MonetaryAccount>
     */
    public function monetaryAccount(): BelongsTo
    {
        return $this->belongsTo(MonetaryAccount::class);
    }
}
