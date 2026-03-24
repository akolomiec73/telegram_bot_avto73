<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TelegramBotService;  // Сервис для бизнес‑логики
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    protected TelegramBotService $botService; // Экземпляр сервиса

    public function __construct(TelegramBotService $botService)
    {
        $this->botService = $botService; // Сохраняем сервис в свойство класса
    }

    public function webhook(Request $request): void
    {
        $this->botService->handleUpdate();
    }
}
