<?php

namespace App\Models\Relations;

use App\Models\Payment;

trait BelongsToPayment
{
   /**
     * Relação com pagamento
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}