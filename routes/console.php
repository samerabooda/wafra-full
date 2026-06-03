<?php
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', fn() => $this->comment(Inspiring::quote()))->purpose('Display inspiring quote');

// Rebuild the pre-aggregated card summaries every hour so reports/dashboards
// always read fast, pre-computed numbers (never scan the raw cards table).
Schedule::command('cards:rebuild-summaries')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
