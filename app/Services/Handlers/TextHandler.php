<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Constant\UserStages;
use App\DTO\UpdateContext;
use App\Services\AdvValidationService;
use App\Services\Flow\AdvPostingFlow;
use App\Services\LoggerService;
use App\Services\RepositoryService;
use App\Services\SenderService;
use App\Services\TextMessagesService;

class TextHandler
{
    protected AdvPostingFlow $flow;

    protected LoggerService $logger;

    protected SenderService $senderMessage;

    protected AdvValidationService $validator;

    protected RepositoryService $repository;

    protected TextMessagesService $textMessages;

    public function __construct(
        AdvPostingFlow $flow,
        LoggerService $logger,
        SenderService $senderMessage,
        AdvValidationService $validator,
        RepositoryService $repository,
        TextMessagesService $textMessages
    ) {
        $this->flow = $flow;
        $this->logger = $logger;
        $this->senderMessage = $senderMessage;
        $this->validator = $validator;
        $this->repository = $repository;
        $this->textMessages = $textMessages;
    }

    /**
     * Обработчик обычных текстовых сообщений
     */
    public function handle(UpdateContext $context): void
    {
        if ($this->validator->validateMainText($context->text)) {
            $user = $this->repository->getUser($context->chatId);
            if ($user !== null) {
                switch ($user['stage']) {
                    case UserStages::POST_ADV_STEP1: // получаем марку(название)
                        $this->handleStage($context->text, UserStages::POST_ADV_STEP2, 'adv_car_mark', $context->chatId);
                        break;
                    case UserStages::POST_ADV_STEP2: // получаем год выпуска
                        $this->handleStage($context->text, UserStages::POST_ADV_STEP3, 'adv_car_year_realise', $context->chatId);
                        break;
                    case UserStages::POST_ADV_STEP3: // получаем цену
                    case UserStages::POST_ADV_DETAIL_STEP2:
                        $this->handleStage($context->text, UserStages::POST_ADV_STEP4, 'adv_price', $context->chatId);
                        break;
                    case UserStages::POST_ADV_STEP4: // получаем описание
                        $this->handleStage($context->text, UserStages::POST_ADV_STEP5, 'adv_description', $context->chatId);
                        break;
                    case UserStages::POST_ADV_STEP5: // получаем фотку
                        if ($context->username) {
                            $this->handleStage($context->photoFileId, '', 'adv_photo', $context->chatId);
                        } else {
                            $this->handleStage($context->photoFileId, UserStages::POST_ADV_STEP6, 'adv_photo', $context->chatId);
                        }
                        break;
                    case UserStages::POST_ADV_STEP6: // получаем доп контакты
                        $this->handleStage($context->text, UserStages::POST_ADV_STEP7, 'adv_extra_contact', $context->chatId);
                        break;
                    case UserStages::POST_ADV_DETAIL_STEP1: // получаем Название запчасти
                        $this->handleStage($context->text, UserStages::POST_ADV_DETAIL_STEP2, 'adv_car_mark', $context->chatId);
                        break;
                    case UserStages::SET_FILTER_PRICE_MIN:
                        $this->handleStageFilters($context->text, UserStages::SET_FILTER_PRICE_MAX, 'filter_price_min', $context->chatId);
                        break;
                    case UserStages::SET_FILTER_PRICE_MAX:
                        $this->handleStageFilters($context->text, UserStages::SET_FILTER_PRICE_APPLY, 'filter_price_max', $context->chatId);
                        break;
                    default:
                        $this->senderMessage->sendOrEditMessage($context->chatId, 'Неопределённый stage');
                        $this->logger->debug('Unknown stage for user', ['chat_id' => $context->chatId, 'text' => $context->text]);
                }
            } else {
                $this->logger->error('User not found', ['chat_id' => $context->chatId]);
            }
        } else {
            $this->senderMessage->sendOrEditMessage($context->chatId, 'Некорректный текст сообщения');
            $this->logger->warning('User send bad text', ['chat_id' => $context->chatId, 'text' => $context->text]);
        }
    }

    /**
     * Обработчик стадии в handle
     */
    private function handleStage(?string $text, string $stage, string $column_name, int $chatId): void
    {
        $validated = $this->validateStage($stage, $text);
        if ($validated['result']) {
            $text_message = $this->getTextMessageForStage($stage);
            $this->repository->updateUser($chatId, $stage);
            $this->repository->updateTempAdv($chatId, [$column_name => $text]);
            if ($text_message !== null) {
                $this->senderMessage->sendOrEditMessage($chatId, $text_message);
            }
            if ($stage == '' || $stage == UserStages::POST_ADV_STEP7) {
                $this->flow->finishAdv($chatId);
            }
        } else {
            $this->senderMessage->sendOrEditMessage($chatId, $validated['message']);
            $this->logger->debug('Send NOT validated message to user', ['chat_id' => $chatId, 'message' => $validated['message']]);
        }
    }

    /**
     * Валидация стадии
     */
    private function validateStage(string $stage, ?string $text): array
    {
        return match ($stage) {
            UserStages::POST_ADV_STEP2 => ['result' => true, 'message' => null], // title пока не валидируем
            UserStages::POST_ADV_STEP3 => $this->validator->validateCarYear($text),
            UserStages::POST_ADV_STEP4,
            UserStages::SET_FILTER_PRICE_MAX,
            UserStages::SET_FILTER_PRICE_APPLY => $this->validator->validatePrice($text),
            UserStages::POST_ADV_STEP5 => $this->validator->validateDescription($text),
            UserStages::POST_ADV_STEP6, '' => $this->validator->validateIsPhoto($text),
            UserStages::POST_ADV_STEP7 => $this->validator->validateExtraContact($text),
            UserStages::POST_ADV_DETAIL_STEP2 => $this->validator->validateTitle($text),
            default => ['result' => false, 'message' => 'Не смог определить правило валидации'],
        };
    }

    /**
     * Получение текста сообщения в handleStage
     */
    private function getTextMessageForStage(?string $stage, ?int $chatId = null): string|array
    {
        return match ($stage) {
            UserStages::POST_ADV_STEP2 => TextMessagesService::getCarYearMessage(),
            UserStages::POST_ADV_STEP3, UserStages::POST_ADV_DETAIL_STEP2 => TextMessagesService::getPriceMessage(),
            UserStages::POST_ADV_STEP4 => TextMessagesService::getDescriptionMessage(),
            UserStages::POST_ADV_STEP5 => TextMessagesService::getPhotoMessage(),
            UserStages::POST_ADV_STEP6 => TextMessagesService::getContactMessage(),
            UserStages::SET_FILTER_PRICE_MAX => TextMessagesService::getFilterPriceMaxMessage(),
            UserStages::SET_FILTER_PRICE_APPLY => $this->textMessages->getFilterListMessage($chatId),
            default => null,
        };
    }

    /**
     * Обработчик стадии в handle для фильтров
     */
    private function handleStageFilters(?string $text, string $stage, string $column_name, int $chatId): void
    {
        $validated = $this->validateStage($stage, $text);
        if ($validated['result']) {
            $text_message = $this->getTextMessageForStage($stage, $chatId);
            $this->repository->updateUser($chatId, $stage);
            $this->repository->updateFilterPrice($chatId, $column_name, $text);
            if ($stage == UserStages::SET_FILTER_PRICE_MAX) {
                $this->senderMessage->sendOrEditMessage($chatId, $text_message);
            } else {
                $this->senderMessage->sendOrEditMessage($chatId, $text_message['text'], null, $text_message['keyboard']);
            }
        } else {
            $this->senderMessage->sendOrEditMessage($chatId, $validated['message']);
            $this->logger->debug('Send NOT validated message to user', ['chat_id' => $chatId, 'message' => $validated['message']]);
        }
    }
}
