<?php

namespace App\Repositories;

use App\Models\TelegramUser;

class TelegramUserRepository
{
    protected $model;

    public function __construct(TelegramUser $telegramUser)
    {
        $this->model = $telegramUser;
    }

    public function findTelegramUser(int $telegramId): ?TelegramUser
    {
        return TelegramUser::query()->where('telegram_id', $telegramId)->first();
    }

    public function createOrUpdate(array $data): TelegramUser
    {
        return TelegramUser::updateOrCreate(['telegram_id' => $data['telegram_id']],$data);
    }

    public function setSubscriberStatus(int $telegramId, string $status): false | TelegramUser
    {
        $user = TelegramUser::where('telegram_id', $telegramId)->first();

        if (!$user) return false;

        $user->is_subscriber = $status;
        $user->save();

        return $user;
    }

    public function returnUserData(int $chatId): ?TelegramUser
    {
        return TelegramUser::where('telegram_id', $chatId)->first();
    }
}
