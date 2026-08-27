<?php

namespace App\Console\Commands;

use App\Models\CommandQueue;
use Illuminate\Console\Command;

class RetryDeliveredCommands extends Command
{
    protected $signature   = 'commands:retry-stale {--minutes=10 : Re-queue commands delivered longer than this many minutes ago}';
    protected $description = 'Re-queue delivered commands that were never acknowledged by the game server';

    public function handle(): void
    {
        $cutoff = now()->subMinutes((int) $this->option('minutes'));

        $count = CommandQueue::where('status', 'delivered')
            ->where('delivered_at', '<=', $cutoff)
            ->update([
                'status'       => 'pending',
                'delivered_at' => null,
                'updated_at'   => now(),
            ]);

        if ($count > 0) {
            $this->warn("Re-queued {$count} stale command(s) that were never acknowledged.");
        }
    }
}
