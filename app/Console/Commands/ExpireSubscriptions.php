<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Expire subscriptions that have passed their expiry date';

    public function handle(): int
    {
        $now = now();

        $trialCount = Subscription::where('status', 'trial')
            ->where(function ($query) use ($now) {
                $query->whereNull('trial_ends_at')
                    ->orWhere('trial_ends_at', '<=', $now);
            })
            ->update(['status' => 'expired']);

        $paidCount = Subscription::where('status', 'active')
            ->where(function ($query) use ($now) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '<=', $now);
            })
            ->update(['status' => 'expired']);

        $this->info("Expired trials: {$trialCount}");
        $this->info("Expired paid subscriptions: {$paidCount}");

        return self::SUCCESS;
    }
}
