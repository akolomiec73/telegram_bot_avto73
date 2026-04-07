<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\UpdateContext;
use App\Services\Flow\AdvPostingFlow;
use App\Services\Handlers\CommandsHandler;
use App\Services\LoggerService;
use App\Services\MessageService;
use App\Services\SenderService;
use PHPUnit\Framework\TestCase;

class CommandsHandlerTest extends TestCase
{
    private AdvPostingFlow $flowMock;

    private LoggerService $loggerMock;

    private SenderService $senderMock;

    private MessageService $messageMock;

    private CommandsHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flowMock = $this->createMock(AdvPostingFlow::class);
        $this->loggerMock = $this->createMock(LoggerService::class);
        $this->senderMock = $this->createMock(SenderService::class);
        $this->messageMock = $this->createMock(MessageService::class);

        $this->handler = new CommandsHandler($this->flowMock, $this->loggerMock, $this->senderMock, $this->messageMock);
    }

    // -------------------------------------------------------------------------
    // Тесты для метода handle
    // -------------------------------------------------------------------------

    public function test_it_calls_command_start(): void
    {
        $context = new UpdateContext(
            chatId: 123456,
            username: 'username',
            messageId: 42,
            text: '/start',
            photoFileId: null,
            callbackData: null,
        );

        $this->flowMock
            ->expects($this->once())
            ->method('sendWelcomeMessage')
            ->with(
                $context->chatId,
                $context->username,
                $context->messageId,
                true
            );

        $this->loggerMock->expects($this->never())->method('info');
        $this->senderMock->expects($this->never())->method('sendOrEditMessage');
        $this->messageMock->expects($this->never())->method('getUnknownCommandMessage');

        $this->handler->handle($context);
    }

    public function test_it_calls_unknown_command(): void
    {
        $context = new UpdateContext(
            chatId: 123456,
            username: 'username',
            messageId: 42,
            text: '/unknown_command',
            photoFileId: null,
            callbackData: null,
        );
        $message = 'Some message';

        $this->messageMock
            ->expects($this->once())
            ->method('getUnknownCommandMessage')
            ->willReturn($message);

        $this->senderMock
            ->expects($this->once())
            ->method('sendOrEditMessage')
            ->with(
                $context->chatId,
                $message
            );

        $this->flowMock->expects($this->never())->method('sendWelcomeMessage');

        $this->handler->handle($context);
    }
}
