<?php

namespace App\Console\Commands;

use App\Models\PendingLogin;
use Illuminate\Console\Command;

class ExpirePendingLogins extends Command
{
    protected $signature = 'session:expire-pending-logins';
    protected $description = 'Expire pending login requests that have passed their expiration time.';

    public function handle(): int
    {
        $expired = PendingLogin::where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$expired} pending login(s).");

        return self::SUCCESS;
    }
}
