<?php

namespace App\Models;

use App\Models\Relations\HasOneSubscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    use HasFactory, HasOneSubscription;
    
    protected $primaryKey = 'telegram_id';
    public $incrementing = false;
    protected $keyType = 'bigInteger';
    protected $fillable = [
        'telegram_id',
        'first_name',
        'last_name',
        'username',
        'is_subscriber',
    ];

    public function markAsSubscriber()
    {
        $this->is_subscriber = 'active';
        $this->save();
        
        return;
    }
}