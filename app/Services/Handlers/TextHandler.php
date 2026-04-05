<?php

declare(strict_types=1);

namespace App\Services\Handlers;

use App\Constant\UserStages;
use App\DTO\UpdateContext;
use App\Services\Flow\AdvPostingFlow;
use App\Services\LoggerService;
use App\Services\MessageService;
use App\Services\RepositoryService;
use App\Services\SenderService;
use App\Services\ValidationService;

/**
 * Обработчик текстовых сообщений и фото.
 */
readonly class TextHandler
{
    /**
     * Конфигурация этапов создания объявления.
     *  Для каждого этапа:
     *  - next_stage: следующая стадия ('' — завершение)
     *  - field: поле в БД для сохранения
     *  - validator: метод валидации в ValidationService
     *  - message: имя метода MessageService для получения текста
     *  - expect_photo: true если ожидается фото
     */
    private const STAGE_CONFIG = [
        UserStages::POST_ADV_STEP1 => [
            'next_stage' => UserStages::POST_ADV_STEP2,
            'field' => 'adv_car_mark',
            'validator' => 'validateTitle',
            'message' => 'getCarYearMessage',
        ],
        UserStages::POST_ADV_STEP2 => [
            'next_stage' => UserStages::POST_ADV_STEP3,
            'field' => 'adv_car_year_realise',
            'validator' => 'validateCarYear',
            'message' => 'getPriceMessage',
        ],
        UserStages::POST_ADV_STEP3 => [
            'next_stage' => UserStages::POST_ADV_STEP4,
            'field' => 'adv_price',
            'validator' => 'validatePrice',
            'message' => 'getDescriptionMessage',
        ],
        UserStages::POST_ADV_STEP4 => [
            'next_stage' => UserStages::POST_ADV_STEP5,
            'field' => 'adv_description',
            'validator' => 'validateDescription',
            'message' => 'getPhotoMessage',
        ],
        UserStages::POST_ADV_STEP5 => [
            'next_stage' => '',
            'field' => 'adv_photo',
            'validator' => 'validateIsPhoto',
            'message' => 'getContactMessage',
            'expect_photo' => true,
        ],
        UserStages::POST_ADV_STEP6 => [
            'next_stage' => '',
            'field' => 'adv_extra_contact',
            'validator' => 'validateExtraContact',
            'message' => null,
        ],
        UserStages::POST_ADV_DETAIL_STEP1 => [
            'next_stage' => UserStages::POST_ADV_DETAIL_STEP2,
            'field' => 'adv_car_mark',
            'validator' => 'validateTitle',
            'message' => 'getPriceMessage',
        ],
        UserStages::POST_ADV_DETAIL_STEP2 => [
            'next_stage' => UserStages::POST_ADV_STEP4,
            'field' => 'adv_price',
            'validator' => 'validatePrice',
            'message' => 'getDescriptionMessage',
        ],
    ];

    /**
     * Конфигурация этапов фильтрации по цене.
     */
    private const FILTER_STAGES = [
        UserStages::SET_FILTER_PRICE_MIN => [
            'next_stage' => UserStages::SET_FILTER_PRICE_MAX,
            'field' => 'filter_price_min',
            'validator' => 'validatePrice',
            'message' => 'getFilterPriceMaxMessage',
        ],
        UserStages::SET_FILTER_PRICE_MAX => [
            'next_stage' => UserStages::SET_FILTER_PRICE_APPLY,
            'field' => 'filter_price_max',
            'validator' => 'validatePrice',
            'message' => null,
        ],
    ];

    public function __construct(
        private AdvPostingFlow $flow,
        private LoggerService $logger,
        private SenderService $sender,
        private ValidationService $validator,
        private RepositoryService $repository,
        private MessageService $messageService,
    ) {}

    /**
     * Основная точка входа в обработчик
     */
    public function handle(UpdateContext $context): void
    {
        $user = $this->repository->getUser($context->chatId);
        if ($user === null) {
            $this->logger->error('User not found', ['chat_id' => $context->chatId]);
            $this->sender->sendOrEditMessage($context->chatId, $this->messageService->getErrorMessage());

            return;
        }
        if (isset(self::FILTER_STAGES[$user['stage']])) {
            $this->processFilterStage($context, $user['stage']);

            return;
        }
        if (isset(self::STAGE_CONFIG[$user['stage']])) {
            $this->processAdvStage($context, $user['stage'], $user);

            return;
        }
        $this->logger->debug('Unknown stage for user', ['chat_id' => $context->chatId, 'stage' => $user['stage']]);
        $this->sender->sendOrEditMessage($context->chatId, $this->messageService->getUnknownCommandMessage());
    }

    /**
     * Обрабатывает этап создания объявления.
     */
    private function processAdvStage(UpdateContext $context, string $stage, array $user): void
    {
        $config = self::STAGE_CONFIG[$stage];
        $input = $this->getAndValidateInput($context, $config, $stage);
        if ($input === null) {
            return;
        }
        $this->repository->updateTempAdv($context->chatId, [$config['field'] => $input]);
        $nextStage = $this->determineNextStage($stage, $config, $user);
        // Завершение публикации, если достигнут финал
        if ($nextStage === '') {
            if ($this->flow->finishAdv($context->chatId)) {
                $this->repository->updateUser($context->chatId, $nextStage);
            }
            return;
        }
        $this->repository->updateUser($context->chatId, $nextStage);
        $this->sender->sendOrEditMessage($context->chatId, $this->messageService->{$config['message']}());
    }

    /**
     * Получает и валидирует входные данные в зависимости от ожидаемого типа.
     */
    private function getAndValidateInput(UpdateContext $context, array $config, string $stage): ?string
    {
        if (isset($config['expect_photo'])) {
            $input = $context->photoFileId;
        } else {
            $input = $context->text;
        }
        $validation = $this->validator->{$config['validator']}($input);
        if (! $validation['result']) {
            $this->sender->sendOrEditMessage($context->chatId, $validation['message']);
            $this->logger->debug('Validation failed', ['chat_id' => $context->chatId, 'validationMessage' => $validation['message']]);

            return null;
        }

        return $input;
    }

    /**
     * Определяет следующую стадию с учётом специальных правил (например, пропуск шага при наличии username).
     */
    private function determineNextStage(string $stage, array $config, array $user): string
    {
        $nextStage = $config['next_stage'];
        if ($stage === UserStages::POST_ADV_STEP5) {
            if (! empty($user['username'])) {
                return '';
            }

            return UserStages::POST_ADV_STEP6;
        }

        return $nextStage;
    }

    /**
     * Обрабатывает этапы установки фильтра цены.
     */
    private function processFilterStage(UpdateContext $context, string $stage): void
    {
        $config = self::FILTER_STAGES[$stage];
        $validation = $this->validator->{$config['validator']}($context->text);
        if (! $validation['result']) {
            $this->sender->sendOrEditMessage($context->chatId, $validation['message']);

            return;
        }
        $this->repository->updateFilterPrice($context->chatId, $config['field'], $context->text);
        $nextStage = $config['next_stage'];

        if ($nextStage === UserStages::SET_FILTER_PRICE_APPLY) {
            $this->repository->updateUser($context->chatId, $nextStage);
            $finalMessage = $this->messageService->getFilterListMessage($context->chatId);
            $this->sender->sendOrEditMessage($context->chatId, $finalMessage['text'], null, $finalMessage['keyboard']);
        } else {
            $this->repository->updateUser($context->chatId, $nextStage);
            if ($config['message'] !== null) {
                $this->sender->sendOrEditMessage($context->chatId, $this->messageService->{$config['message']}());
            }
        }
    }
}
