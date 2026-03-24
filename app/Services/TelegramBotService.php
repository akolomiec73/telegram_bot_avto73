<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BotUsers;
use Telegram\Bot\Api;

class TelegramBotService
{
    protected Api $telegram;  // Экземпляр API Telegram

    protected TelegramMessenger $senderMessage;

    public function __construct(Api $telegram, TelegramMessenger $senderMessage)
    {
        $this->telegram = $telegram;
        $this->senderMessage = $senderMessage;
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
                $this->senderMessage->sendMessage($chatId, 'Неизвестная команда.');
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
                $this->senderMessage->sendMessage($chatId, $text);
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
                    $this->senderMessage->sendMessage($chatId, $text);
                } else {
                    $text = TextMessagesService::getCorrectCarYearMessage();
                    $this->senderMessage->sendMessage($chatId, $text);
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
                    $this->senderMessage->sendMessage($chatId, $text);
                } else {
                    $text = TextMessagesService::getCorrectPriceMessage();
                    $this->senderMessage->sendMessage($chatId, $text);
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
                $this->senderMessage->sendMessage($chatId, $text);
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
                        $this->senderMessage->sendMessage($chatId, $text);
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
                            $this->senderMessage->sendMessage($chatId, $text);
                        } else {
                            $temp_adv_row = $user->tempAdv()->first();
                            $text_adv = TextMessagesService::getFullAdvMessage($temp_adv_row, $username);
                            $this->senderMessage->sendMessage($chatId, $text_adv); // предпросмотр для теста

                            $temp_adv_row->delete(); // удаляем строки из таблицы temp
                            /*
                             * тут логика отправки поста в группу
                             * $bot->sendPhoto(-1001647936849, $res_query['add_photo'], $text_add,null,null,false, 'HTML');
                             */

                            /*
                             * тут логика отправки пользователям по фильтрам
                             * $bot->sendPhoto($users_arr[$i]['id_user'], $res_query['add_photo'], $text_add,null,null,false, 'HTML');
                             */
                            $textMessage = TextMessagesService::getFinishMessage();
                            $text = $textMessage['text'];
                            $keyboard = $textMessage['keyboard'];

                            $this->senderMessage->sendMessageWithKeyboard($chatId, $text, $keyboard);
                        }
                    }
                } else {
                    $text = 'Отправьте фотографию, а не файл\текст';
                    $this->senderMessage->sendMessage($chatId, $text);
                }
                break;
            default:
                $this->senderMessage->sendMessage($chatId, "Неопределённый stage: $stage");
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
                $this->senderMessage->sendMessage($chatId, 'category_detail');
                break;
            case 'search_adv':
                $this->senderMessage->sendMessage($chatId, 'search_adv');
                break;
            case 'back_main_menu':
                $this->sendWelcomeMessage($chatId, $username, $message_id, false);
                break;
            default:
                $this->senderMessage->sendMessage($chatId, 'Неизвестный callback.');
        }
    }

    // Отправка приветственного сообщения
    private function sendWelcomeMessage(int $chatId, string $username, int $message_id, bool $isFirstMessage): void
    {
        $textMessage = TextMessagesService::getStartMessage();
        $text = $textMessage['text'];
        $keyboard = $textMessage['keyboard'];

        if ($isFirstMessage) {
            $this->senderMessage->sendMessageWithKeyboard($chatId, $text, $keyboard);
        } else {
            $this->senderMessage->editMessageWithKeyboard($chatId, $message_id, $text, $keyboard);
        }

        BotUsers::updateOrCreate( // метод ищет по условию и обновляет данные в БД если находит, если нет - добавляет
            ['chat_id' => $chatId], // условия поиска
            ['username' => $username, 'stage' => ''] // данные для обновления/создания
        );
    }

    // Отправка сообщения о подаче объявления
    private function sendPostMessage(int $chatId, int $message_id): void
    {
        $textMessage = TextMessagesService::getPostMessage();
        $text = $textMessage['text'];
        $keyboard = $textMessage['keyboard'];
        $this->senderMessage->editMessageWithKeyboard($chatId, $message_id, $text, $keyboard);
    }

    // Отправка сообщения о выборе категории транспорт
    private function sendCategoryCarMessage(int $chatId, int $message_id): void
    {
        $text = TextMessagesService::getCategoryCarMessage();
        $this->senderMessage->editMessage($chatId, $message_id, $text);

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
