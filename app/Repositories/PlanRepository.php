<?php

namespace App\Repositories;

use App\Models\Plan;

class PlanRepository
{
    protected $model;

    public function __construct(Plan $plan)
    {
        $this->model = $plan;
    }

    /**
     * Listar todos os planos
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Criar um novo plano
     */
    public function create(array $data): Plan
    {
        return $this->model->create($data);
    }

    /**
     * Atualizar um plano existente
     */
    public function update(Plan $plan, array $data): Plan
    {
        $plan->update($data);
        return $plan;
    }

    /**
     * Remover um plano
     */
    public function delete(Plan $plan): bool
    {
        return $plan->delete();
    }

    /**
     * Buscar plano ativo por ID
     */
    public function findActive(int $id): ?Plan
    {
        return $this->model->where('id', $id)->where('active', true)->first();
    }
}
