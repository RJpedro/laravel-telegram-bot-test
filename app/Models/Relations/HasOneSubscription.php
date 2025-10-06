<?php

namespace App\Models\Relations;

use App\Models\Subscription;

trait HasOneSubscription
{
    /**
     * Relação com Subscriptions
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'telegram_id', 'telegram_id')->latestOfMany();
    }
}
