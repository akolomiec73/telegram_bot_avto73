<?php

declare(strict_types=1);

namespace App\Services;

use Telegram\Bot\Api;

class TelegramBotService
{
    protected Api $telegram;  // Экземпляр API Telegram

    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    // Основной метод обработки обновлений от Telegram
    public function handleUpdate(): void
    {
        $update = $this->telegram->getWebhookUpdate(); // Получаем обновление от Telegram
        $message = $update->getMessage();
        if (! $message) {
            return;
        }  // Если сообщения нет (например, событие о добавлении в чат), выходим
        $callbackQuery = $update->getCallbackQuery();
        $chatId = $message->getChat()->getId();  // ID чата
        $text = $message->getText();  // Текст сообщения

        // Определяем тип сообщения и вызываем соответствующий обработчик
        if (str_starts_with($text, '/')) {  // Если сообщение — команда (начинается с /)
            $this->handleCommand($chatId, $text);
        } elseif ($callbackQuery) {  // обработка нажатий кнопок
            $this->handleCallback($chatId, $text, $callbackQuery);
        } else {  // Иначе — обычный текст
            $this->handleText($chatId, $text);
        }

    }

    // Обработчик команд (/start, /help и т. д.)
    private function handleCommand(int $chatId, string $text): void
    {
        switch ($text) {
            case '/start':
                $this->sendWelcomeMessage($chatId);
                break;
            default:
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Неизвестная команда.',
                ]);
        }
    }

    // Обработчик обычных текстовых сообщений (не команд)
    private function handleText(int $chatId, string $text): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "Вы сказали: $text",
        ]);

    }

    private function handleCallback(int $chatId, string $text, object $callbackQuery): void
    {
        $data = $callbackQuery->getData();

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "callback data: $data",
        ]);
    }

    // Отправка приветственного сообщения
    private function sendWelcomeMessage(int $chatId): void
    {
        $text = '🚗 <b>Главное меню</b>

Авто Барахолка Ульяновск | <b>avto73ru</b>

Город: <b>Ульяновск</b>
Канал: @avto73ru

Главная наша цель - создание удобной платформы для продажи и покупки б\у авто и запчастей в г.Ульяновск.';
        $keyboard = [
            [
                [
                    'text' => 'Подать объявление',
                    'callback_data' => 'post_ad',
                ],
                [
                    'text' => 'Найти объявление',
                    'callback_data' => 'search_ad',
                ],
            ],
            [
                [
                    'text' => 'Объявления',
                    'url' => 'https://t.me/avto73ru',
                ],
            ],
        ];

        $reply_markup = [
            'inline_keyboard' => $keyboard,
        ];
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($reply_markup),
        ]);
    }
}
