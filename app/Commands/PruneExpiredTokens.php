<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class PruneExpiredTokens extends Command
{
    protected $signature = 'sanctum:prune-expired';
    protected $description = 'Prune expired Sanctum tokens';

    public function handle()
    {
        $expiration = config('sanctum.expiration');

        if (!$expiration) {
            $this->info('Sanctum token expiration is not enabled.');
            return;
        }

        $count = PersonalAccessToken::where('created_at', '<', now()->subMinutes($expiration))
            ->whereNull('expires_at')
            ->orWhere('expires_at', '<', now())
            ->delete();

        $this->info("$count expired Sanctum tokens pruned.");
    }
}
