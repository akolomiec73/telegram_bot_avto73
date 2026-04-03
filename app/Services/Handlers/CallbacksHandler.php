<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Constant\UserStages;
use App\DTO\UpdateContext;
use App\Services\Flow\AdvPostingFlow;
use App\Services\LoggerService;
use App\Services\RepositoryService;
use App\Services\SenderService;
use App\Services\TextMessagesService;

class CallbacksHandler
{
    protected AdvPostingFlow $flow;

    protected LoggerService $logger;

    protected SenderService $senderMessage;

    protected RepositoryService $repository;

    protected TextMessagesService $textMessages;

    public function __construct(
        AdvPostingFlow $flow,
        LoggerService $logger,
        SenderService $senderMessage,
        RepositoryService $repository,
        TextMessagesService $textMessages
    ) {
        $this->flow = $flow;
        $this->logger = $logger;
        $this->senderMessage = $senderMessage;
        $this->repository = $repository;
        $this->textMessages = $textMessages;
    }

    /**
     * Обработчик callback (кнопки меню)
     */
    public function handle(UpdateContext $context): void
    {
        switch ($context->callbackData) {
            case UserStages::BUTTON_POST_ADV:
            case UserStages::BUTTON_CATEGORY_CAR:
            case UserStages::BUTTON_CATEGORY_DETAIL:
            case UserStages::BUTTON_CATEGORY_DETAIL_DETAIL:
            case UserStages::BUTTON_CATEGORY_DETAIL_WHEELS:
            case UserStages::BUTTON_CATEGORY_DETAIL_AUDIO:
            case UserStages::BUTTON_CATEGORY_DETAIL_TOOLS:
            case UserStages::BUTTON_CATEGORY_DETAIL_OTHER:
            case UserStages::BUTTON_SEARCH_ADV:
            case UserStages::BUTTON_FILTER_ADD:
            case UserStages::BUTTON_FILTER_CATEGORY:
            case UserStages::BUTTON_FILTER_CATEGORY_CAR:
            case UserStages::BUTTON_FILTER_CATEGORY_DETAIL:
            case UserStages::BUTTON_FILTER_APPLY:
            case UserStages::BUTTON_FILTER_PRICE:
            case UserStages::BUTTON_FILTER_STATUS:
                $this->handleStage($context->chatId, $context->callbackData, $context->messageId);
                break;
            case UserStages::BUTTON_BACK_MAIN_MENU:
                $this->flow->sendWelcomeMessage($context->chatId, $context->username, $context->messageId, false);
                break;
            default:
                $this->senderMessage->sendMessage($context->chatId, 'Неизвестный callback.');
                $this->logger->warning('Unknown callback for user', ['chat_id' => $context->chatId, 'callbackQuery' => $context->callbackData]);
        }
    }

    private function handleStage(int $chatId, string $button, int $message_id): void
    {
        switch ($button) {
            case UserStages::BUTTON_CATEGORY_CAR:
            case UserStages::BUTTON_CATEGORY_DETAIL_DETAIL:
            case UserStages::BUTTON_CATEGORY_DETAIL_WHEELS:
            case UserStages::BUTTON_CATEGORY_DETAIL_AUDIO:
            case UserStages::BUTTON_CATEGORY_DETAIL_TOOLS:
            case UserStages::BUTTON_CATEGORY_DETAIL_OTHER:
                $adv_category = match ($button) {
                    UserStages::BUTTON_CATEGORY_CAR => 'Транспорт',
                    UserStages::BUTTON_CATEGORY_DETAIL_DETAIL => UserStages::CATEGORY_NAME_DETAIL,
                    UserStages::BUTTON_CATEGORY_DETAIL_WHEELS => UserStages::CATEGORY_NAME_WHEELS,
                    UserStages::BUTTON_CATEGORY_DETAIL_AUDIO => UserStages::CATEGORY_NAME_AUDIO,
                    UserStages::BUTTON_CATEGORY_DETAIL_TOOLS => UserStages::CATEGORY_NAME_TOOLS,
                    UserStages::BUTTON_CATEGORY_DETAIL_OTHER => UserStages::CATEGORY_NAME_OTHERS,
                    default => null,
                };
                $stage = UserStages::POST_ADV_DETAIL_STEP1;
                if ($button == UserStages::BUTTON_CATEGORY_CAR) {
                    $stage = UserStages::POST_ADV_STEP1;
                }
                $this->repository->updateUser($chatId, $stage);
                $this->repository->updateTempAdv($chatId, ['adv_category' => $adv_category]);
                break;
            case UserStages::BUTTON_FILTER_CATEGORY_CAR:
            case UserStages::BUTTON_FILTER_CATEGORY_DETAIL:
            case UserStages::BUTTON_FILTER_STATUS:
                $this->repository->updateFilter($chatId, $button);
                break;
            case UserStages::BUTTON_FILTER_PRICE:
                $this->repository->updateUser($chatId, UserStages::SET_FILTER_PRICE_MIN);
                break;
        }
        $textMessage = match ($button) {
            UserStages::BUTTON_POST_ADV => TextMessagesService::getPostMessage(),
            UserStages::BUTTON_CATEGORY_CAR => TextMessagesService::getCategoryCarMessage(),
            UserStages::BUTTON_CATEGORY_DETAIL => TextMessagesService::getCategoryDetailMessage(),
            UserStages::BUTTON_CATEGORY_DETAIL_DETAIL => TextMessagesService::getCategoryDetailDetailMessage(),
            UserStages::BUTTON_CATEGORY_DETAIL_WHEELS => TextMessagesService::getCategoryDetailWheelsMessage(),
            UserStages::BUTTON_CATEGORY_DETAIL_AUDIO => TextMessagesService::getCategoryDetailAudioMessage(),
            UserStages::BUTTON_CATEGORY_DETAIL_TOOLS => TextMessagesService::getCategoryDetailToolsMessage(),
            UserStages::BUTTON_CATEGORY_DETAIL_OTHER => TextMessagesService::getCategoryDetailOthersMessage(),
            UserStages::BUTTON_SEARCH_ADV => TextMessagesService::getSearchMessage(),
            UserStages::BUTTON_FILTER_ADD,
            UserStages::BUTTON_FILTER_APPLY,
            UserStages::BUTTON_FILTER_STATUS => $this->textMessages->getFilterListMessage($chatId),
            UserStages::BUTTON_FILTER_CATEGORY,
            UserStages::BUTTON_FILTER_CATEGORY_CAR,
            UserStages::BUTTON_FILTER_CATEGORY_DETAIL => $this->textMessages->getFilterCategoryMessage($chatId),
            UserStages::BUTTON_FILTER_PRICE => TextMessagesService::getFilterPriceMessage(),
            default => null,
        };
        if ($textMessage['keyboard'] !== null) {
            $this->senderMessage->editMessageWithKeyboard($chatId, $message_id, $textMessage['text'], $textMessage['keyboard']);
        } else {
            $this->senderMessage->editMessage($chatId, $message_id, $textMessage['text']);
        }
        $this->logger->debug('handleStageCallback to user', ['chat_id' => $chatId, 'button' => $button]);
    }
}
