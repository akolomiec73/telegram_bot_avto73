<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\LoggerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $level;

    public $message;

    public $context;

    /**
     * Create a new job instance.
     */
    public function __construct($level, $message, $context)
    {
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
    }

    /**
     * Execute the job.
     */
    public function handle(LoggerService $logger): void
    {
        $logger->log($this->level, $this->message, $this->context, false);
    }
}
