<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:database --notify')
    ->dailyAt('02:00')
    ->appendOutputTo(storage_path('logs/backup.log'));

Schedule::command('wa:terlambat')
    ->dailyAt('09:00')
    ->appendOutputTo(storage_path('logs/wa-terlambat.log'));
