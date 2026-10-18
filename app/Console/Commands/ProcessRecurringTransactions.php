<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\RecurringTransactionProcessor;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all due recurring transaction rules and generate transactions';

    /**
     * Execute the console command.
     */
    public function handle(RecurringTransactionProcessor $processor): int
    {
        $this->info('Starting recurring transactions processing...');

        $created = $processor->processAllDue();

        $this->info("Completed. Created {$created} transactions.");

        return Command::SUCCESS;
    }
}
