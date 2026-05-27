<?php

use App\Domains\Export\Models\Export;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Horizon metrics snapshot — required for dashboard graphs
Schedule::command('horizon:snapshot')->everyMinute();

// Cleanup old export files (30+ days via Export::prunable())
Schedule::command('model:prune', ['--model' => Export::class])->daily();
