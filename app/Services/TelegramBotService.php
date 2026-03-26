<?php

/*
 * Основная бизнес-логика
 */
declare(strict_types=1);

namespace App\Services;

use App\Constant\UserStages;
use App\Repositories\Contracts\UserRepositoryInterface;
use Telegram\Bot\Api;

class TelegramBotService
{
    protected Api $telegram;  // Экземпляр API Telegram

    protected SenderService $senderMessage;

    protected UserRepositoryInterface $userRepository;

    protected AdvValidationService $validator;

    public function __construct(
        Api $telegram,
        SenderService $senderMessage,
        UserRepositoryInterface $userRepository,
        AdvValidationService $validator
    ) {
        $this->telegram = $telegram;
        $this->senderMessage = $senderMessage;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
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
    private function handleText(int $chatId, ?string $text): void
    {
        if ($this->validator->validateMainText($text)) {
            $user = $this->userRepository->findByChatId($chatId);
            if ($user !== null) {
                switch ($user->stage) {
                    case UserStages::POST_ADV_STEP1:
                        $mark = $text; // нет валидации, потому что здесь будут массивы по маркам, что бы однообразно и красиво выбирать марку

                        $this->userRepository->updateUser($chatId, ['stage' => UserStages::POST_ADV_STEP2]);
                        $this->userRepository->updateTempAdv($user->id, [
                            'id_bot_user' => $user->id,
                            'adv_car_mark' => $mark,
                        ]);

                        $text = TextMessagesService::getCarYearMessage();
                        $this->senderMessage->sendMessage($chatId, $text);
                        break;
                    case UserStages::POST_ADV_STEP2:
                        if ($this->validator->validateCarYear($text)) {
                            $this->userRepository->updateUser($chatId, ['stage' => UserStages::POST_ADV_STEP3]);
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
                    case UserStages::POST_ADV_STEP3:
                        if ($this->validator->validatePrice($text)) {
                            $this->userRepository->updateUser($chatId, ['stage' => UserStages::POST_ADV_STEP4]);
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
                    case UserStages::POST_ADV_STEP4:
                        if ($this->validator->validateDescription($text)) {
                            $this->userRepository->updateUser($chatId, ['stage' => UserStages::POST_ADV_STEP5]);
                            $this->userRepository->updateTempAdv($user->id, [
                                'id_bot_user' => $user->id,
                                'adv_description' => $text,
                            ]);
                            $text = TextMessagesService::getPhotoMessage();
                            $this->senderMessage->sendMessage($chatId, $text);
                        } else {
                            $text = TextMessagesService::getCorrectDescriptionMessage();
                            $this->senderMessage->sendMessage($chatId, $text);
                        }
                        break;
                    case UserStages::POST_ADV_STEP5:
                        $update = $this->telegram->getWebhookUpdate(); // Получаем обновление от Telegram
                        $message = $update->getMessage();
                        $photo = $message->getPhoto();
                        if ($photo) {
                            $count_minutes = $this->validator->validateTimeLimit($user->date_send_add);
                            if ($count_minutes) {
                                $FileId = $photo[count($photo) - 1]->getFileId();
                                $stage = '';

                                $this->userRepository->updateUser($chatId, [
                                    'stage' => $stage,
                                    'date_send_add' => date('Y-m-d H:i:s'),
                                ]);
                                $this->userRepository->updateTempAdv($user->id, [
                                    'id_bot_user' => $user->id,
                                    'adv_photo' => $FileId,
                                ]);

                                if ($user->username == '') {
                                    $this->userRepository->updateUser($chatId, ['stage' => UserStages::POST_ADV_STEP6]);

                                    $text = TextMessagesService::getContactMessage();
                                    $this->senderMessage->sendMessage($chatId, $text);
                                } else {
                                    $temp_adv_row = $this->userRepository->getAdvRow($chatId);
                                    $text_adv = TextMessagesService::getFullAdvMessage($temp_adv_row, $user->username);

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
                            } else {
                                $text = TextMessagesService::getTimeLimitMessage($count_minutes);
                                $this->senderMessage->sendMessage($chatId, $text);
                            }
                        } else {
                            $text = 'Отправьте фотографию, а не файл\текст';
                            $this->senderMessage->sendMessage($chatId, $text);
                        }
                        break;
                    default:
                        $this->senderMessage->sendMessage($chatId, "Неопределённый stage: $user->stage");
                }
            } else {
                \Log::warning('User not found for chat_id: '.$chatId);
            }
        } else {
            $this->senderMessage->sendMessage($chatId, 'Некорректный текст сообщения');
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

        $adv_category = 'Транспорт';

        $user = $this->userRepository->findByChatId($chatId);
        $this->userRepository->updateUser($chatId, ['stage' => UserStages::POST_ADV_STEP1]);
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

        $adv_category = 'Запчасти';

        $user = $this->userRepository->findByChatId($chatId);
        $this->userRepository->updateUser($chatId, ['stage' => UserStages::POST_ADV_STEP1]);
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

        $adv_category = 'Колёса';

        $user = $this->userRepository->findByChatId($chatId);
        $this->userRepository->updateUser($chatId, ['stage' => UserStages::POST_ADV_STEP1]);
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

        $adv_category = 'Аудио';

        $user = $this->userRepository->findByChatId($chatId);
        $this->userRepository->updateUser($chatId, ['stage' => UserStages::POST_ADV_STEP1]);
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

        $adv_category = 'Инструменты';

        $user = $this->userRepository->findByChatId($chatId);
        $this->userRepository->updateUser($chatId, ['stage' => UserStages::POST_ADV_STEP1]);
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

        $adv_category = 'Другое';

        $user = $this->userRepository->findByChatId($chatId);
        $this->userRepository->updateUser($chatId, ['stage' => UserStages::POST_ADV_STEP1]);
        $this->userRepository->updateTempAdv($user->id, [
            'id_bot_user' => $user->id,
            'adv_category' => $adv_category,
        ]);
    }
}
