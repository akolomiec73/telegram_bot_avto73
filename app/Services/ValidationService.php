<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Сервис валидации
 */
class ValidationService
{
    private const MAX_DESCRIPTION_LENGTH = 1000;

    private const MAX_TEXT_LENGTH = 100;

    /**
     * Базовая валидация запрещает любые HTML-теги
     */
    private function basicValidate(?string $text): bool
    {
        return strip_tags($text) === $text;
    }

    /**
     * Валидация года выпуска авто (4 символа и Год должен быть от 1901 до текущего+1)
     */
    public function validateCarYear(?string $year): array
    {
        $yearInt = (int) $year;
        $resultValidate = preg_match('/^\d{4}$/', (string) $year) &&
            $yearInt > 1900 &&
            $yearInt < date('Y') + 1;

        return [
            'result' => $resultValidate,
            'message' => TextMessagesService::getCorrectCarYearMessage(),
        ];
    }

    /**
     * Валидация цены (положительное, целое число)
     */
    public function validatePrice(?string $price): array
    {
        return [
            'result' => filter_var($price, FILTER_VALIDATE_INT) !== false && $price > 0,
            'message' => TextMessagesService::getCorrectPriceMessage(),
        ];
    }

    /**
     * Валидация описания (не пустое, длина до 1000 символов)
     */
    public function validateDescription(?string $description): array
    {
        return $this->validateText($description, self::MAX_DESCRIPTION_LENGTH, TextMessagesService::getCorrectDescriptionMessage());
    }

    /**
     * Валидация фото (не пустое)
     */
    public function validateIsPhoto(?string $fileId): array
    {
        return [
            'result' => ! empty($fileId),
            'message' => TextMessagesService::getCorrectPhotoMessage(),
        ];
    }

    /**
     * Валидация доп контактов (не пустое не более 100 символов)
     */
    public function validateExtraContact(?string $text): array
    {
        return $this->validateText($text, self::MAX_TEXT_LENGTH, TextMessagesService::getCorrectExtraContactMessage());
    }

    /**
     * Валидация названия\марки авто (не пустое не более 100 символов)
     */
    public function validateTitle(?string $text): array
    {
        return $this->validateText($text, self::MAX_TEXT_LENGTH, TextMessagesService::getCorrectTitleMessage());
    }

    private function validateText(?string $text, int $maxLength, string $errorMessage): array
    {
        $isValid = $text !== null && $text !== '' && mb_strlen($text, 'UTF-8') <= $maxLength && $this->basicValidate($text);

        return [
            'result' => $isValid,
            'message' => $errorMessage,
        ];
    }
}
