<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\TelegramBotRepository;

class TelegramBotController extends Controller
{
    protected $repository;

    public function __construct(TelegramBotRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Recebe mensagens do Telegram
     */
    public function handle(Request $request)
    {
        $this->verifyMessageToCallMethod($request);
    }

    protected function verifyMessageToCallMethod($request)
    {
        $update = $request->all();

        if (isset($update['message'])) {
            $text = $update['message']['text'];
            $chatId = $update['message']['chat']['id'];
        }

        if (isset($update['edited_message'])) {
            $text = $update['edited_message']['text'];
            $chatId = $update['edited_message']['chat']['id'];
        }

        if (isset($update['callback_query'])) {
            $text = $update['callback_query']['data'];
            $chatId = $update['callback_query']['message']['chat']['id'];
        }

        if ($text == '/start') return $this->repository->handleStart($chatId);
        if ($text == '/reembolso') return $this->repository->handleRefund($chatId);
        if ($text == '/assinatura') return $this->repository->handlePlan($chatId);
        if ($text == '/menu') return $this->repository->handleMenu($chatId);
        if (str_contains($text, 'select_plan')) return $this->repository->planSubscriber($update);
    }
}
