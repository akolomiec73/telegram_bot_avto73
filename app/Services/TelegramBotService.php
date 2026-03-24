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
        $photo = $message->getPhoto();

        // Определяем тип сообщения и вызываем соответствующий обработчик
        if ($photo) { // костыль для орбаботки фото, надо исправить
            $this->handleText($chatId, $text);
        } else {
            if (str_starts_with($text, '/')) {  // Если сообщение — команда (начинается с /)
                $this->handleCommand($chatId, $text, $username, $message_id);
            } elseif ($callbackQuery) {  // обработка нажатий кнопок
                $this->handleCallback($chatId, $text, $username, $callbackQuery, $message_id);
            } else {  // Иначе — обычный текст
                $this->handleText($chatId, $text);
            }
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
    private function handleText(int $chatId, ?string $text): void  // Сюда нужно будет функцию валидации
    {
        $user = BotUsers::where('chat_id', $chatId)->first();
        $stage = $user->stage;
        switch ($stage) {
            case 'post_adv_category_car_step1':
                $mark = $text; // здесь потом будут массивы по маркам, что бы однообразно и красиво выбирать марку
                $stage = 'post_adv_car_mark_step2';

                $user = BotUsers::where('chat_id', $chatId)->first();
                $user->update(['stage' => $stage]);
                $user->tempAdv()->updateOrCreate(
                    ['id_bot_user' => $user->id],
                    ['adv_car_mark' => $mark]
                );

                $text = TextMessagesService::getCarYearMessage();
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                ]);
                break;
            case 'post_adv_car_mark_step2':
                $pattern = '#^[0-9]{4}+$#';
                if (preg_match($pattern, $text)) {
                    $stage = 'post_adv_car_year_realise_step3';

                    $user = BotUsers::where('chat_id', $chatId)->first();
                    $user->update(['stage' => $stage]);
                    $user->tempAdv()->updateOrCreate(
                        ['id_bot_user' => $user->id],
                        ['adv_car_year_realise' => $text]
                    );
                    $text = TextMessagesService::getPriceMessage();
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $text,
                        'parse_mode' => 'HTML',
                    ]);
                } else {
                    $text = TextMessagesService::getCorrectCarYearMessage();
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $text,
                        'parse_mode' => 'HTML',
                    ]);
                }
                break;
            case 'post_adv_car_year_realise_step3':
                if (is_numeric($text)) {
                    $stage = 'post_adv_price_step4';

                    $user = BotUsers::where('chat_id', $chatId)->first();
                    $user->update(['stage' => $stage]);
                    $user->tempAdv()->updateOrCreate(
                        ['id_bot_user' => $user->id],
                        ['adv_price' => $text]
                    );
                    $text = TextMessagesService::getDescriptionMessage();
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $text,
                        'parse_mode' => 'HTML',
                    ]);
                } else {
                    $text = TextMessagesService::getCorrectPriceMessage();
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $text,
                        'parse_mode' => 'HTML',
                    ]);
                }
                break;
            case 'post_adv_price_step4':
                $stage = 'post_adv_description_step5';

                $user = BotUsers::where('chat_id', $chatId)->first();
                $user->update(['stage' => $stage]);
                $user->tempAdv()->updateOrCreate(
                    ['id_bot_user' => $user->id],
                    ['adv_description' => $text]
                );
                $text = TextMessagesService::getPhotoMessage();
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                ]);
                break;
            case 'post_adv_description_step5':
                $update = $this->telegram->getWebhookUpdate(); // Получаем обновление от Telegram
                $message = $update->getMessage();
                $photo = $message->getPhoto();
                if ($photo) {
                    $date_now = date('Y-m-d H:i:s');
                    $last_date_send_add = BotUsers::where('chat_id', $chatId)->first()->date_send_add;
                    $diff = strtotime($last_date_send_add) - strtotime($date_now);
                    $min_around = abs(round($diff / 60));

                    if ($min_around < 1) {// 960
                        $text = TextMessagesService::getTimeLimitMessage($min_around);
                        $this->telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $text,
                            'parse_mode' => 'HTML',
                        ]);
                    } else {
                        $max_index = count($photo) - 1;
                        $FileId = $photo[$max_index]->getFileId();
                        $stage = '';

                        $user = BotUsers::where('chat_id', $chatId)->first();
                        $user->update([
                            'stage' => $stage,
                            'date_send_add' => $date_now,
                        ]);
                        $user->tempAdv()->updateOrCreate(
                            ['id_bot_user' => $user->id],
                            ['adv_photo' => $FileId]
                        );

                        $username = BotUsers::where('chat_id', $chatId)->first()->username;
                        if ($username == '') {
                            $stage = 'dop_contact';

                            $user->update(['stage' => $stage]);

                            $text = TextMessagesService::getContactMessage();
                            $this->telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => $text,
                                'parse_mode' => 'HTML',
                            ]);
                        } else {
                            $temp_adv_row = $user->tempAdv()->first();
                            $text_adv = TextMessagesService::getFullAdvMessage($temp_adv_row, $username);
                            $this->telegram->sendMessage([// предпросмотр для теста
                                'chat_id' => $chatId,
                                'text' => $text_adv,
                                'parse_mode' => 'HTML',
                            ]);

                            $temp_adv_row->delete(); // удаляем строки из таблицы temp
                            /*
                             * тут логика отправки поста в группу
                             * $bot->sendPhoto(-1001647936849, $res_query['add_photo'], $text_add,null,null,false, 'HTML');
                             */

                            /*
                             * тут логика отправки пользователям по фильтрам
                             * $bot->sendPhoto($users_arr[$i]['id_user'], $res_query['add_photo'], $text_add,null,null,false, 'HTML');
                             */
                            $text = TextMessagesService::getFinishMessage();
                            $keyboard = [
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
                            $this->telegram->sendMessage([
                                'chat_id' => $chatId,
                                'text' => $text,
                                'parse_mode' => 'HTML',
                                'reply_markup' => json_encode($reply_markup),
                            ]);
                        }
                    }
                } else {
                    $text = 'Отправьте фотографию, а не файл\текст';
                    $this->telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => $text,
                    ]);
                }
                break;
            default:
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Неопределённый stage: $stage",
                ]);
        }
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
        $text = TextMessagesService::getStartMessage();
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
        $text = TextMessagesService::getPostMessage();
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
        $text = TextMessagesService::getCategoryCarMessage();

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
