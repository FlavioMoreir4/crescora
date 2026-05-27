<?php

declare(strict_types=1);

use App\Domains\Billing\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('billing')->name('billing.')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index'])->name('index');
    Route::post('/subscribe', [SubscriptionController::class, 'store'])->name('subscribe');
    Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
    Route::get('/pix', [SubscriptionController::class, 'pixPayment'])->name('pix');
});

Route::post('billing/webhook', [SubscriptionController::class, 'webhook'])
    ->name('billing.webhook')
    ->withoutMiddleware(['auth']);
