<?php

declare(strict_types=1);

use App\Domains\Notifications\Controllers\NotificationsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationsController::class, 'index'])->name('index');
    Route::post('{id}/read', [NotificationsController::class, 'markAsRead'])->name('markAsRead');
    Route::post('mark-all-read', [NotificationsController::class, 'markAllAsRead'])->name('markAllAsRead');
});

Route::middleware(['auth'])->get('notifications/unread-count', [NotificationsController::class, 'unreadCount'])
    ->name('notifications.unreadCount');
