<?php

/*
 * Основная бизнес-логика
 */
declare(strict_types=1);

namespace App\Services;

use App\Services\Handlers\CallbacksHandler;
use App\Services\Handlers\CommandsHandler;
use App\Services\Handlers\TextHandler;
use Telegram\Bot\Api;

class MainService
{
    protected Api $telegram;

    protected SenderService $senderMessage;

    protected LoggerService $logger;

    protected CommandsHandler $handlerCommands;

    protected CallbacksHandler $handlerCallbacks;

    protected TextHandler $handlerText;

    public function __construct(
        Api $telegram,
        SenderService $senderMessage,
        LoggerService $logger,
        CommandsHandler $handlerCommands,
        CallbacksHandler $handlerCallbacks,
        TextHandler $handlerText
    ) {
        $this->telegram = $telegram;
        $this->senderMessage = $senderMessage;
        $this->logger = $logger;
        $this->handlerCommands = $handlerCommands;
        $this->handlerCallbacks = $handlerCallbacks;
        $this->handlerText = $handlerText;
    }

    /**
     * Основной метод обработки обновлений от Telegram
     */
    public function handleUpdate(): void
    {
        $update = $this->telegram->getWebhookUpdate();
        $message = $update->getMessage();
        if (! $message) {
            $this->logger->warning('Unknown message', ['update' => $update]);

            return;
        }
        $text = $message->getText();
        $chatId = $message->getChat()->getId();
        $message_id = $message->getMessageId();
        $username = $message->getChat()->getUsername();
        $photo = $message->getPhoto() ?? null;
        $fileId = null;
        if ($photo !== null) {
            $fileId = $photo[count($photo) - 1]->getFileId();
        }

        if ($update->getCallbackQuery()) {
            $this->handlerCallbacks->handle($chatId, $username, $update->getCallbackQuery()->getData(), $message_id);
        } elseif ($text !== null && str_starts_with($text, '/')) {
            $this->handlerCommands->handle($chatId, $text, $username, $message_id);
        } elseif ($text !== null || $fileId !== null) {
            $this->handlerText->handle($chatId, $text, $fileId);
        } else {
            $this->senderMessage->sendMessage($chatId, 'Бот принимает только текстовые сообщения и команды.');
            $this->logger->debug('User send media', ['chat_id' => $chatId, 'message' => $message]);
        }
    }
}
