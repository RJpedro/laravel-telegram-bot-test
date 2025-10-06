<?php

namespace App\Http\Controllers;

use App\Http\Requests\RefundRequest;
use App\Models\Refund;
use App\Repositories\RefundRepository;
use Illuminate\Http\JsonResponse;

class RefundController extends Controller
{
    protected $repository;

    public function __construct(RefundRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Listar todos os reembolsos.
     */
    public function index(): JsonResponse
    {
        $refunds = $this->repository->all();
        return response()->json($refunds);
    }

    /**
     * Criar novo reembolso.
     */
    public function store(RefundRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Verifica se o pagamento já possui reembolso
        if ($this->repository->existsForPayment($data['payment_id'])) {
            return response()->json([
                'message' => 'Este pagamento já possui um reembolso registrado.'
            ], 409);
        }

        $refund = $this->repository->create($data);
        return response()->json($refund, 201);
    }

    /**
     * Exibir um reembolso.
     */
    public function show(Refund $refund): JsonResponse
    {
        return response()->json($refund->load('payment'));
    }

    /**
     * Atualizar um reembolso.
     */
    public function update(RefundRequest $request, Refund $refund): JsonResponse
    {
        $refund = $this->repository->update($refund, $request->validated());
        return response()->json($refund);
    }

    /**
     * Remover um reembolso.
     */
    public function destroy(Refund $refund): JsonResponse
    {
        $this->repository->delete($refund);
        return response()->json(['message' => 'Reembolso removido com sucesso.']);
    }
}
