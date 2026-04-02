<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MainService;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    protected MainService $botService;

    public function __construct(MainService $botService)
    {
        $this->botService = $botService;
    }

    public function webhook(Request $request): void
    {
        $this->botService->handleUpdate();
    }
}
