<?php
declare(strict_types=1);

namespace App\Console\Commands\Bunq;

use App\Exports\AccountPaymentsExport;
use App\Models\MonetaryAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Sleep;
use function Laravel\Prompts\info as info;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\spin;
use function Psy\debug;

class ExportPaymentsCommand extends Command
{
    protected $signature = 'bunq:export-payments';

    public function handle(): int
    {
        progress('Exporting accounts...', MonetaryAccount::all(), function (MonetaryAccount $account) {
            $firstPayment = $account->importPayments()->first();
            $lastPayment = $account->importPayments()->latest()->first();

            if (! $firstPayment || ! $lastPayment) {
                return;
            }

            foreach (range($firstPayment->created_at->year, $lastPayment->created_at->year) as $year) {
                (new AccountPaymentsExport($account, $year))->store('bunq/'.AccountPaymentsExport::fileName($account, $year));
            }
        });

        return self::SUCCESS;
    }
}
