<?php

use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// TELEGRAM
Route::post('/telegram', [TelegramBotController::class, 'handle']);

// PAGAMENTO
Route::post('/payment', [WebhookController::class, 'handlePayment']);

// ESTORNO
Route::post('/refund', [WebhookController::class, 'handleRefund']);