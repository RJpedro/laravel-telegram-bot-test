<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Repositories\PaymentRepository;
use App\Repositories\SubscriptionRepository;
use App\Repositories\TelegramUserRepository;
use Carbon\Carbon;

class WebhookService
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
        $subscription = $this->subscriptionRepository->updateSubscriptionStatusToRefunded($request);
        $user = $this->telegramUserRepository->setSubscriberStatus($request['data']['telegram_id'], 'deactive');

        $this->sendRefundMessage($user, $subscription);

        return response()->json(['status' => 'success'], 200);
    }
    
    /**
     * Envia mensagem de estorno para o usuário
     */
    protected function sendRefundMessage($user, Subscription $subscription): void
    {
        $plan = $subscription->plan;

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

        $this->subscriptionRepository->activateSubscription($subscription, $plan);
        $user->markAsSubscriber();
        $payment->markAsPaid();

        $this->sendSubscriptionCompletedMessage($user, $subscription, $plan);

        return response()->json($payment, 201);
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
