<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlanRequest;
use App\Models\Plan;
use App\Repositories\PlanRepository;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    protected $repository;

    public function __construct(PlanRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Listar todos os planos
     */
    public function index(): JsonResponse
    {
        $plans = $this->repository->all();
        return response()->json($plans);
    }

    /**
     * Criar novo plano
     */
    public function store(PlanRequest $request): JsonResponse
    {
        $plan = $this->repository->create($request->validated());
        return response()->json($plan, 201);
    }

    /**
     * Exibir um plano
     */
    public function show(Plan $plan): JsonResponse
    {
        return response()->json($plan);
    }

    /**
     * Atualizar plano
     */
    public function update(PlanRequest $request, Plan $plan): JsonResponse
    {
        $plan = $this->repository->update($plan, $request->validated());
        return response()->json($plan);
    }

    /**
     * Remover plano
     */
    public function destroy(Plan $plan): JsonResponse
    {
        $this->repository->delete($plan);
        return response()->json(['message' => 'Plano removido com sucesso.']);
    }
}
