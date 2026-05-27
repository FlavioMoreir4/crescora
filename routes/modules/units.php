<?php

declare(strict_types=1);

use App\Domains\Units\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('units', UnitController::class)
        ->parameters(['units' => 'unit'])
        ->scoped(['unit' => 'slug']);
});
