<?php

namespace App\Models\Relations;

use App\Models\Payment;

trait HasOnePayment
{
    /**
     * Relação com reembolsos
     */
    public function payment()
    {
        return $this->hasOne(Payment::class, 'subscription_id');
    }
}
