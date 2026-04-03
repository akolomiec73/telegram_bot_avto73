<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\DTO\UpdateContext;
use App\Services\Flow\AdvPostingFlow;
use App\Services\LoggerService;
use App\Services\SenderService;
use App\Services\TextMessagesService;

readonly class CommandsHandler
{
    public function __construct(
        private AdvPostingFlow $flow,
        private LoggerService $logger,
        private SenderService $sender
    ) {}

    /**
     * Обработчик текстовых команд (начинаются с '/').
     */
    public function handle(UpdateContext $context): void
    {
        switch ($context->text) {
            case '/start':
                $this->flow->sendWelcomeMessage($context->chatId, $context->username, $context->messageId, true);
                break;
            default:
                $this->logger->info('User send unknown command', ['chat_id' => $context->chatId, 'text' => $context->text]);
                $this->sender->sendOrEditMessage($context->chatId, TextMessagesService::getUnknownCommandMessage());
        }
    }
}
