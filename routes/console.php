<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule notifikasi reminder pengembalian setiap hari jam 09:00
Schedule::command('notifikasi:reminder-pengembalian')
    ->dailyAt('09:00')
    ->timezone('Asia/Jakarta');
