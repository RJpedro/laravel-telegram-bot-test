<?php

namespace App\Models;

use App\Models\Relations\BelongsToSubscription;
use App\Models\Relations\HasOneRefund;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, HasOneRefund, BelongsToSubscription;

    protected $fillable = [
        'subscription_id',
        'telegram_id',
        'amount',
        'status',
        'payment_method',
    ];

    public function verifyRefundExists()
    {
        if ($this->refund()->exists()) {
            return true;
        }

        return false;
    }

    public function markAsPaid()
    {
        $this->status = 'paid';
        $this->save();
        
        return;
    }
}
