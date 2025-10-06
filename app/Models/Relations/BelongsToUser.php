<?php

namespace App\Models\Relations;

use App\Models\TelegramUser;

trait BelongsToUser
{
    /**
     * Relação com usuário
     */
    public function user()
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_id', 'telegram_id');
    }
}