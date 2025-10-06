<?php

namespace App\Repositories;

use App\Models\Refund;

class RefundRepository
{
    protected $model;

    public function __construct(Refund $refund)
    {
        $this->model = $refund;
    }

    /**
     * Retorna todos os reembolsos com seu pagamento relacionado.
     */
    public function all()
    {
        return $this->model->with('payment')->get();
    }

    /**
     * Cria um novo reembolso.
     */
    public function create(array $data): Refund
    {
        return $this->model->create($data);
    }

    /**
     * Atualiza um reembolso existente.
     */
    public function update(Refund $refund, array $data): Refund
    {
        $refund->update($data);
        return $refund;
    }

    /**
     * Remove um reembolso.
     */
    public function delete(Refund $refund): bool
    {
        return $refund->delete();
    }

    /**
     * Busca reembolsos por status (pending, processing, completed, failed).
     */
    public function findByStatus(string $status)
    {
        return $this->model->where('status', $status)->with('payment')->get();
    }

    /**
     * Verifica se um pagamento já possui reembolso associado.
     */
    public function existsForPayment(int $paymentId): bool
    {
        return $this->model->where('payment_id', $paymentId)->exists();
    }

    /**
     * Busca um reembolso específico pelo ID.
     */
    public function findById(int $id): ?Refund
    {
        return $this->model->with('payment')->find($id);
    }
}
