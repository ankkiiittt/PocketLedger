<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('recurring:process')->dailyAt('00:10')->withoutOverlapping();
