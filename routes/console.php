<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('links:check')->dailyAt('06:00')->withoutOverlapping();
Schedule::command('whistleblowing:sync')->everyFiveMinutes()->withoutOverlapping();
