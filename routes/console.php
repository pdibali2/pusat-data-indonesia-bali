<?php

use App\Console\Commands\ExpirePendingLogins;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('session:expire-pending-logins', function () {
    $this->call(ExpirePendingLogins::class);
})->purpose('Expire pending login requests.');
