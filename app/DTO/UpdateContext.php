<?php

declare(strict_types=1);

namespace App\DTO;

use Telegram\Bot\Objects\Message;

/**
 * Нормализованные данные из входящего Telegram Update для передачи в хендлеры.
 *
 * Собирается из {@see Message} и при наличии — из callback_query того же Update.
 */
final readonly class UpdateContext
{
    /**
     * @param  int  $chatId  Идентификатор чата (Telegram chat id).
     * @param  string|null  $username  Username чата, если есть (для личных чатов совпадает с @username пользователя).
     * @param  int|null  $messageId  Идентификатор сообщения, к которому относится контекст.
     * @param  string|null  $text  Текст сообщения (если это не фото без подписи).
     * @param  string|null  $photoFileId  File id самого крупного размера фото, если в сообщении есть фото.
     * @param  string|null  $callbackData  Данные inline-кнопки, если в этом же Update пришёл callback_query.
     */
    public function __construct(
        public int $chatId,
        public ?string $username,
        public ?int $messageId,
        public ?string $text,
        public ?string $photoFileId,
        public ?string $callbackData,
    ) {}
}
