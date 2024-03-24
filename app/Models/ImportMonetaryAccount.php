<?php

namespace App\Models;

use App\Casts\Obj;
use bunq\Model\Generated\Endpoint\MonetaryAccountBank;
use bunq\Model\Generated\Endpoint\MonetaryAccountExternal;
use bunq\Model\Generated\Endpoint\MonetaryAccountJoint;
use bunq\Model\Generated\Endpoint\MonetaryAccountSavings;
use Illuminate\Database\Eloquent\Model;

/**
 * @property MonetaryAccountBank|MonetaryAccountSavings|MonetaryAccountJoint|MonetaryAccountExternal $original
 */
class ImportMonetaryAccount extends Model
{
    protected $guarded = [];

    public function casts(): array
    {
        return [
            'original' => Obj::class,
            'original_json' => 'json',
        ];
    }
}
