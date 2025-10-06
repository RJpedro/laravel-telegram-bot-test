<?php

namespace App\Repositories;

use App\Models\Subscription;

class SubscriptionRepository
{
    protected $model;

    public function __construct(Subscription $subscription)
    {
        $this->model = $subscription;
    }

    /**
     * Listar todas as assinaturas
     */
    public function all()
    {
        return $this->model->with(['user', 'plan'])->get();
    }

    /**
     * Criar nova assinatura
     */
    public function create(array $data): Subscription
    {
        return $this->model->create($data);
    }

    /**
     * Atualizar assinatura existente
     */
    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->update($data);
        return $subscription;
    }

    /**
     * Remover assinatura
     */
    public function delete(Subscription $subscription): bool
    {
        return $subscription->delete();
    }

    /**
     * Buscar assinaturas ativas de um usuário
     */
    public function activeByUser(int $userId)
    {
        return $this->model->where('user_id', $userId)
                           ->where('status', 'active')
                           ->with('plan')
                           ->first();
    }
}
