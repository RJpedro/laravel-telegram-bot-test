<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Refund;
use App\Models\Subscription;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Subscription $subscription;

    /**
     * Construtor
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Executa o job.
     */
    public function handle(): void
    {
        $payment = $this->subscription->payment;
        $chatId = $this->subscription->user->telegram_id;
        $telegramService = app(TelegramService::class);

        if ($payment->status != 'paid') {
            $telegramService->sendMessage(
                $chatId,
                "<b>⚠️ Pagamento não encontrado</b>\n\n"
                    . "Não foi possível realizar o reembolso, pois o pagamento ainda não foi efetuado.\n\n"
                    . "Verifique se há uma assinatura ativa ou tente novamente mais tarde. 💬",
            );

            return;
        }

        // Garantia de idempotência: evita reembolso duplicado
        $verifyRefundExists = $payment->verifyRefundExists();
        if ($verifyRefundExists) {
            $telegramService->sendMessage(
                $chatId,
                "<b>⚠️ Reembolso já realizado\n\n</b>"
                    . "Parece que o pagamento referente à assinatura <b>#{$this->subscription->id}</b> "
                    . "já possui um reembolso registrado.\n\n"
                    . "Se você acredita que isso é um erro, entre em contato com o suporte. 💬",
            );
            return;
        }

        $refund = $this->processRefund($payment);

        $this->sendWebhookMessage($payment, $refund);
    }

    protected function processRefund(Payment $payment) 
    {
        $refund = Refund::create([
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'status' => 'processing',
            'reason' => 'Solicitação via bot Telegram',
        ]);

        $refund->update(['status' => 'completed']);

        return $refund;
    } 

    protected function sendWebhookMessage($payment, $refund) 
    {
        try {
            $webhookUrl = config('services.webhooks.refunds');
            if ($webhookUrl) {
                Http::post($webhookUrl, [
                    'event' => 'refund.completed',
                    'data' => [
                        'telegram_id' => $this->subscription->user->telegram_id,
                        'subscription_id' => $this->subscription->id,
                        'payment_id' => $payment->id,
                        'refund_id' => $refund->id,
                        'amount' => $refund->amount,
                        'status' => $refund->status,
                    ],
                ]);
            }

            Log::info("Webhook de reembolso enviado com sucesso para assinatura ID {$this->subscription->id}");
        } catch (\Throwable $e) {
            Log::error("Erro ao enviar webhook de reembolso: {$e->getMessage()}");
        }
    } 
}
