<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    private string $apiUrl;

    public function __construct()
    {
        $token = config('services.telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$token}/";
    }

    /**
     * Envia uma mensagem para o usuário
     */
    public function sendMessage(int $chatId, string $text, array $replyMarkup = []): array
    {
        $payload = $this->buildPayload($chatId, $text, $replyMarkup);

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($this->apiUrl . 'sendMessage', $payload);

        return $response->json();
    }

    /**
     * Monta payload padrão
     */
    private function buildPayload(int $chatId, string $text, array $replyMarkup = []): array
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];

        if (!empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        return $payload;
    }

    /**
     * Monta teclado inline padrão (ajuda no reuso)
     */
    public function mountKeyboard(array $buttons): array
    {
        return ['inline_keyboard' => $buttons];
    }
}
