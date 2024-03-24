<?php
declare(strict_types=1);

namespace App\Models;

use bunq\Model\Generated\Endpoint\MonetaryAccountBank;
use bunq\Model\Generated\Endpoint\MonetaryAccountExternal;
use bunq\Model\Generated\Endpoint\MonetaryAccountJoint;
use bunq\Model\Generated\Endpoint\MonetaryAccountSavings;
use bunq\Model\Generated\Object\Pointer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class MonetaryAccount extends Model
{
    protected $guarded = [];

    /**
     * @return HasMany<ImportPayment>
     */
    public function importPayments(): HasMany
    {
        return $this->hasMany(ImportPayment::class);
    }

    public static function fromApiAccount(MonetaryAccountBank|MonetaryAccountSavings|MonetaryAccountJoint|MonetaryAccountExternal $account): self
    {
        return new self([
            'id' => $account->getId(),
            'display_name' => $account instanceof MonetaryAccountBank ? $account->getDisplayName() : null,
            'description' => $account->getDescription(),
            'iban' => Arr::first($account->getAlias(), fn (Pointer $alias) => $alias->getType() === 'IBAN')?->getValue(),
            'currency' => $account->getCurrency(),
            'active' => $account->getStatus() === 'ACTIVE',
            'created_at' => $account->getCreated(),
        ]);
    }
}
