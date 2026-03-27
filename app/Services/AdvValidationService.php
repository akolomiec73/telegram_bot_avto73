<?php

/*
 * Сервис валидации
 */
declare(strict_types=1);

namespace App\Services;

class AdvValidationService
{
    private const MIN_PRICE = 0;

    private const MAX_DESCRIPTION_LENGTH = 1000;

    private const MAX_CONTACT_LENGTH = 100;

    private const TIME_LIMIT_MINUTES = 720;

    private const ALLOWED_TAGS = '<b><i><u><strong><em>';

    /**
     * Валидация основного сообщения от пользователя
     */
    public function validateMainText(?string $text): bool
    {
        if (empty($text)) {
            return true;
        }
        $sanitized = htmlspecialchars(strip_tags($text, self::ALLOWED_TAGS), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $sanitized === $text;
    }

    /**
     * Валидация года выпуска авто (4 цифры)
     */
    public function validateCarYear(?string $year): array
    {
        $resultValidate['message'] = TextMessagesService::getCorrectCarYearMessage();
        $resultValidate['result'] = ! empty($year) && preg_match('#^[0-9]{4}$#', $year) === 1;

        return $resultValidate;
    }

    /**
     * Валидация цены (положительное число)
     */
    public function validatePrice(?string $price): array
    {
        $resultValidate['message'] = TextMessagesService::getCorrectPriceMessage();
        $resultValidate['result'] = ! empty($price) && is_numeric($price) && (float) $price > self::MIN_PRICE;

        return $resultValidate;
    }

    /**
     * Валидация описания (не пустое, длина до 1000 символов)
     */
    public function validateDescription(?string $description): array
    {
        $resultValidate['message'] = TextMessagesService::getCorrectDescriptionMessage();
        $resultValidate['result'] = ! empty($description) && mb_strlen($description, 'UTF-8') <= self::MAX_DESCRIPTION_LENGTH;

        return $resultValidate;
    }

    /**
     * Валидация фото (не пустое)
     */
    public function validateIsPhoto(?string $fileId): array
    {
        $resultValidate['message'] = 'Отправьте фотографию, а не файл\текст';
        $resultValidate['result'] = ! empty($fileId);

        return $resultValidate;
    }

    /**
     * Валидация доп контактов (не пустое не более 100 символов)
     */
    public function validateExtraContact(?string $text): array
    {
        $resultValidate['message'] = 'Отправьте текст, не более 100 символов';
        $resultValidate['result'] = ! empty($text) && mb_strlen($text, 'UTF-8') <= self::MAX_CONTACT_LENGTH;

        return $resultValidate;
    }

    /**
     * Валидация названия для запчастей (не пустое не более 100 символов)
     */
    public function validateTitle(?string $text): array
    {
        $resultValidate['message'] = 'Отправьте текст, не более 100 символов';
        $resultValidate['result'] = ! empty($text) && mb_strlen($text, 'UTF-8') <= self::MAX_CONTACT_LENGTH;

        return $resultValidate;
    }

    /**
     * Валидация лимита отправки объявлений
     */
    public function validateTimeLimit(?string $date_post): int|bool
    {
        if ($date_post === null) {
            return true;
        }
        $date_now = date('Y-m-d H:i:s');
        $diff = strtotime($date_post) - strtotime($date_now);
        $count_minutes = (int) abs(round($diff / 60));
        /* Некорректно, нужно переделать
          if ($count_minutes < self::TIME_LIMIT_MINUTES) {
           return false;
        }*/

        return $count_minutes;
    }
}
