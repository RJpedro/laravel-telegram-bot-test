<?php

namespace App\Models\Relations;

use App\Models\Plan;

trait BelongsToPlan
{
    /**
     * Relação com plano
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}