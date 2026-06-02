<?php

namespace App\Console\Commands;

use App\Models\Support;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanClosedTickets extends Command
{
    protected $signature = 'tickets:clean-closed';
    protected $description = 'Delete tickets closed more than 3 days ago';

    public function handle(): void
    {
        $cutoff = now()->subDays(3);

        $deleted = Support::where('status', 'closed')
            ->where('updated_at', '<', $cutoff)
            ->delete();

        Log::info("Cleaned {$deleted} closed tickets older than 3 days.");

        $this->info("Deleted {$deleted} closed tickets.");
    }
}
