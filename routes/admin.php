<?php

use App\Http\Controllers\PlanController;
use Illuminate\Support\Facades\Route;

// PLANOS
Route::prefix('plans')->group(function () {
    Route::get('/', [PlanController::class, 'index']);
    Route::post('/', [PlanController::class, 'store']);
    Route::get('/{plan}', [PlanController::class, 'show']);
    Route::put('/{plan}', [PlanController::class, 'update']);
    Route::delete('/{plan}', [PlanController::class, 'destroy']);
});