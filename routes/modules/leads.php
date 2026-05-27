<?php

declare(strict_types=1);

use App\Domains\Leads\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('leads', LeadController::class)
        ->parameters(['leads' => 'lead']);
});
