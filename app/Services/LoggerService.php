<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\LogJob;
use Illuminate\Support\Facades\Log;

class LoggerService
{
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Создаёт job для асинхронного логирования
     */
    public function log(string $level, string $message, array $context, bool $dispatch = true): void
    {
        if ($dispatch) {
            dispatch(new LogJob($level, $message, $context))->onQueue('logs');

            return;
        }
        Log::channel('single')->$level($message, $context);
    }
}
