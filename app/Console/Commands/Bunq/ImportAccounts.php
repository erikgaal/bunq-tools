<?php

namespace App\Console\Commands\Bunq;

use App\Bunq\FetchAll;
use App\Models\ImportMonetaryAccount;
use App\Models\MonetaryAccount as MonetaryAccountModel;
use bunq\Model\Generated\Endpoint\MonetaryAccount;
use bunq\Model\Generated\Endpoint\MonetaryAccountBank;
use bunq\Model\Generated\Endpoint\MonetaryAccountExternal;
use bunq\Model\Generated\Endpoint\MonetaryAccountExternalSavings;
use bunq\Model\Generated\Endpoint\MonetaryAccountJoint;
use bunq\Model\Generated\Endpoint\MonetaryAccountSavings;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use SebastianBergmann\Exporter\Exporter;
use UnhandledMatchError;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;

class ImportAccounts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bunq:import-accounts';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        spin(function () {
            LazyCollection::make(fn () => FetchAll::execute(fn (array $params) => MonetaryAccount::listing($params)))
                ->map(function (MonetaryAccount $account) {
                    return match (true) {
                        null !== $account->getMonetaryAccountBank() => $this->fromBankAccount($account->getMonetaryAccountBank()),
                        null !== $account->getMonetaryAccountJoint() => $this->fromBankAccount($account->getMonetaryAccountJoint()),
                        null !== $account->getMonetaryAccountSavings() => $this->fromBankAccount($account->getMonetaryAccountSavings()),
                        null !== $account->getMonetaryAccountExternal() => $this->fromBankAccount($account->getMonetaryAccountExternal()),
                        null !== $account->getMonetaryAccountExternalSavings() => $this->fromBankAccount($account->getMonetaryAccountExternalSavings()),
                        default => throw new UnhandledMatchError((new Exporter())->export($account)),
                    };
                })
                ->collect()
                ->tap(function (Collection $collection) {
                    ImportMonetaryAccount::query()->upsert(
                        $collection->map(fn (ImportMonetaryAccount $importMonetaryAccount) => $importMonetaryAccount->getAttributes())->toArray(),
                        uniqueBy: ['id'],
                    );
                })
                ->each(function (ImportMonetaryAccount $importAccount) {
                    MonetaryAccountModel::query()->updateOrCreate(
                        ['id' => $importAccount->getKey()],
                        MonetaryAccountModel::fromApiAccount($importAccount->original)->toArray(),
                    );
                });
        }, 'Importing accounts...');

        info('Successfully imported all accounts.');
    }

    private function fromBankAccount(MonetaryAccountBank|MonetaryAccountJoint|MonetaryAccountSavings|MonetaryAccountExternal|MonetaryAccountExternalSavings $account): ImportMonetaryAccount
    {
        return new ImportMonetaryAccount([
            'id' => $account->getId(),
            'original' => $account,
            'original_json' => json_encode($account),
            'created_at' => $account->getCreated(),
        ]);
    }
}
