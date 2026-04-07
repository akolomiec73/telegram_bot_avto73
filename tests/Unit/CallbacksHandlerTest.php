<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Constant\CallbackData;
use App\DTO\UpdateContext;
use App\Services\Flow\AdvPostingFlow;
use App\Services\Handlers\CallbacksHandler;
use App\Services\LoggerService;
use App\Services\MessageService;
use App\Services\RepositoryService;
use App\Services\SenderService;
use PHPUnit\Framework\TestCase;

class CallbacksHandlerTest extends TestCase
{
    private AdvPostingFlow $flowMock;

    private LoggerService $loggerMock;

    private SenderService $senderMock;

    private RepositoryService $repositoryMock;

    private MessageService $messageMock;

    private CallbacksHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flowMock = $this->createMock(AdvPostingFlow::class);
        $this->loggerMock = $this->createMock(LoggerService::class);
        $this->senderMock = $this->createMock(SenderService::class);
        $this->repositoryMock = $this->createMock(RepositoryService::class);
        $this->messageMock = $this->createMock(MessageService::class);

        $this->handler = new CallbacksHandler(
            $this->flowMock,
            $this->loggerMock,
            $this->senderMock,
            $this->repositoryMock,
            $this->messageMock
        );
    }

    // -------------------------------------------------------------------------
    // Тесты для метода handle
    // -------------------------------------------------------------------------

    public function test_handle_when_callback_is_main_menu(): void
    {
        $context = new UpdateContext(
            chatId: 123456,
            username: 'username',
            messageId: 42,
            text: 'some text',
            photoFileId: null,
            callbackData: CallbackData::BACK_MAIN_MENU,
        );

        $this->flowMock
            ->expects($this->once())
            ->method('sendWelcomeMessage')
            ->with(
                $context->chatId,
                $context->username,
                $context->messageId,
                false
            );

        $this->repositoryMock->expects($this->never())->method($this->anything());
        $this->senderMock->expects($this->never())->method($this->anything());
        $this->messageMock->expects($this->never())->method($this->anything());

        $this->handler->handle($context);
    }
}
