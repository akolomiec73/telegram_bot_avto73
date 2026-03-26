<?php

/*
 * отправка сообщений в телеграм
 */
declare(strict_types=1);

namespace App\Services;

use Telegram\Bot\Api;

class SenderService
{
    protected Api $telegram;

    private string $publicGroupId;

    public function __construct(Api $telegram, string $publicGroupId)
    {
        $this->telegram = $telegram;
        $this->publicGroupId = $publicGroupId;
    }

    private function prepareMessageParams(int $chatId, string $text, ?int $message_id = null, ?array $keyboard = null): array
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($message_id !== null) {
            $params['message_id'] = $message_id;
        }
        if ($keyboard !== null) {
            $params['reply_markup'] = json_encode($keyboard);
        }

        return $params;
    }

    public function sendMessage(int $chatId, string $text): void
    {
        try {
            $this->telegram->sendMessage($this->prepareMessageParams($chatId, $text));
        } catch (\Exception $e) {
            \Log::error('Telegram sendMessage failed: '.$e->getMessage());
        }
    }

    public function editMessage(int $chatId, int $message_id, string $text): void
    {
        try {
            $this->telegram->editMessageText($this->prepareMessageParams($chatId, $text, $message_id));
        } catch (\Exception $e) {
            \Log::error('Telegram editMessageText failed: '.$e->getMessage());
        }
    }

    public function sendMessageWithKeyboard(int $chatId, string $text, array $keyboard): void
    {
        try {
            $this->telegram->sendMessage($this->prepareMessageParams($chatId, $text, null, $keyboard));
        } catch (\Exception $e) {
            \Log::error('Telegram sendMessageWithKeyboard failed: '.$e->getMessage());
        }
    }

    public function editMessageWithKeyboard(int $chatId, int $message_id, string $text, array $keyboard): void
    {
        try {
            $this->telegram->editMessageText($this->prepareMessageParams($chatId, $text, $message_id, $keyboard));
        } catch (\Exception $e) {
            \Log::error('Telegram editMessageWithKeyboard failed: '.$e->getMessage());
        }
    }

    public function sendPostInPublic(?string $fileId, string $text): void
    {
        if ($fileId !== null) {
            try {
                $this->telegram->sendPhoto([
                    'chat_id' => $this->publicGroupId,
                    'photo' => $fileId,
                    'caption' => $text,
                    'parse_mode' => 'HTML',
                ]);
            } catch (\Exception $e) {
                \Log::error('Telegram sendPhoto failed: '.$e->getMessage());
            }
        } else {
            try {
                $this->telegram->sendMessage([
                    'chat_id' => $this->publicGroupId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                ]);
            } catch (\Exception $e) {
                \Log::error('Telegram sendMessage failed: '.$e->getMessage());
            }
        }
    }
}
