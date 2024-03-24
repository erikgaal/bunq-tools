<?php

namespace App\Console\Commands\Bunq;

use App\Bunq\FetchAll;
use App\Models\MonetaryAccount as MonetaryAccountModel;
use App\Models\ImportPayment as PaymentModel;
use bunq\Model\Generated\Endpoint\Payment;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\LazyCollection;
use Laravel\Prompts\Progress;
use function Laravel\Prompts\info;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class ImportPayments extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bunq:import-payments';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $account = $this->selectAccount();

        spin(function () use ($account, &$newImportedPayments) {
            $lastImportedPayment = $account->importPayments()->latest()->first();

            $newImportedPayments = LazyCollection::make(fn () => FetchAll::execute(fn (array $params) => Payment::listing($account->getKey(), $params), reversed: true))
                ->map(function (Payment $payment) use ($account) {
                    return new PaymentModel([
                        'id' => $payment->getId(),
                        'original_json' => json_encode($payment),
                        'original' => serialize($payment),
                        'monetary_account_id' => $account->getKey(),
                        'created_at' => $payment->getCreated(),
                    ]);
                })
                ->takeWhile(fn (PaymentModel $importPayment) => ! $lastImportedPayment || $importPayment->created_at->greaterThan($lastImportedPayment->created_at));

            $newImportedPayments
                ->chunk(200)
                ->each(function (LazyCollection $chunk) {
                    PaymentModel::query()->upsert(
                        $chunk->toArray(),
                        uniqueBy: ['id'],
                    );
                });

            return $newImportedPayments;
        }, "Fetching transactions for [{$account->description}]...");

        $newImportedPayments->isNotEmpty()
            ? info("Successfully imported {$newImportedPayments->count()} new payments.")
            : warning('No new payments to import!');

        return self::SUCCESS;
    }

    private function selectAccount(): MonetaryAccountModel
    {
        $accounts = MonetaryAccountModel::query()
            ->orderByDesc('active')
            ->withMax('importPayments', 'created_at')
            ->withCasts(['import_payments_max_created_at' => 'datetime'])
            ->get();

        $selectedAccount = select(
            'Choose the account',
            $accounts->mapWithKeys(fn (MonetaryAccountModel $account) => [$account->getKey() => collect([
                $account->description,
                "[$account->iban]",
                ! $account->active ? "[inactive]" : null,
                "[last_imported_payment={$account->import_payments_max_created_at?->toDateString()}]",
            ])->filter()->implode(' ')])
        );

        return $accounts->find($selectedAccount);
    }
}
