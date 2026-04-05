<?php

declare(strict_types=1);

namespace App\Services\Flow;

use App\Jobs\NotificationJob;
use App\Services\LoggerService;
use App\Services\MessageService;
use App\Services\RepositoryService;
use App\Services\SenderService;

readonly class AdvPostingFlow
{
    public function __construct(
        private LoggerService $logger,
        private SenderService $senderMessage,
        private RepositoryService $repository,
        private MessageService $messageService,
        private int $timeLimitToPost,
    ) {}

    /**
     * Отправка приветственного сообщения
     */
    public function sendWelcomeMessage(int $chatId, ?string $username, int $message_id, bool $isFirstMessage): void
    {
        $textMessage = $this->messageService->getStartMessage();
        if ($isFirstMessage) {
            $this->senderMessage->sendOrEditMessage($chatId, $textMessage['text'], null, $textMessage['keyboard']);
        } else {
            $this->senderMessage->sendOrEditMessage($chatId, $textMessage['text'], $message_id, $textMessage['keyboard']);
        }
        $this->repository->updateUser($chatId, '', $username);
        $this->logger->debug('Send welcome message to user', ['chat_id' => $chatId]);
    }

    /**
     * Публикация объявления
     */
    public function finishAdv(int $chatId): bool
    {
        $user = $this->repository->getUserDatePost($chatId);
        $count_minutes = $this->getCountMinutes($user->date_send_add);
        if ($count_minutes >= $this->timeLimitToPost) {
            $tempAdvRow = $this->repository->getAdvRow($chatId);
            $textAdv = $this->messageService->getFullAdvMessage($tempAdvRow, $user->username);
            $this->senderMessage->sendPostInPublic($tempAdvRow->adv_photo, $textAdv);
            $this->repository->updateUserDatePost($chatId, date('Y-m-d H:i:s'));
            dispatch(new NotificationJob($tempAdvRow, $textAdv))->onQueue('notification');
            $textMessage = $this->messageService->getFinishMessage();
            $this->senderMessage->sendOrEditMessage($chatId, $textMessage['text'], null, $textMessage['keyboard']);
            $this->logger->debug('User successful post adv', ['chat_id' => $chatId, 'text_adv' => $textAdv]);

            return true;
        } else {
            $text = $this->messageService->getTimeLimitMessage($count_minutes);
            $this->senderMessage->sendOrEditMessage($chatId, $text);
            $this->logger->debug('User have time limit to post', ['chat_id' => $chatId, 'last_date_post' => $user->date_send_add]);

            return false;
        }
    }

    /**
     * Подсчет кол-ва минут после последней публикации
     */
    private function getCountMinutes(?string $date_post): int
    {
        if ($date_post === null) {
            return $this->timeLimitToPost;
        }
        $date_now = date('Y-m-d H:i:s');
        $diff = strtotime($date_now) - strtotime($date_post);

        return (int) abs(round($diff / 60));
    }
}
