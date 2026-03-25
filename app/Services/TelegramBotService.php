<?php

/*
 * Основная бизнес-логика
 */
declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Telegram\Bot\Api;

class TelegramBotService
{
    protected Api $telegram;  // Экземпляр API Telegram

    protected TelegramMessenger $senderMessage;

    protected UserRepositoryInterface $userRepository;

    public function __construct(Api $telegram, TelegramMessenger $senderMessage, UserRepositoryInterface $userRepository)
    {
        $this->telegram = $telegram;
        $this->senderMessage = $senderMessage;
        $this->userRepository = $userRepository;
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
        $chatId = $message->getChat()->getId();
        $text = $message->getText();
        $message_id = $message->getMessageId(); // id message для изменений
        $username = $message->getChat()->getUsername();
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

    // Обработчик команд
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

    // Обработчик обычных текстовых сообщений
    private function handleText(int $chatId, ?string $text): void  // Сюда нужно будет функцию валидации
    {
        $user = $this->userRepository->findByChatId($chatId);
        switch ($user->stage) {
            case 'post_adv_category_car_step1':
                $mark = $text; // здесь потом будут массивы по маркам, что бы однообразно и красиво выбирать марку
                $stage = 'post_adv_car_mark_step2';

                $this->userRepository->updateUser($chatId, ['stage' => $stage]);
                $this->userRepository->updateTempAdv($user->id, [
                    'id_bot_user' => $user->id,
                    'adv_car_mark' => $mark,
                ]);

                $text = TextMessagesService::getCarYearMessage();
                $this->senderMessage->sendMessage($chatId, $text);
                break;
            case 'post_adv_car_mark_step2':
                $pattern = '#^[0-9]{4}+$#';
                if (preg_match($pattern, $text)) {
                    $stage = 'post_adv_car_year_realise_step3';

                    $this->userRepository->updateUser($chatId, ['stage' => $stage]);
                    $this->userRepository->updateTempAdv($user->id, [
                        'id_bot_user' => $user->id,
                        'adv_car_year_realise' => $text,
                    ]);
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

                    $this->userRepository->updateUser($chatId, ['stage' => $stage]);
                    $this->userRepository->updateTempAdv($user->id, [
                        'id_bot_user' => $user->id,
                        'adv_price' => $text,
                    ]);
                    $text = TextMessagesService::getDescriptionMessage();
                    $this->senderMessage->sendMessage($chatId, $text);
                } else {
                    $text = TextMessagesService::getCorrectPriceMessage();
                    $this->senderMessage->sendMessage($chatId, $text);
                }
                break;
            case 'post_adv_price_step4':
                $stage = 'post_adv_description_step5';

                $this->userRepository->updateUser($chatId, ['stage' => $stage]);
                $this->userRepository->updateTempAdv($user->id, [
                    'id_bot_user' => $user->id,
                    'adv_description' => $text,
                ]);
                $text = TextMessagesService::getPhotoMessage();
                $this->senderMessage->sendMessage($chatId, $text);
                break;
            case 'post_adv_description_step5':
                $update = $this->telegram->getWebhookUpdate(); // Получаем обновление от Telegram
                $message = $update->getMessage();
                $photo = $message->getPhoto();
                if ($photo) {
                    $date_now = date('Y-m-d H:i:s');
                    $last_date_send_add = $user->date_send_add;
                    $diff = strtotime($last_date_send_add) - strtotime($date_now);
                    $min_around = abs(round($diff / 60));

                    if ($min_around < 1) {// 960
                        $text = TextMessagesService::getTimeLimitMessage($min_around);
                        $this->senderMessage->sendMessage($chatId, $text);
                    } else {
                        $max_index = count($photo) - 1;
                        $FileId = $photo[$max_index]->getFileId();
                        $stage = '';

                        $this->userRepository->updateUser($chatId, [
                            'stage' => $stage,
                            'date_send_add' => $date_now,
                        ]);
                        $this->userRepository->updateTempAdv($user->id, [
                            'id_bot_user' => $user->id,
                            'adv_photo' => $FileId,
                        ]);

                        $username = $user->username;
                        if ($username == '') {
                            $stage = 'dop_contact';

                            $this->userRepository->updateUser($chatId, ['stage' => $stage]);

                            $text = TextMessagesService::getContactMessage();
                            $this->senderMessage->sendMessage($chatId, $text);
                        } else {
                            $temp_adv_row = $this->userRepository->getAdvRow($chatId);
                            $text_adv = TextMessagesService::getFullAdvMessage($temp_adv_row, $username);

                            $this->senderMessage->sendMessageInPublic($FileId, $text_adv); // отправка поста в паблик

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
                $this->senderMessage->sendMessage($chatId, "Неопределённый stage: $user->stage");
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
                $this->sendCategoryDetailMessage($chatId, $message_id);
                break;
            case 'category_detail_detail':
                $this->sendCategoryDetailDetailMessage($chatId, $message_id);
                break;
            case 'category_detail_wheels':
                $this->sendCategoryDetailWheelsMessage($chatId, $message_id);
                break;
            case 'category_detail_audio':
                $this->sendCategoryDetailAudioMessage($chatId, $message_id);
                break;
            case 'category_detail_tools':
                $this->sendCategoryDetailToolsMessage($chatId, $message_id);
                break;
            case 'category_detail_others':
                $this->sendCategoryDetailOthersMessage($chatId, $message_id);
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

        $this->userRepository->updateUser($chatId, [
            'username' => $username,
            'stage' => '',
        ]);
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

        $user = $this->userRepository->findByChatId($chatId);
        $this->userRepository->updateUser($chatId, ['stage' => $stage]);
        $this->userRepository->updateTempAdv($user->id, [
            'id_bot_user' => $user->id,
            'adv_category' => $adv_category,
        ]);
    }

    // Отправка сообщения по кнопке "Запчасти"
    private function sendCategoryDetailMessage(int $chatId, int $message_id): void
    {
        $textMessage = TextMessagesService::getCategoryDetailMessage();
        $text = $textMessage['text'];
        $keyboard = $textMessage['keyboard'];
        $this->senderMessage->editMessageWithKeyboard($chatId, $message_id, $text, $keyboard);
    }

    // Отправка сообщения при выборе Запчасти-Запчасти
    private function sendCategoryDetailDetailMessage(int $chatId, int $message_id): void
    {
        $text = TextMessagesService::getCategoryDetailDetailMessage();
        $this->senderMessage->editMessage($chatId, $message_id, $text);

        $stage = 'post_adv_category_detail_step1';
        $adv_category = 'Запчасти';

        $user = $this->userRepository->findByChatId($chatId);
        $this->userRepository->updateUser($chatId, ['stage' => $stage]);
        $this->userRepository->updateTempAdv($user->id, [
            'id_bot_user' => $user->id,
            'adv_category' => $adv_category,
        ]);
    }

    // Отправка сообщения при выборе Запчасти-Колёса
    private function sendCategoryDetailWheelsMessage(int $chatId, int $message_id): void
    {
        $text = TextMessagesService::getCategoryDetailWheelsMessage();
        $this->senderMessage->editMessage($chatId, $message_id, $text);

        $stage = 'post_adv_category_detail_step1';
        $adv_category = 'Колёса';

        $user = $this->userRepository->findByChatId($chatId);
        $this->userRepository->updateUser($chatId, ['stage' => $stage]);
        $this->userRepository->updateTempAdv($user->id, [
            'id_bot_user' => $user->id,
            'adv_category' => $adv_category,
        ]);
    }

    // Отправка сообщения при выборе Запчасти-Аудио
    private function sendCategoryDetailAudioMessage(int $chatId, int $message_id): void
    {
        $text = TextMessagesService::getCategoryDetailAudioMessage();
        $this->senderMessage->editMessage($chatId, $message_id, $text);

        $stage = 'post_adv_category_detail_step1';
        $adv_category = 'Аудио';

        $user = $this->userRepository->findByChatId($chatId);
        $this->userRepository->updateUser($chatId, ['stage' => $stage]);
        $this->userRepository->updateTempAdv($user->id, [
            'id_bot_user' => $user->id,
            'adv_category' => $adv_category,
        ]);
    }

    // Отправка сообщения при выборе Запчасти-Инструменты
    private function sendCategoryDetailToolsMessage(int $chatId, int $message_id): void
    {
        $text = TextMessagesService::getCategoryDetailToolsMessage();
        $this->senderMessage->editMessage($chatId, $message_id, $text);

        $stage = 'post_adv_category_detail_step1';
        $adv_category = 'Инструменты';

        $user = $this->userRepository->findByChatId($chatId);
        $this->userRepository->updateUser($chatId, ['stage' => $stage]);
        $this->userRepository->updateTempAdv($user->id, [
            'id_bot_user' => $user->id,
            'adv_category' => $adv_category,
        ]);
    }

    // Отправка сообщения при выборе Запчасти-Другое
    private function sendCategoryDetailOthersMessage(int $chatId, int $message_id): void
    {
        $text = TextMessagesService::getCategoryDetailOthersMessage();
        $this->senderMessage->editMessage($chatId, $message_id, $text);

        $stage = 'post_adv_category_detail_step1';
        $adv_category = 'Другое';

        $user = $this->userRepository->findByChatId($chatId);
        $this->userRepository->updateUser($chatId, ['stage' => $stage]);
        $this->userRepository->updateTempAdv($user->id, [
            'id_bot_user' => $user->id,
            'adv_category' => $adv_category,
        ]);
    }
}
