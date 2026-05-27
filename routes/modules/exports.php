<?php

declare(strict_types=1);

use App\Domains\Export\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('exports')->name('exports.')->group(function () {
    Route::get('/', [ExportController::class, 'index'])->name('index');
    Route::post('/', [ExportController::class, 'store'])->name('store');
    Route::get('{export}/download', [ExportController::class, 'download'])->name('download');
});
