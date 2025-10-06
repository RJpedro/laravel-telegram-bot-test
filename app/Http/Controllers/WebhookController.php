<?php

namespace App\Http\Controllers;

use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function handleRefund(Request $request): JsonResponse
    {
        $order = $this->webhookService->handleRefund($request->all());
        return response()->json([
            'message' => 'Webhook de estorno processado com sucesso.',
            'order' => $order
        ], 201);
    }

    public function handlePayment(Request $request): JsonResponse
    {
        $order = $this->webhookService->handlePayment($request->all());
        return response()->json([
            'message' => 'Webhook de pagamento com sucesso.',
            'order' => $order
        ], 201);
    }
}
