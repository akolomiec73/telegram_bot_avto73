<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\BotUsers;
use App\Models\TempAdvUser;
use App\Services\Flow\AdvPostingFlow;
use App\Services\LoggerService;
use App\Services\MessageService;
use App\Services\RepositoryService;
use App\Services\SenderService;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdvPostingFlowTest extends TestCase
{
    private LoggerService $loggerMock;

    private SenderService $senderMock;

    private RepositoryService $repositoryMock;

    private MessageService $messageServiceMock;

    private AdvPostingFlow $flow;

    private const TIME_LIMIT = 120;

    private const TEST_MESSAGE_ID = 42;

    private const TEST_CHAT_ID = 123456789;

    private const TEST_USERNAME = 'username';

    private const TEST_MESSAGE_WITH_KEYBOARD = [
        'text' => 'Test message with keyboard',
        'keyboard' => ['inline_keyboard' => []],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake(); // что бы не проверять dispatch

        $this->loggerMock = $this->createMock(LoggerService::class);
        $this->senderMock = $this->createMock(SenderService::class);
        $this->repositoryMock = $this->createMock(RepositoryService::class);
        $this->messageServiceMock = $this->createMock(MessageService::class);

        $this->flow = new AdvPostingFlow(
            $this->loggerMock,
            $this->senderMock,
            $this->repositoryMock,
            $this->messageServiceMock,
            self::TIME_LIMIT);
    }

    // -------------------------------------------------------------------------
    // Тесты для метода sendWelcomeMessage
    // -------------------------------------------------------------------------

    #[Test]
    #[DataProvider('welcomeMessageDataProvider')]
    public function test_it_calls_send_message(bool $isFirstMessage, ?int $expectedMessageId): void
    {
        $chatId = self::TEST_CHAT_ID;
        $username = self::TEST_USERNAME;
        $startMessage = self::TEST_MESSAGE_WITH_KEYBOARD;

        $this->messageServiceMock->expects(self::once())
            ->method('getStartMessage')
            ->willReturn($startMessage);

        $this->senderMock->expects(self::once())
            ->method('sendOrEditMessage')
            ->with(
                $chatId,
                $startMessage['text'],
                $expectedMessageId,
                $startMessage['keyboard']
            );

        $this->repositoryMock->expects(self::once())
            ->method('updateUser')
            ->with($chatId, '', $username);

        $this->loggerMock->expects(self::once())
            ->method('debug');

        $this->flow->sendWelcomeMessage($chatId, self::TEST_USERNAME, self::TEST_MESSAGE_ID, $isFirstMessage);
    }

    public static function welcomeMessageDataProvider(): array
    {
        return [
            'Первое сообщение (isFirstMessage = true)' => [
                'isFirstMessage' => true,
                'expectedMessageId' => null,
            ],
            'Не первое сообщение (isFirstMessage = false)' => [
                'isFirstMessage' => false,
                'expectedMessageId' => self::TEST_MESSAGE_ID,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Тесты для метода finishAdv
    // -------------------------------------------------------------------------

    #[Test]
    public function test_finish_adv_when_time_limit_good(): void
    {
        $chatId = self::TEST_CHAT_ID;
        $lastPostDate = date('Y-m-d H:i:s', strtotime('-121 minutes'));
        $userObject = new BotUsers;
        $userObject->date_send_add = $lastPostDate;
        $userObject->username = self::TEST_USERNAME;
        $advObject = new TempAdvUser;
        $advObject->adv_photo = 'JADPiwj3dmp23omdopmd';
        $advText = 'Text adv';
        $finishMessage = self::TEST_MESSAGE_WITH_KEYBOARD;

        $this->repositoryMock->expects(self::once())
            ->method('getUserDatePost')
            ->with($chatId)
            ->willReturn($userObject);

        $this->repositoryMock->expects(self::once())
            ->method('getAdvRow')
            ->with($chatId)
            ->willReturn($advObject);

        $this->messageServiceMock->expects(self::once())
            ->method('getFullAdvMessage')
            ->with($advObject, $userObject->username)
            ->willReturn($advText);

        $this->senderMock->expects(self::once())
            ->method('sendPostInPublic')
            ->with($advObject->adv_photo, $advText);

        $this->repositoryMock->expects(self::once())
            ->method('updateUserDatePost')
            ->with($chatId, date('Y-m-d H:i:s'));

        $this->messageServiceMock->expects(self::once())
            ->method('getFinishMessage')
            ->willreturn($finishMessage);

        $this->senderMock->expects(self::once())
            ->method('sendOrEditMessage')
            ->with($chatId, $finishMessage['text'], null, $finishMessage['keyboard']);

        $this->loggerMock->expects(self::once())
            ->method('debug');

        $result = $this->flow->finishAdv($chatId);
        $this->assertTrue($result);
    }

    #[Test]
    public function test_finish_adv_when_time_limit_bad(): void
    {
        $chatId = self::TEST_CHAT_ID;
        $lastPostDate = date('Y-m-d H:i:s', strtotime('-2 minutes'));
        $userObject = new BotUsers;
        $userObject->date_send_add = $lastPostDate;
        $count_minutes = 2;
        $someText = 'Some text';

        $this->repositoryMock->expects(self::once())
            ->method('getUserDatePost')
            ->with($chatId)
            ->willReturn($userObject);

        $this->messageServiceMock->expects(self::once())
            ->method('getTimeLimitMessage')
            ->with($count_minutes)
            ->willReturn($someText);

        $this->senderMock->expects(self::once())
            ->method('sendOrEditMessage')
            ->with($chatId, $someText);

        $this->loggerMock->expects(self::once())
            ->method('debug');

        $result = $this->flow->finishAdv($chatId);
        $this->assertFalse($result);
    }
}
