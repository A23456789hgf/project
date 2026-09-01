<?php

namespace App\Console\Commands;

use App\Models\Authority;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanInvalidAuthorities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authorities:clean-invalid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up invalid authority records where agency_name starts with an Excel formula or =';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Searching for invalid authority records...');

        $invalidAuthorities = Authority::withoutGlobalScopes()
            ->where(function ($query) {
                $query->whereNull('agency_name')
                    ->orWhere('agency_name', '=', '')
                    ->orWhere('agency_name', 'LIKE', '=%');
            })
            ->get();

        $count = $invalidAuthorities->count();

        if ($count === 0) {
            $this->info('No invalid authority records found.');

            return Command::SUCCESS;
        }

        $this->warn("Found {$count} invalid authority records.");

        foreach ($invalidAuthorities as $auth) {
            $this->line("Deleting authority ID: {$auth->id} - Name: {$auth->agency_name}");
            $auth->delete();
        }

        Log::info("Cleaned up {$count} invalid authority records via console command.");
        $this->info("Successfully deleted {$count} invalid authority records.");

        return Command::SUCCESS;
    }
}
