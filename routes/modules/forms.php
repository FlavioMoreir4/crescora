<?php

declare(strict_types=1);

use App\Domains\Forms\Controllers\FormController;
use App\Domains\Forms\Controllers\PublicFormController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('forms', FormController::class)
        ->parameters(['forms' => 'form'])
        ->scoped(['form' => 'slug']);
});

Route::prefix('forms/{teamSlug}/{unitSlug}')->group(function () {
    Route::get('{formSlug}', [PublicFormController::class, 'show'])->name('public.forms.show');
    Route::post('{formSlug}', [PublicFormController::class, 'submit'])->name('public.forms.submit');
});
