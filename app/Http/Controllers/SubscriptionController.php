<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionRequest;
use App\Models\Subscription;
use App\Repositories\SubscriptionRepository;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    protected $repository;

    public function __construct(SubscriptionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(): JsonResponse
    {
        $subscriptions = $this->repository->all();
        return response()->json($subscriptions);
    }

    public function store(SubscriptionRequest $request): JsonResponse
    {
        $subscription = $this->repository->create($request->validated());
        return response()->json($subscription, 201);
    }

    public function show(Subscription $subscription): JsonResponse
    {
        return response()->json($subscription->load(['user', 'plan']));
    }

    public function update(SubscriptionRequest $request, Subscription $subscription): JsonResponse
    {
        $subscription = $this->repository->update($subscription, $request->validated());
        return response()->json($subscription);
    }

    public function destroy(Subscription $subscription): JsonResponse
    {
        $this->repository->delete($subscription);
        return response()->json(['message' => 'Assinatura removida com sucesso.']);
    }

    /**
     * Buscar assinatura ativa de um usuário
     */
    public function activeByUser(int $userId): JsonResponse
    {
        $subscription = $this->repository->activeByUser($userId);
        return response()->json($subscription);
    }
}
