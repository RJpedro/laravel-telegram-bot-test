<?php

namespace App\Models\Relations;

use App\Models\Subscription;

trait HasManySubscription
{
    /**
     * Relação com assinaturas (Subscription)
     * Um plano pode ter muitas assinaturas.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
