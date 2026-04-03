<?php

declare(strict_types=1);

namespace App\Services;

use Telegram\Bot\Api;

readonly class SenderService
{
    /**
     * Сервис отправки сообщений через Telegram BOT SDK
     */
    public function __construct(
        private Api $telegram,
        private string $publicGroupId,
        private LoggerService $logger
    ) {}

    /**
     * Основная точка входа, определяет отправить новое сообщение или изменить отправленное
     */
    public function sendOrEditMessage(int $chatId, string $text, ?int $messageId = null, ?array $keyboard = null): void
    {
        $params = $this->buildRequestParams($chatId, $text, $messageId, $keyboard);
        if ($messageId === null) {
            $this->sendMessage($params);
        } else {
            $this->editMessage($params);
        }
    }

    /**
     * Формирует параметры для отправки сообщения
     */
    private function buildRequestParams(int $chatId, string $text, ?int $messageId = null, ?array $keyboard = null): array
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];
        if ($messageId !== null) {
            $params['message_id'] = $messageId;
        }
        if ($keyboard !== null) {
            $params['reply_markup'] = json_encode($keyboard);
        }

        return $params;
    }

    /**
     * Отправка нового сообщения
     *
     * @throws \Throwable
     */
    private function sendMessage(array $params): void
    {
        try {
            $this->telegram->sendMessage($params);
        } catch (\Throwable $e) {
            $this->logger->error('Telegram sendMessage failed', ['error' => $e->getMessage(), 'params' => $params]);
            throw $e;
        }
    }

    /**
     * Редактирование отправленного сообщения
     *
     * @throws \Throwable
     */
    private function editMessage(array $params): void
    {
        try {
            $this->telegram->editMessageText($params);
        } catch (\Throwable $e) {
            $this->logger->error('Telegram editMessage failed', ['error' => $e->getMessage(), 'params' => $params]);
            throw $e;
        }
    }

    /**
     * Отправка фото с подписью в паблик
     *
     * @throws \Throwable
     */
    public function sendPostInPublic(string $fileId, string $text): void
    {
        try {
            $params = [
                'chat_id' => $this->publicGroupId,
                'photo' => $fileId,
                'caption' => $text,
                'parse_mode' => 'HTML',
            ];
            $this->telegram->sendPhoto($params);
        } catch (\Throwable $e) {
            $this->logger->error('Telegram sendPhoto failed', ['error' => $e->getMessage(), 'params' => $params]);
            throw $e;
        }
    }
}
