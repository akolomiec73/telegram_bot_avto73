<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUsers;
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
        $message_id = $message->getMessageId(); // id message для изменений
        $username = $message->getChat()->getUsername(); // ник пользователя

        // Определяем тип сообщения и вызываем соответствующий обработчик
        if (str_starts_with($text, '/')) {  // Если сообщение — команда (начинается с /)
            $this->handleCommand($chatId, $text, $username, $message_id);
        } elseif ($callbackQuery) {  // обработка нажатий кнопок
            $this->handleCallback($chatId, $text, $username, $callbackQuery, $message_id);
        } else {  // Иначе — обычный текст
            $this->handleText($chatId, $text);
        }

    }

    // Обработчик команд (/start, /help и т. д.)
    private function handleCommand(int $chatId, string $text, string $username, int $message_id): void
    {
        switch ($text) {
            case '/start':
                $this->sendWelcomeMessage($chatId, $username, $message_id, true);
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

    // обработчик callback
    private function handleCallback(int $chatId, string $text, string $username, object $callbackQuery, int $message_id): void
    {
        $data = $callbackQuery->getData();

        switch ($data) {
            case 'post_adv':
                $this->sendPostMessage($chatId, $message_id);
                break;
            case 'category_car':
                $this->sendCategoryCarMessage($chatId, $message_id);
                break;
            case 'category_detail':
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'category_detail',
                ]);
                break;
            case 'search_adv':
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'search_adv',
                ]);
                break;
            case 'back_main_menu':
                $this->sendWelcomeMessage($chatId, $username, $message_id, false);
                break;
            default:
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Неизвестный callback.',
                ]);
        }
    }

    // Отправка приветственного сообщения
    private function sendWelcomeMessage(int $chatId, string $username, int $message_id, bool $isFirstMessage): void
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
                    'callback_data' => 'post_adv',
                ],
                [
                    'text' => 'Найти объявление',
                    'callback_data' => 'search_adv',
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
        if ($isFirstMessage) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($reply_markup),
            ]);
        } else {
            $this->telegram->editMessageText([
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($reply_markup),
            ]);
        }

        BotUsers::updateOrCreate( // метод ищет по условию и обновляет данные в БД если находит, если нет - добавляет
            ['chat_id' => $chatId], // условия поиска
            ['username' => $username, 'stage' => ''] // данные для обновления/создания
        );
    }

    // Отправка сообщения о подаче объявления
    private function sendPostMessage(int $chatId, int $message_id): void
    {
        $text = '❗️ Перед подачей объявления - измените настройки приватности в Телеграме: Конфидициальность, Перессылка сообщений, Для всех! Иначе вам не смогут написать❗️

🚦 <b>Категория</b>

Выберите категорию объявления из представленных.';
        $keyboard = [
            [
                [
                    'text' => '🚗 Транспорт',
                    'callback_data' => 'category_car',
                ],
                [
                    'text' => '⚙️ Запчасти',
                    'callback_data' => 'category_detail',
                ],
            ],
            [
                [
                    'text' => 'Главное меню',
                    'callback_data' => 'back_main_menu',
                ],
            ],
        ];

        $reply_markup = [
            'inline_keyboard' => $keyboard,
        ];
        $this->telegram->editMessageText([
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($reply_markup),
        ]);
    }

    // Отправка сообщения о выборе категории транспорт
    private function sendCategoryCarMessage(int $chatId, int $message_id): void
    {
        $text = '❗❔ <b>Марка и модель авто</b>

Укажите марку и модель вашего авто.

<i>Например Audi RS7 \ Mercedes C180 \ BMW 3 \ Chevrolet Niva \ Ford Focus \ Hyundai Solaris \ Volkswagen Golf \ ВАЗ 2114</i>';

        $this->telegram->editMessageText([
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);

        $stage = 'post_adv_category_car_step1';
        $adv_category = 'Транспорт';

        $user = BotUsers::where('chat_id', $chatId)->first();
        $user->update(['stage' => $stage]);
        $user->tempAdv()->updateOrCreate(
            ['id_bot_user' => $user->id], // условие поиска
            ['adv_category' => $adv_category] // данные для обновления/создания
        );

    }
}
