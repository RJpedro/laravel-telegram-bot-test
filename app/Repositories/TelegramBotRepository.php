<?php

namespace App\Repositories;

use App\Jobs\CreateSubscriptionJob;
use App\Jobs\RefundJob;
use App\Models\Plan;
use App\Models\TelegramUser;
use App\Services\TelegramService;
use Carbon\Carbon;

class TelegramBotRepository
{
    protected TelegramService $telegramService;
    protected TelegramUserRepository $telegramUserRepository;

    public function __construct(TelegramService $telegramService, TelegramUserRepository $telegramUserRepository)
    {
        $this->telegramService = $telegramService;
        $this->telegramUserRepository = $telegramUserRepository;
    }

    /**
     * Lógica do comando /start
     */
    public function handleStart(int $chatId)
    {
        return $this->verifyUser($chatId);
    }

    public function handleRefund(int $chatId)
    {
        $user = $this->returnUserData($chatId);

        if ($user) {
            $subscription = $user->subscription;

            if (!$subscription || $subscription->status !== 'active') {
                return $this->sendWelcomeMessage($chatId);
            }

            RefundJob::dispatch($subscription)->delay(now()->addSeconds(4));

            return $this->sendProcessingMessage($chatId);
        }

        return $this->sendWelcomeMessage($chatId);
    }
    
    public function handlePlan(int $chatId)
    {
        $user = $this->returnUserData($chatId);

        if ($user) {
            $subscription = $user->subscription;
            
            if ($subscription && $subscription->status === 'active') {
                return $this->sendActiveSubscriptionMessage($chatId);
            }
        }

        $plans = (new Plan)->getPlansFormattedForBot();

        $this->telegramService->sendMessage(
            $chatId,
            'Escolha um plano para assinar:',
            $plans
        );
    }

    public function handleMenu(int $chatId)
    {
        $text = "📋 Menu de Opções\n\n"
            . "Você já possui uma assinatura ativa! Aqui estão alguns comandos úteis:\n\n"
            . "💸 <b>/reembolso</b> → Solicitar um reembolso\n"
            . "📝 <b>/assinatura</b> → Cadastrar uma nova assinatura\n"
            . "🚀 <b>/start</b> → Ver opções de assinatura e menu principal\n\n"
            . "_Escolha uma das opções acima para continuar._";

        return $this->telegramService->sendMessage(
            $chatId,
            $text,
        );
    }

    public function planSubscriber($request)
    {
        $chatId = $request['callback_query']['message']['chat']['id'];
        $this->sendProcessingMessage($chatId, 'processando seu pedido');

        $user = $this->returnUserData($chatId);
        $subscription = $user?->subscription;
            
        if (!$subscription || $subscription->status !== 'active') {
            $user = $this->maybeCreateUser($request);
            $plan = Plan::where('id', str_replace('select_plan_', '', $request['callback_query']['data']))->first();
        
            return CreateSubscriptionJob::dispatch($user->telegram_id, $plan->id)->delay(now()->addSeconds(4));
        }
        
        return $this->sendActiveSubscriptionMessage($chatId);
    }

    protected function verifyUser(int $chatId)
    {
        $user = $this->returnUserData($chatId);

        if (!$user || !$user->subscription || $user->subscription->status !== 'active') {
            return $this->sendWelcomeMessage($chatId, $user?->first_name);
        }
        
        return $this->sendActiveSubscriptionMessage($chatId);
    }

    protected function returnUserData(int $chatId): ?TelegramUser
    {
        return TelegramUser::where('telegram_id', $chatId)->first();
    }
    
    protected function maybeCreateUser($request): TelegramUser
    {
        $chatId = (int) $request['callback_query']['message']['chat']['id'];

        $user = $this->returnUserData($chatId);

        $last_name = isset($request['callback_query']['message']['chat']['last_name']) ?  $request['callback_query']['message']['chat']['last_name'] : '';

        if (!$user) {
            $user = $this->telegramUserRepository->createOrUpdate([
                'telegram_id' => $chatId,
                'first_name' => $request['callback_query']['message']['chat']['first_name'],
                'username' => $request['callback_query']['message']['chat']['first_name'] . ' ' . $last_name
            ]);
        }   

        return $user;
    }

    protected function mountKeyboard(array $buttons): array
    {
        return [
            'inline_keyboard' => [$buttons]
        ];
    }

    // ======================
    // ✅ Mensagens padrão
    // ======================

    protected function sendWelcomeMessage(int $chatId, string $name = null): void
    {
        $text = $name
            ? "Olá {$name}! Você ainda não é assinante.\nEscolha uma opção:"
            : "Olá! Seja bem-vindo ao nosso serviço.\nEscolha uma opção:";

        $keyboard = $this->mountKeyboard([
            ['text' => 'Assinar', 'callback_data' => '/assinatura'],
            ['text' => 'Menu', 'callback_data' => '/menu']
        ]);

        $this->telegramService->sendMessage(
            $chatId,
            $text,
            $keyboard
        );
    }

    protected function sendActiveSubscriptionMessage(int $chatId): void
    {
        $user = $this->returnUserData($chatId);
        $subscription = $user->subscription;
        $plan = $subscription->plan;

        $keyboard = $this->mountKeyboard([
            ['text' => 'Reembolso', 'callback_data' => '/reembolso'],
            ['text' => 'Menu', 'callback_data' => '/menu']
        ]);

        $endDate = Carbon::parse($subscription->end_date)->format('d/m/Y');

        $text = "Você já é assinante!\nVencimento: {$endDate} | Tipo de plano: {$plan->plan_type}";

        $this->telegramService->sendMessage(
            $chatId,
            $text,
            $keyboard
        );
    }

    protected function sendProcessingMessage(int $chatId, string $action = 'processando sua solicitação'): void
    {
        $text = "Estamos {$action}. Aguarde até a conclusão ⏳";

        $this->telegramService->sendMessage(
            $chatId,
            $text,
        );
    }

    protected function sendRefundCompletedMessage(int $chatId, int $subscriptionId, int $paymentId, float $amount): void
    {
        $text = "<b>✅ Reembolso concluído</b>\n\n"
            . "O pagamento referente à assinatura <b>#{$subscriptionId}</b> "
            . "foi reembolsado com sucesso.\n\n"
            . "Valor: <b>R$ {$amount}</b>";

        $this->telegramService->sendMessage(
            $chatId,
            $text,
        );
    }
}
