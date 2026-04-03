<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\DTO\UpdateContext;
use App\Services\Flow\AdvPostingFlow;
use App\Services\LoggerService;
use App\Services\SenderService;

class CommandsHandler
{
    protected AdvPostingFlow $flow;

    protected LoggerService $logger;

    protected SenderService $senderMessage;

    public function __construct(
        AdvPostingFlow $flow,
        LoggerService $logger,
        SenderService $senderMessage
    ) {
        $this->flow = $flow;
        $this->logger = $logger;
        $this->senderMessage = $senderMessage;
    }

    /**
     * Обработчик команд
     */
    public function handle(UpdateContext $context): void
    {
        switch ($context->text) {
            case '/start':
                $this->flow->sendWelcomeMessage($context->chatId, $context->username, $context->messageId, true);
                break;
            default:
                $this->logger->debug('User send unknown command', ['chat_id' => $context->chatId, 'text' => $context->text]);
                $this->senderMessage->sendOrEditMessage($context->chatId, 'Неизвестная команда.');
        }
    }
}
