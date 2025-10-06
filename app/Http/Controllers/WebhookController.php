<?php

namespace App\Http\Controllers;

use App\Repositories\WebhookRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $repository;

    public function __construct(WebhookRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handleRefund(Request $request): JsonResponse
    {
        $order = $this->repository->handleRefund($request->all());
        return response()->json([
            'message' => 'Webhook de estorno processado com sucesso.',
            'order' => $order
        ], 201);
    }

    public function handlePayment(Request $request): JsonResponse
    {
        $order = $this->repository->handlePayment($request->all());
        return response()->json([
            'message' => 'Webhook de pagamento com sucesso.',
            'order' => $order
        ], 201);
    }
}
