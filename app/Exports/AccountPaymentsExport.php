<?php

namespace App\Exports;

use App\Models\ImportPayment;
use App\Models\MonetaryAccount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements WithMapping<ImportPayment>
 */
final readonly class AccountPaymentsExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    public function __construct(
        private MonetaryAccount $account,
        private int $year,
    ) {
    }

    public function query(): Builder
    {
        return ImportPayment::query()
            ->whereBelongsTo($this->account)
            ->whereBetween('created_at', [$start = CarbonImmutable::create($this->year), $start->endOfYear()]);
    }

    /**
     * @param ImportPayment $row
     */
    public function map($row): array
    {
        $payment = $row->original;

        return [
            $row->created_at->toDateString(),
            '',
            $payment->getAmount()->getValue(),
            $payment->getAlias()->getIban(),
            $payment->getCounterpartyAlias()->getIban(),
            $payment->getCounterpartyAlias()->getDisplayName(),
            trim($payment->getDescription()),
            $payment->getAmount()->getCurrency(),
            $payment->getCounterpartyAlias()->getMerchantCategoryCode(),
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Interest Date',
            'Amount',
            'Account',
            'Counterparty',
            'Name',
            'Description',
            'Currency',
            'MCC',
        ];
    }

    public static function fileName(MonetaryAccount $account, int $year): string
    {
        return "{$account->iban}/$year.csv";
    }
}
