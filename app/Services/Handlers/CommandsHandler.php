<?php

declare(strict_types=1);

namespace App\Services\Handlers;

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
    public function handle(int $chatId, string $text, ?string $username, int $message_id): void
    {
        switch ($text) {
            case '/start':
                $this->flow->sendWelcomeMessage($chatId, $username, $message_id, true);
                break;
            default:
                $this->logger->debug('User send unknown command', ['chat_id' => $chatId, 'text' => $text]);
                $this->senderMessage->sendMessage($chatId, 'Неизвестная команда.');
        }
    }
}
