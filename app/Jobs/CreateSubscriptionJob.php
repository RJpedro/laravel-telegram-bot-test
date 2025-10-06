<?php

namespace App\Jobs;

use App\Models\Plan;
use App\Models\TelegramUser;
use App\Repositories\SubscriptionRepository;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreateSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected int $planId;

    /**
     * Construtor do job
     */
    public function __construct(int $userId, int $planId)
    {
        $this->userId = $userId;
        $this->planId = $planId;
    }

    /**
     * Executa o job
     */
    public function handle(SubscriptionRepository $subscriptionRepository, TelegramService $telegramService)
    {
        // Recupera usuário e plano
        $user = TelegramUser::where('telegram_id', $this->userId)->firstOrFail();
        $plan = Plan::findOrFail($this->planId);

        // Envia mensagem de processamento para o usuário
        $this->sendProcessingMessage($telegramService, $user->telegram_id, "processando sua assinatura");

        // Cria assinatura em estado 'processing'
        $subscription = $subscriptionRepository->create([
            'telegram_id' => $user->telegram_id,
            'plan_id' => $plan->id,
            'status' => 'processing',
        ]);

        // Dispara webhook idempotente para API
        $this->callPaymentWebhook($user, $plan, $subscription);
    }

    /**
     * Envia mensagem de processamento para o usuário
     */
    protected function sendProcessingMessage(TelegramService $telegramService, int $chatId, string $action): void
    {
        $telegramService->sendMessage(
            $chatId,
            "Estamos {$action}. Aguarde até a conclusão ⏳",
        );
    }

    /**
     * Chama o webhook de pagamento para API
     */
    protected function callPaymentWebhook(TelegramUser $user, Plan $plan, $subscription): void
    {
        $webhookUrl = config('services.webhooks.payment');

        if (!$webhookUrl) return;

        try {
            Http::post($webhookUrl, [
                'event' => 'payment',
                'data' => [
                    'payment_method' => 'credit_card',
                    'amount' => $plan->amount,
                    'telegram_id' => $user->telegram_id,
                    'plan_id' => $plan->id,
                    'subscription_id' => $subscription->id,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error("Erro ao enviar webhook de pagamento: {$e->getMessage()}");
        }
    }
}
