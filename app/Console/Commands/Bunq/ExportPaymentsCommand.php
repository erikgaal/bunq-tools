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
        $this->info('Exporting account payments...');

        foreach (MonetaryAccount::all() as $account) {
            $this->components->task($account->description, fn () => $this->exportPaymentsForAccount($account));
        }

        $this->newLine();

        return self::SUCCESS;
    }

    private function exportPaymentsForAccount(MonetaryAccount $account): void
    {
        $firstPayment = $account->importPayments()->first();
        $lastPayment = $account->importPayments()->latest()->first();

        if (!$firstPayment || !$lastPayment) {
            return;
        }

        foreach (range($firstPayment->created_at->year, $lastPayment->created_at->year) as $year) {
            (new AccountPaymentsExport($account, $year))->store(
                'bunq/' . AccountPaymentsExport::fileName($account, $year)
            );
        }
    }
}
