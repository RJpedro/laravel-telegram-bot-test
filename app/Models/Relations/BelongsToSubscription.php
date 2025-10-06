<?php

namespace App\Models\Relations;

use App\Models\Subscription;

trait BelongsToSubscription
{
    /**
     * Relação inversa com Subscription
     * Um pagamento pertence a uma assinatura.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}