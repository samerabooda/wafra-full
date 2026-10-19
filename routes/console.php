<?php
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', fn() => $this->comment(Inspiring::quote()))->purpose('Display inspiring quote');

// (Removed) The hourly cards:rebuild-summaries job is gone — dashboards/reports now
// aggregate live from commission_cards, so the card_summaries table is no longer used.
