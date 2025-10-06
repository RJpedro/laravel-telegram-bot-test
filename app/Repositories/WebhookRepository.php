<?php

namespace App\Repositories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WebhookRepository
{
    protected PaymentRepository $paymentRepository;
    protected TelegramUserRepository $telegramUserRepository;
    protected SubscriptionRepository $subscriptionRepository;
    protected TelegramService $telegramService;

    public function __construct(
        PaymentRepository $paymentRepository,
        SubscriptionRepository $subscriptionRepository,
        TelegramUserRepository $telegramUserRepository,
        TelegramService $telegramService
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->telegramUserRepository = $telegramUserRepository;
        $this->telegramService = $telegramService;
    }

    /**
     * Handle refund webhook
     */
    public function handleRefund(array $request)
    {
        $subscription = $this->updateSubscription($request);
        $user = $this->updateUser($request);

        $this->sendRefundMessage($user, $subscription);

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Atualiza assinatura para 'refunded'
     */
    protected function updateSubscription(array $request): Subscription
    {
        $subscription = Subscription::findOrFail($request['data']['subscription_id']);
        $subscription->update(['status' => 'refunded']);

        return $subscription;
    }

    /**
     * Atualiza usuário para 'deactive'
     */
    protected function updateUser(array $request)
    {
        return $this->telegramUserRepository->setSubscriberStatus($request['data']['telegram_id'], 'deactive');
    }

    /**
     * Envia mensagem de estorno para o usuário
     */
    protected function sendRefundMessage($user, Subscription $subscription): void
    {
        $plan = $subscription->plan;

        Log::info(json_encode(['teste' => $user]));

        $firstName = $user->first_name;
        $planName = $plan->name;
        $amount = number_format($plan->amount, 2, ',', '.');

        $text = "<b>⚠️ Olá {$firstName}!</b>\n\n";
        $text .= "O estorno da sua assinatura do plano <b>{$planName}</b> foi realizado com sucesso.\n";
        $text .= "💰 Valor estornado: <b>R$ {$amount}</b>\n";
        $text .= "✅ Status da assinatura: <b>Estornada</b>\n\n";
        $text .= "Se tiver dúvidas, entre em contato com o suporte.";

        $this->telegramService->sendMessage(
            $user->telegram_id,
            $text,
        );
    }

    /**
     * Webhook de pagamento já refatorado (mantido como estava)
     */
    public function handlePayment(array $request)
    {
        $data = $request['data'];

        $plan = Plan::findOrFail($data['plan_id']);
        $payment = $this->paymentRepository->create($data);
        $subscription = Subscription::findOrFail($data['subscription_id']);
        $user = $subscription->user;

        $this->activateSubscription($subscription, $plan);
        $user->markAsSubscriber();
        $payment->markAsPaid();

        $this->sendSubscriptionCompletedMessage($user, $subscription, $plan);

        return response()->json($payment, 201);
    }

    // Métodos auxiliares do handlePayment (mantidos)
    protected function activateSubscription(Subscription $subscription, Plan $plan)
    {
        $subscription->update([
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays($plan->duration_days),
        ]);
    }

    protected function sendSubscriptionCompletedMessage($user, Subscription $subscription, Plan $plan)
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Reembolso', 'callback_data' => '/reembolso'],
                    ['text' => 'Menu', 'callback_data' => '/menu']
                ]
            ]
        ];

        $endDate = Carbon::parse($subscription->end_date)->format('d/m/Y');

        $text = "<b>✅ Assinatura ativada com sucesso!</b>\n\n"
            . "Plano: <b>{$plan->plan_type}</b>\n"
            . "Vencimento: <b>{$endDate}</b>";

        $this->telegramService->sendMessage(
            $user->telegram_id,
            $text,
            $keyboard,
        );
    }
}
