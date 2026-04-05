<?php

declare(strict_types=1);

namespace App\Services\Flow;

use App\Services\LoggerService;
use App\Services\MessageService;
use App\Services\RepositoryService;
use App\Services\SenderService;
use App\Services\ValidationService;

class AdvPostingFlow
{
    protected LoggerService $logger;

    protected SenderService $senderMessage;

    protected RepositoryService $repository;

    protected ValidationService $validator;

    public function __construct(
        LoggerService $logger,
        SenderService $senderMessage,
        RepositoryService $repository,
        ValidationService $validator,
        private MessageService $messageService
    ) {
        $this->logger = $logger;
        $this->senderMessage = $senderMessage;
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * Отправка приветственного сообщения
     */
    public function sendWelcomeMessage(int $chatId, ?string $username, int $message_id, bool $isFirstMessage): void
    {
        $textMessage = $this->messageService->getStartMessage();
        $text = $textMessage['text'];
        $keyboard = $textMessage['keyboard'];
        if ($isFirstMessage) {
            $this->senderMessage->sendOrEditMessage($chatId, $text, null, $keyboard);
        } else {
            $this->senderMessage->sendOrEditMessage($chatId, $text, $message_id, $keyboard);
        }
        $this->repository->updateUser($chatId, '', $username);
        $this->logger->debug('Send welcome message to user', ['chat_id' => $chatId]);
    }

    /**
     * Публикация объявления
     */
    public function finishAdv(int $chatId): void
    {
        $user = $this->repository->getUserDatePost($chatId);
        $count_minutes = $this->getCountMinutes($user->date_send_add);
        if ($count_minutes >= 120) {
            $this->repository->updateUserDatePost($chatId, date('Y-m-d H:i:s'));
            $temp_adv_row = $this->repository->getAdvRow($chatId);
            $text_adv = $this->messageService->getFullAdvMessage($temp_adv_row, $user->username);
            $this->senderMessage->sendPostInPublic($temp_adv_row->adv_photo, $text_adv);
            /*
             * тут будет логика отправки пользователям по фильтрам
             *
             */
            $textMessage = $this->messageService->getFinishMessage();
            $text = $textMessage['text'];
            $keyboard = $textMessage['keyboard'];
            $this->senderMessage->sendOrEditMessage($chatId, $text, null, $keyboard);
            $this->logger->debug('User successful post adv', ['chat_id' => $chatId, 'text_adv' => $text_adv]);
        } else {
            $text = $this->messageService->getTimeLimitMessage($count_minutes);
            $this->senderMessage->sendOrEditMessage($chatId, $text);
            $this->logger->debug('User have time limit to post', ['chat_id' => $chatId, 'last_date_post' => $user->date_send_add]);
        }
    }

    /**
     * Подсчет кол-ва минут после последней публикации
     */
    private function getCountMinutes(?string $date_post): int
    {
        if ($date_post === null) {
            return 120;
        }
        $date_now = date('Y-m-d H:i:s');
        $diff = strtotime($date_now) - strtotime($date_post);

        return (int) abs(round($diff / 60));
    }
}
