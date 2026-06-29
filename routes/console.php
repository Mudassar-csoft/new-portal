<?php

use App\Console\Commands\ImportLegacyOldCrm;
use App\Console\Commands\WipeDataKeepAdmins;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

app(ConsoleKernel::class)->addCommands([
    ImportLegacyOldCrm::class,
    WipeDataKeepAdmins::class,
]);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
