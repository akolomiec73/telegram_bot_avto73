<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Message\KeyboardBuilder;
use App\Services\Message\MessageFormatter;
use App\Services\Message\MessageTemplates;

/**
 * Фасад, объединяющий динамические, статические текста и клавиатуры
 */
readonly class MessageService
{
    public function __construct(
        private RepositoryService $repository,
        private MessageTemplates $templates,
        private MessageFormatter $formatter,
        private KeyboardBuilder $keyboard
    ) {}

    public function getStartMessage(): array
    {
        return [
            'text' => $this->templates->getStartMessage(),
            'keyboard' => $this->keyboard->getStartKeyboard(),
        ];
    }

    public function getCarYearMessage(): string
    {
        return $this->templates->getCarYearMessage();
    }

    public function getPriceMessage(): string
    {
        return $this->templates->getPriceMessage();
    }

    public function getCorrectCarYearMessage(): string
    {
        return $this->templates->getCorrectCarYearMessage();
    }

    public function getDescriptionMessage(): string
    {
        return $this->templates->getDescriptionMessage();
    }

    public function getCorrectPriceMessage(): string
    {
        return $this->templates->getCorrectPriceMessage();
    }

    public function getPhotoMessage(): string
    {
        return $this->templates->getPhotoMessage();
    }

    public function getTimeLimitMessage(int $count_minutes): string
    {
        return $this->formatter->getTimeLimitMessage($count_minutes);
    }

    public function getContactMessage(): string
    {
        return $this->templates->getContactMessage();
    }

    public function getFullAdvMessage(object $temp_adv_row, ?string $username): string
    {
        return $this->formatter->getFullAdvMessage($temp_adv_row, $username);
    }

    public function getFinishMessage(): array
    {
        return [
            'text' => $this->templates->getFinishMessage(),
            'keyboard' => $this->keyboard->getFinishKeyboard(),
        ];
    }

    public function getPostMessage(): array
    {
        return [
            'text' => $this->templates->getPostMessage(),
            'keyboard' => $this->keyboard->getPostKeyboard(),
        ];
    }

    public function getCategoryCarMessage(): array
    {
        return [
            'text' => $this->templates->getCategoryCarMessage(),
            'keyboard' => null,
        ];
    }

    public function getCategoryDetailMessage(): array
    {
        return [
            'text' => $this->templates->getCategoryDetailMessage(),
            'keyboard' => $this->keyboard->getCategoryDetailKeyboard(),
        ];
    }

    public function getCategoryDetailDetailMessage(): array
    {
        return [
            'text' => $this->templates->getCategoryDetailDetailMessage(),
            'keyboard' => null,
        ];
    }

    public function getCategoryDetailWheelsMessage(): array
    {
        return [
            'text' => $this->templates->getCategoryDetailWheelsMessage(),
            'keyboard' => null,
        ];
    }

    public function getCategoryDetailAudioMessage(): array
    {
        return [
            'text' => $this->templates->getCategoryDetailAudioMessage(),
            'keyboard' => null,
        ];
    }

    public function getCategoryDetailToolsMessage(): array
    {
        return [
            'text' => $this->templates->getCategoryDetailToolsMessage(),
            'keyboard' => null,
        ];
    }

    public function getCategoryDetailOthersMessage(): array
    {
        return [
            'text' => $this->templates->getCategoryDetailOthersMessage(),
            'keyboard' => null,
        ];
    }

    public function getCorrectDescriptionMessage(): string
    {
        return $this->templates->getCorrectDescriptionMessage();
    }

    public function getSearchMessage(): array
    {
        return [
            'text' => $this->templates->getSearchMessage(),
            'keyboard' => $this->keyboard->getSearchKeyboard(),
        ];
    }

    public function getFilterListMessage(int $chatId): ?array
    {
        $filterList = $this->repository->getFilterList($chatId);
        if ($filterList == null) {
            return null;
        }

        return [
            'text' => $this->getFilterInfoMessage($filterList),
            'keyboard' => $this->keyboard->getFilterListKeyboard($filterList->filter_status),
        ];
    }

    public function getFilterCategoryMessage(int $chatId): ?array
    {
        $filterList = $this->repository->getFilterList($chatId);
        if ($filterList == null) {
            return null;
        }
        $textMessage = $this->getFilterInfoMessage($filterList);
        $textMessage .= '

<b>Выберите категории:</b>';

        return [
            'text' => $textMessage,
            'keyboard' => $this->keyboard->getFilterCategoryKeyboard($filterList),
        ];
    }

    private function getFilterInfoMessage(object $filterList): string
    {
        return $this->formatter->getFilterInfoMessage($filterList);
    }

    public function getFilterPriceMessage(): array
    {
        return [
            'text' => $this->templates->getFilterPriceMessage(),
            'keyboard' => null,
        ];
    }

    public function getFilterPriceMaxMessage(): string
    {
        return $this->templates->getFilterPriceMaxMessage();
    }

    public function getUnsupportedMediaMessage(): string
    {
        return $this->templates->getUnsupportedMediaMessage();
    }

    public function getErrorMessage(): string
    {
        return $this->templates->getErrorMessage();
    }

    public function getUnknownCommandMessage(): string
    {
        return $this->templates->getUnknownCommandMessage();
    }

    public function getCorrectTitleMessage(): string
    {
        return $this->templates->getCorrectTitleMessage();
    }

    public function getCorrectPhotoMessage(): string
    {
        return $this->templates->getCorrectPhotoMessage();
    }

    public function getCorrectExtraContactMessage(): string
    {
        return $this->templates->getCorrectExtraContactMessage();
    }
}
