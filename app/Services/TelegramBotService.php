<?php

/*
 * Основная бизнес-логика
 */
declare(strict_types=1);

namespace App\Services;

use App\Constant\UserStages;
use Telegram\Bot\Api;

class TelegramBotService
{
    protected Api $telegram;

    protected SenderService $senderMessage;

    protected AdvValidationService $validator;

    protected RepositoryService $repository;

    public function __construct(
        Api $telegram,
        SenderService $senderMessage,
        AdvValidationService $validator,
        RepositoryService $repository
    ) {
        $this->telegram = $telegram;
        $this->senderMessage = $senderMessage;
        $this->validator = $validator;
        $this->repository = $repository;
    }

    // Основной метод обработки обновлений от Telegram
    public function handleUpdate(): void
    {
        $update = $this->telegram->getWebhookUpdate(); // Получаем обновление от Telegram
        $message = $update->getMessage();
        $text = $message->getText();
        $chatId = $message->getChat()->getId();
        $message_id = $message->getMessageId(); // id message для изменений
        $username = $message->getChat()->getUsername() ?? 'unknown';

        if ($update->getCallbackQuery()) {  // обработка нажатий кнопок
            $this->handleCallback($chatId, $username, $update->getCallbackQuery(), $message_id);
        } elseif ($text !== null && str_starts_with($text, '/')) {  // Если сообщение — команда (начинается с /)
            $this->handleCommand($chatId, $text, $username, $message_id);
        } elseif ($text !== null || $message->getPhoto()) { // Иначе — обычный текст + обработка фото
            $this->handleText($chatId, $text);
        } else { // Если попытаются отправить файл, стикер, видео
            $this->senderMessage->sendMessage($chatId, 'Бот принимает только текстовые сообщения и команды.');
            \Log::debug('Пользователь отправил файл\стикер\видео', ['chat_id' => $chatId, 'message_id' => $message_id]);
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
            $user = $this->repository->getUser($chatId);
            if ($user !== null) {
                switch ($user['stage']) {
                    case UserStages::POST_ADV_STEP1: // получаем марку(название)
                        $this->handleStage($text, UserStages::POST_ADV_STEP2, 'adv_car_mark', $chatId);
                        break;
                    case UserStages::POST_ADV_STEP2: // получаем год выпуска
                        $this->handleStage($text, UserStages::POST_ADV_STEP3, 'adv_car_year_realise', $chatId);
                        break;
                    case UserStages::POST_ADV_STEP3: // получаем цену
                    case UserStages::POST_ADV_DETAIL_STEP2:
                        $this->handleStage($text, UserStages::POST_ADV_STEP4, 'adv_price', $chatId);
                        break;
                    case UserStages::POST_ADV_STEP4: // получаем описание
                        $this->handleStage($text, UserStages::POST_ADV_STEP5, 'adv_description', $chatId);
                        break;
                    case UserStages::POST_ADV_STEP5: // получаем фотку
                        $photo = $this->telegram->getWebhookUpdate()->getMessage()->getPhoto();
                        $fileId = $photo[count($photo) - 1]->getFileId();
                        if ($user['username']) {
                            $this->handleStage($fileId, '', 'adv_photo', $chatId);
                        } else {
                            $this->handleStage($fileId, UserStages::POST_ADV_STEP6, 'adv_photo', $chatId);
                        }
                        break;
                    case UserStages::POST_ADV_STEP6: // получаем доп контакты
                        $this->handleStage($text, UserStages::POST_ADV_STEP7, 'adv_extra_contact', $chatId);
                        break;
                    case UserStages::POST_ADV_DETAIL_STEP1: // получаем Название запчасти
                        $this->handleStage($text, UserStages::POST_ADV_DETAIL_STEP2, 'adv_car_mark', $chatId);
                        break;
                    default:
                        $this->senderMessage->sendMessage($chatId, 'Неопределённый stage');
                }
            } else {
                \Log::warning('User не найден: '.$chatId);
            }
        } else {
            $this->senderMessage->sendMessage($chatId, 'Некорректный текст сообщения');
            \Log::warning("User: $chatId отправил некорректный текст: $text");
        }
    }

    // обработчик callback
    private function handleCallback(int $chatId, string $username, object $callbackQuery, int $message_id): void
    {
        switch ($callbackQuery->getData()) {
            case UserStages::BUTTON_POST_ADV:
                $this->sendPostMessage($chatId, $message_id);
                break;
            case UserStages::BUTTON_CATEGORY_CAR:
                $this->sendCategoryCarMessage($chatId, $message_id);
                break;
            case UserStages::BUTTON_CATEGORY_DETAIL:
                $this->sendCategoryDetailMessage($chatId, $message_id);
                break;
            case UserStages::BUTTON_CATEGORY_DETAIL_DETAIL:
                $text_message = TextMessagesService::getCategoryDetailDetailMessage();
                $this->handleDetailCallback($callbackQuery->getData(), $chatId, $message_id, $text_message);
                break;
            case UserStages::BUTTON_CATEGORY_DETAIL_WHEELS:
                $text_message = TextMessagesService::getCategoryDetailWheelsMessage();
                $this->handleDetailCallback($callbackQuery->getData(), $chatId, $message_id, $text_message);
                break;
            case UserStages::BUTTON_CATEGORY_DETAIL_AUDIO:
                $text_message = TextMessagesService::getCategoryDetailAudioMessage();
                $this->handleDetailCallback($callbackQuery->getData(), $chatId, $message_id, $text_message);
                break;
            case UserStages::BUTTON_CATEGORY_DETAIL_TOOLS:
                $text_message = TextMessagesService::getCategoryDetailToolsMessage();
                $this->handleDetailCallback($callbackQuery->getData(), $chatId, $message_id, $text_message);
                break;
            case UserStages::BUTTON_CATEGORY_DETAIL_OTHER:
                $text_message = TextMessagesService::getCategoryDetailOthersMessage();
                $this->handleDetailCallback($callbackQuery->getData(), $chatId, $message_id, $text_message);
                break;
            case 'search_adv':
                $this->senderMessage->sendMessage($chatId, 'search_adv');
                break;
            case UserStages::BUTTON_BACK_MAIN_MENU:
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
        $this->repository->updateUser($chatId, '', $username);
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

        $this->repository->updateUser($chatId, UserStages::POST_ADV_STEP1);
        $this->repository->updateTempAdv($chatId, ['adv_category' => $adv_category]);
    }

    // Отправка сообщения по кнопке "Запчасти"
    private function sendCategoryDetailMessage(int $chatId, int $message_id): void
    {
        $textMessage = TextMessagesService::getCategoryDetailMessage();
        $text = $textMessage['text'];
        $keyboard = $textMessage['keyboard'];
        $this->senderMessage->editMessageWithKeyboard($chatId, $message_id, $text, $keyboard);
    }

    // Обработчик callback для категории Запчасти
    private function handleDetailCallback(string $button_name, int $chatId, int $message_id, string $text_message): void
    {
        $this->senderMessage->editMessage($chatId, $message_id, $text_message);
        $adv_category = match ($button_name) {
            UserStages::BUTTON_CATEGORY_DETAIL_DETAIL => UserStages::CATEGORY_NAME_DETAIL,
            UserStages::BUTTON_CATEGORY_DETAIL_WHEELS => UserStages::CATEGORY_NAME_WHEELS,
            UserStages::BUTTON_CATEGORY_DETAIL_AUDIO => UserStages::CATEGORY_NAME_AUDIO,
            UserStages::BUTTON_CATEGORY_DETAIL_TOOLS => UserStages::CATEGORY_NAME_TOOLS,
            UserStages::BUTTON_CATEGORY_DETAIL_OTHER => UserStages::CATEGORY_NAME_OTHERS,
            default => null,
        };
        $this->repository->updateUser($chatId, UserStages::POST_ADV_DETAIL_STEP1);
        $this->repository->updateTempAdv($chatId, ['adv_category' => $adv_category]);
    }

    // Обработчик стадии
    private function handleStage(string $text, string $stage, string $column_name, int $chatId): void
    {
        $validated = $this->validateStage($stage, $text);
        if ($validated['result']) {
            $text_message = $this->getTextMessageForStage($stage);
            $this->repository->updateUser($chatId, $stage);
            $this->repository->updateTempAdv($chatId, [$column_name => $text]);
            if ($text_message !== null) {
                $this->senderMessage->sendMessage($chatId, $text_message);
            }
            if ($stage == '' || $stage == UserStages::POST_ADV_STEP7) {
                $this->finishAdv($chatId);
            }
        } else {
            $this->senderMessage->sendMessage($chatId, $validated['message']);
        }
    }

    // Публикация объявления
    private function finishAdv(int $chatId): void
    {
        $user = $this->repository->getUserDatePost($chatId);
        $count_minutes = $this->validator->validateTimeLimit($user->date_send_add);
        if ($count_minutes) {
            $this->repository->updateUserDatePost($chatId, date('Y-m-d H:i:s'));
            $temp_adv_row = $this->repository->getAdvRow($chatId);
            $text_adv = TextMessagesService::getFullAdvMessage($temp_adv_row, $user->username);
            $this->senderMessage->sendPostInPublic($temp_adv_row->adv_photo, $text_adv);
            /*
             * тут будет логика отправки пользователям по фильтрам
             *
             */
            $textMessage = TextMessagesService::getFinishMessage();
            $text = $textMessage['text'];
            $keyboard = $textMessage['keyboard'];
            $this->senderMessage->sendMessageWithKeyboard($chatId, $text, $keyboard);
        } else {
            $text = TextMessagesService::getTimeLimitMessage($count_minutes);
            $this->senderMessage->sendMessage($chatId, $text);
        }
    }

    // Валидация стадии
    private function validateStage(string $stage, string $text): array
    {
        return match ($stage) {
            UserStages::POST_ADV_STEP2 => ['result' => true, 'message' => null], // title пока не валидируем
            UserStages::POST_ADV_STEP3 => $this->validator->validateCarYear($text),
            UserStages::POST_ADV_STEP4 => $this->validator->validatePrice($text),
            UserStages::POST_ADV_STEP5 => $this->validator->validateDescription($text),
            UserStages::POST_ADV_STEP6, '' => $this->validator->validateIsPhoto($text),
            UserStages::POST_ADV_STEP7 => $this->validator->validateExtraContact($text),
            UserStages::POST_ADV_DETAIL_STEP2 => $this->validator->validateTitle($text),
            default => ['result' => false, 'message' => 'Не смог определить правило валидации'],
        };
    }

    // Получение текста сообщения в handleStage
    private function getTextMessageForStage(?string $stage): ?string
    {
        return match ($stage) {
            UserStages::POST_ADV_STEP2 => TextMessagesService::getCarYearMessage(),
            UserStages::POST_ADV_STEP3, UserStages::POST_ADV_DETAIL_STEP2 => TextMessagesService::getPriceMessage(),
            UserStages::POST_ADV_STEP4 => TextMessagesService::getDescriptionMessage(),
            UserStages::POST_ADV_STEP5 => TextMessagesService::getPhotoMessage(),
            UserStages::POST_ADV_STEP6 => TextMessagesService::getContactMessage(),
            default => null,
        };
    }
}
