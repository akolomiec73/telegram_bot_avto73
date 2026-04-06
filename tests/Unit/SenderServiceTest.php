<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\LoggerService;
use App\Services\SenderService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Telegram\Bot\Api;

class SenderServiceTest extends TestCase
{
    private Api $apiMock;

    private SenderService $senderService;

    private LoggerService $loggerMock;

    // Тестовые данные
    private const TEST_CHAT_ID = 123456789;

    private const TEST_TEXT = 'Hello, World!';

    private const TEST_MESSAGE_ID = 42;

    private const TEST_PUBLIC_GROUP_ID = '-3123123123123';

    private const TEST_KEYBOARD = ['inline_keyboard' => [[['text' => 'Button', 'callback_data' => 'data']]]];

    private const TEST_FILE_ID = 'adwkd12dj3idojh23dh1oHDO';

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiMock = $this->createMock(Api::class);
        $this->loggerMock = $this->createMock(LoggerService::class);

        $this->senderService = new SenderService($this->apiMock, self::TEST_PUBLIC_GROUP_ID, $this->loggerMock);
    }

    // -------------------------------------------------------------------------
    // Тесты для метода sendOrEditMessage
    // -------------------------------------------------------------------------

    #[Test]
    public function it_calls_send_message_when_message_id_is_null(): void
    {
        $this->apiMock
            ->expects($this->once())
            ->method('sendMessage')
            ->with($this->callback(function (array $params): bool {
                return $params['chat_id'] === self::TEST_CHAT_ID
                    && $params['text'] === self::TEST_TEXT
                    && $params['parse_mode'] === 'HTML'
                    && ! isset($params['message_id'])
                    && ! isset($params['reply_markup']);
            }));
        $this->senderService->sendOrEditMessage(self::TEST_CHAT_ID, self::TEST_TEXT);
    }

    #[Test]
    public function it_calls_send_message_with_keyboard_when_provided(): void
    {
        $this->apiMock
            ->expects($this->once())
            ->method('sendMessage')
            ->with($this->callback(function (array $params): bool {
                if (! isset($params['reply_markup'])) {
                    return false;
                }
                $decodedKeyboard = json_decode($params['reply_markup'], true);

                return $decodedKeyboard === self::TEST_KEYBOARD;
            }));
        $this->senderService->sendOrEditMessage(self::TEST_CHAT_ID, self::TEST_TEXT, null, self::TEST_KEYBOARD);
    }

    #[Test]
    public function it_calls_edit_message_when_message_id_is_provided(): void
    {
        $this->apiMock
            ->expects($this->once())
            ->method('editMessageText')
            ->with($this->callback(function (array $params): bool {
                return $params['chat_id'] === self::TEST_CHAT_ID
                    && $params['text'] === self::TEST_TEXT
                    && $params['parse_mode'] === 'HTML'
                    && $params['message_id'] === self::TEST_MESSAGE_ID
                    && ! isset($params['reply_markup']);
            }));
        $this->senderService->sendOrEditMessage(self::TEST_CHAT_ID, self::TEST_TEXT, self::TEST_MESSAGE_ID, null);
    }

    #[Test]
    public function it_calls_edit_message_with_keyboard_when_provided(): void
    {
        $this->apiMock
            ->expects($this->once())
            ->method('editMessageText')
            ->with($this->callback(function (array $params): bool {
                if (! isset($params['reply_markup'])) {
                    return false;
                }
                $decodedKeyboard = json_decode($params['reply_markup'], true);

                return $decodedKeyboard === self::TEST_KEYBOARD;
            }));
        $this->senderService->sendOrEditMessage(self::TEST_CHAT_ID, self::TEST_TEXT, self::TEST_MESSAGE_ID, self::TEST_KEYBOARD);
    }

    // -------------------------------------------------------------------------
    // Тесты для метода sendPostInPublic
    // -------------------------------------------------------------------------

    #[Test]
    public function it_calls_send_photo(): void
    {
        $this->apiMock
            ->expects($this->once())
            ->method('sendPhoto')
            ->with($this->callback(function (array $params): bool {
                return $params['chat_id'] === self::TEST_PUBLIC_GROUP_ID
                    && $params['photo'] === self::TEST_FILE_ID
                    && $params['caption'] === self::TEST_TEXT
                    && $params['parse_mode'] === 'HTML';
            }));
        $this->senderService->sendPostInPublic(self::TEST_FILE_ID, self::TEST_TEXT);
    }
}
