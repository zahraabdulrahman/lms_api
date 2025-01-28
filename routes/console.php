<?php

use App\Console\Commands\FetchBooks;
use Illuminate\Support\Facades\Schedule;

// scheduled to get books daily
Schedule::command(FetchBooks::class)->daily();
