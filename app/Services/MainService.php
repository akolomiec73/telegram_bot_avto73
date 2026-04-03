<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\UpdateContext;
use App\Services\Handlers\CallbacksHandler;
use App\Services\Handlers\CommandsHandler;
use App\Services\Handlers\TextHandler;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

/**
 * Точка входа бизнес-логики бота: получает webhook Update из Telegram SDK и направляет его в нужный хендлер.
 */
readonly class MainService
{
    public function __construct(
        private Api $telegram,
        private SenderService $sender,
        private LoggerService $logger,
        private CommandsHandler $handlerCommands,
        private CallbacksHandler $handlerCallbacks,
        private TextHandler $handlerText,
    ) {}

    /**
     * Читает текущий webhook Update, собирает {@see UpdateContext} и вызывает нужный хендлер.
     *
     * Ожидает наличие {@see Message} в Update; иные типы апдейтов логируются и игнорируются.
     */
    public function handleUpdate(): void
    {
        $update = $this->telegram->getWebhookUpdate();
        $message = $update->getMessage();
        if (! $message) {
            return;
        }
        $context = $this->createContext($message, $update);

        if ($context->callbackData !== null) {
            $this->handleWithErrorHandling(fn () => $this->handlerCallbacks->handle($context), $context);

            return;
        }

        if ($context->text && str_starts_with($context->text, '/')) {
            $this->handleWithErrorHandling(fn () => $this->handlerCommands->handle($context), $context);
        } elseif ($context->text || $context->photoFileId) {
            $this->handleWithErrorHandling(fn () => $this->handlerText->handle($context), $context);
        } else {
            $this->sender->sendMessage($context->chatId, TextMessagesService::getUnsupportedMediaMessage());
            $this->logger->debug('Unsupported message type', ['chat_id' => $context->chatId, 'message' => $message]);
        }
    }

    /**
     * Собирает DTO из объекта сообщения и полного Update.
     */
    private function createContext(Message $message, Update $update): UpdateContext
    {
        return new UpdateContext(
            chatId: $message->getChat()->getId(),
            username: $message->getChat()->getUsername(),
            messageId: $message->getMessageId(),
            text: $message->getText(),
            photoFileId: $this->extractPhotoFileId($message),
            callbackData: $update->getCallbackQuery()?->getData(),
        );
    }

    /**
     * Возвращает file_id фото из сообщения или null, если фото нет.
     */
    private function extractPhotoFileId(Message $message): ?string
    {
        $photo = $message->getPhoto();
        if (! empty($photo)) {
            return $photo[array_key_last($photo)]->getFileId();
        }

        return null;
    }

    /**
     * Отдаёт сообщение об ошибке юзеру и логирует ошибку выполнения обработчика
     */
    private function handleWithErrorHandling(callable $callback, UpdateContext $context): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            $this->sender->sendMessage($context->chatId, TextMessagesService::getErrorMessage());
            $this->logger->error('Handler error', [
                'chat_id' => $context->chatId,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
