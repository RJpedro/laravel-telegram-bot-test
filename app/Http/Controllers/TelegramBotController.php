<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TelegramBotService;

class TelegramBotController extends Controller
{
    protected $telegramBotService;

    public function __construct(TelegramBotService $telegramBotService)
    {
        $this->telegramBotService = $telegramBotService;
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

        if ($text == '/start') return $this->telegramBotService->handleStart($chatId);
        if ($text == '/reembolso') return $this->telegramBotService->handleRefund($chatId);
        if ($text == '/assinatura') return $this->telegramBotService->handlePlan($chatId);
        if ($text == '/menu') return $this->telegramBotService->handleMenu($chatId);
        if (str_contains($text, 'select_plan')) return $this->telegramBotService->planSubscriber($update);
    }
}
