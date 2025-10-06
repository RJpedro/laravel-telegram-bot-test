<?php

namespace App\Models;

use App\Models\Relations\BelongsToPlan;
use App\Models\Relations\BelongsToUser;
use App\Models\Relations\HasOnePayment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory, BelongsToPlan, BelongsToUser, HasOnePayment;

    protected $fillable = [
        'telegram_id',
        'plan_id',
        'status',
        'start_date',
        'end_date',
    ];
}
