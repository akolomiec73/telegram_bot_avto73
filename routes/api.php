<?php

declare(strict_types=1);

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook', [TelegramBotController::class, 'webhook']);
