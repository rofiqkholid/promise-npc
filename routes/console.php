<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengecekan otomatis untuk proses produksi yang melebihi batas waktu (overdue)
// Mengirimkan eskalasi email ke Manager & Assman departemen terkait 2 kali sehari:
// 1. Pukul 08:00 pagi (pembukaan hari kerja)
// 2. Pukul 15:00 sore (follow-up sebelum akhir jam kerja)
Schedule::command('npc:check-overdue-processes')->dailyAt('08:00');
Schedule::command('npc:check-overdue-processes')->dailyAt('15:00');

