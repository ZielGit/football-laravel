<?php

use Illuminate\Support\Facades\Schedule;
// use Illuminate\Foundation\Inspiring;
// use Illuminate\Support\Facades\Artisan;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

// Cron: refresca la lista de competiciones cada hora (encola un job; respeta el límite de peticiones).
Schedule::command('football:sync-competitions')->hourly()->withoutOverlapping()->onOneServer();
