<?php

namespace App\Models\Relations;

use App\Models\Refund;

trait HasOneRefund
{
    /**
     * Relação com reembolsos
     */
    public function refund()
    {
        return $this->hasOne(Refund::class);
    }
}
