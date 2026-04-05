<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Constant\CallbackData;
use App\Constant\CategoryNames;
use App\Constant\UserStages;
use App\DTO\UpdateContext;
use App\Services\Flow\AdvPostingFlow;
use App\Services\LoggerService;
use App\Services\MessageService;
use App\Services\RepositoryService;
use App\Services\SenderService;

/**
 * Обработчик callback-запросов от кнопок.
 */
readonly class CallbacksHandler
{
    private const CATEGORY_BUTTONS = [
        CallbackData::CATEGORY_CAR => CategoryNames::CAR,
        CallbackData::CATEGORY_DETAIL_DETAIL => CategoryNames::DETAIL,
        CallbackData::CATEGORY_DETAIL_WHEELS => CategoryNames::WHEELS,
        CallbackData::CATEGORY_DETAIL_AUDIO => CategoryNames::AUDIO,
        CallbackData::CATEGORY_DETAIL_TOOLS => CategoryNames::TOOLS,
        CallbackData::CATEGORY_DETAIL_OTHER => CategoryNames::OTHERS,
    ];

    private const FILTER_BUTTONS = [
        CallbackData::FILTER_CATEGORY_CAR,
        CallbackData::FILTER_CATEGORY_DETAIL,
        CallbackData::FILTER_STATUS,
    ];

    public function __construct(
        private AdvPostingFlow $flow,
        private LoggerService $logger,
        private SenderService $sender,
        private RepositoryService $repository,
        private MessageService $messageService
    ) {}

    /**
     * Главная точка входа обработки callback.
     */
    public function handle(UpdateContext $context): void
    {
        if ($context->callbackData === CallbackData::BACK_MAIN_MENU) {
            $this->flow->sendWelcomeMessage($context->chatId, $context->username, $context->messageId, false);

            return;
        }
        $this->applyDatabaseEffects($context->callbackData, $context->chatId);
        $this->sendMessageToUser($context->callbackData, $context->chatId, $context->messageId);
    }

    /**
     * Вносит необходимые изменения в таблицы БД
     */
    private function applyDatabaseEffects(string $callbackData, int $chatId): void
    {
        if (isset(self::CATEGORY_BUTTONS[$callbackData])) {
            $stage = $callbackData === CallbackData::CATEGORY_CAR ? UserStages::POST_ADV_STEP1 : UserStages::POST_ADV_DETAIL_STEP1;
            $this->repository->updateUser($chatId, $stage);
            $this->repository->updateTempAdv($chatId, ['adv_category' => self::CATEGORY_BUTTONS[$callbackData]]);
        }
        if (in_array($callbackData, self::FILTER_BUTTONS, true)) {
            $this->repository->updateFilter($chatId, $callbackData);
        }
        if ($callbackData === CallbackData::FILTER_PRICE) {
            $this->repository->updateUser($chatId, UserStages::SET_FILTER_PRICE_MIN);
        }
    }

    /**
     * Выбирает текст и отправляет сообщение пользователю
     */
    private function sendMessageToUser(string $callbackData, int $chatId, ?int $messageId): void
    {
        $textMessage = match ($callbackData) {
            CallbackData::POST_ADV => $this->messageService->getPostMessage(),
            CallbackData::CATEGORY_CAR => $this->messageService->getCategoryCarMessage(),
            CallbackData::CATEGORY_DETAIL => $this->messageService->getCategoryDetailMessage(),
            CallbackData::CATEGORY_DETAIL_DETAIL => $this->messageService->getCategoryDetailDetailMessage(),
            CallbackData::CATEGORY_DETAIL_WHEELS => $this->messageService->getCategoryDetailWheelsMessage(),
            CallbackData::CATEGORY_DETAIL_AUDIO => $this->messageService->getCategoryDetailAudioMessage(),
            CallbackData::CATEGORY_DETAIL_TOOLS => $this->messageService->getCategoryDetailToolsMessage(),
            CallbackData::CATEGORY_DETAIL_OTHER => $this->messageService->getCategoryDetailOthersMessage(),
            CallbackData::SEARCH_ADV => $this->messageService->getSearchMessage(),
            CallbackData::FILTER_ADD,
            CallbackData::FILTER_APPLY,
            CallbackData::FILTER_STATUS => $this->messageService->getFilterListMessage($chatId),
            CallbackData::FILTER_CATEGORY,
            CallbackData::FILTER_CATEGORY_CAR,
            CallbackData::FILTER_CATEGORY_DETAIL => $this->messageService->getFilterCategoryMessage($chatId),
            CallbackData::FILTER_PRICE => $this->messageService->getFilterPriceMessage(),
            default => null,
        };
        if ($textMessage !== null) {
            $this->sender->sendOrEditMessage($chatId, $textMessage['text'], $messageId, $textMessage['keyboard']);
            $this->logger->debug('Handle callback to user', ['chat_id' => $chatId, 'button' => $callbackData]);
        } else {
            $this->sender->sendOrEditMessage($chatId, $this->messageService->getErrorMessage());
            $this->logger->warning('Unknown callback for user', ['chat_id' => $chatId, 'callbackQuery' => $callbackData]);
        }
    }
}
