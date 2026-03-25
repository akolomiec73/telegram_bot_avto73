<?php

/*
 * отправка сообщений в телеграм
 */
declare(strict_types=1);

namespace App\Services;

use Telegram\Bot\Api;

class TelegramMessenger
{
    protected Api $telegram;

    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function sendMessage(int $chatId, string $text): mixed
    {
        return $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    public function editMessage(int $chatId, int $message_id, string $text): mixed
    {
        return $this->telegram->editMessageText([
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    public function sendMessageWithKeyboard(int $chatId, string $text, array $keyboard): mixed
    {
        return $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($keyboard),
        ]);
    }

    public function editMessageWithKeyboard(int $chatId, int $message_id, string $text, array $keyboard): mixed
    {
        return $this->telegram->editMessageText([
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($keyboard),
        ]);
    }

    public function sendMessageInPublic(string $FileId, string $text): mixed
    {
        $groupId = -1001692673051;
        return $this->telegram->sendPhoto([
            'chat_id' => $groupId,
            'photo' => $FileId,
            'caption' => $text,
            'parse_mode' => 'HTML',
        ]);
    }
}
