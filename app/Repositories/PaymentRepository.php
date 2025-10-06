<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository
{
    protected $model;

    public function __construct(Payment $payment)
    {
        $this->model = $payment;
    }

    public function all()
    {
        return $this->model->with(['refunds'])->get();
    }

    public function create(array $data): Payment
    {
        return $this->model->create($data);
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);
        return $payment;
    }

    public function delete(Payment $payment): bool
    {
        return $payment->delete();
    }

    public function findByStatus(string $status)
    {
        return $this->model->where('status', $status)->get();
    }
}
