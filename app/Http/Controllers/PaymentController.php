<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    protected $repository;

    public function __construct(PaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(): JsonResponse
    {
        $payments = $this->repository->all();
        return response()->json($payments);
    }

    public function store(PaymentRequest $request): JsonResponse
    {
        $payment = $this->repository->create($request->validated());
        return response()->json($payment, 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json($payment->load(['order', 'refunds']));
    }

    public function update(PaymentRequest $request, Payment $payment): JsonResponse
    {
        $payment = $this->repository->update($payment, $request->validated());
        return response()->json($payment);
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $this->repository->delete($payment);
        return response()->json(['message' => 'Pagamento removido com sucesso.']);
    }

    /**
     * Buscar pagamentos por status
     */
    public function byStatus(string $status): JsonResponse
    {
        $payments = $this->repository->findByStatus($status);
        return response()->json($payments);
    }
}
