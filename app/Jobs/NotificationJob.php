<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\TempAdvUser;
use App\Repositories\DatabaseRepository;
use App\Services\SenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Задача на отправку поста, юзерам подходящим по фильтрам
 */
class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private TempAdvUser $tempAdv;

    private string $textAdv;

    public function __construct(TempAdvUser $tempAdv, string $textAdv)
    {
        $this->tempAdv = $tempAdv;
        $this->textAdv = $textAdv;
    }

    /**
     * Execute the job.
     */
    public function handle(DatabaseRepository $dbRepo, SenderService $sender): void
    {
        $usersList = $dbRepo->getUsersWithFilters($this->tempAdv);
        $fullText = 'Новое объявление по вашим фильтрам

'.$this->textAdv;
        foreach ($usersList as $user) {
            $sender->sendOrEditMessage($user->chat_id, $fullText);
        }
    }
}
