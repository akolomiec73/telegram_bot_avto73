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
    public function validateCarYear(?string $year): bool
    {
        if (empty($year)) {
            return false;
        }

        return preg_match('#^[0-9]{4}$#', $year) === 1;
    }

    /**
     * Валидация цены (положительное число)
     */
    public function validatePrice(?string $price): bool
    {
        if (empty($price)) {
            return false;
        }

        return is_numeric($price) && (float) $price > self::MIN_PRICE;
    }

    /**
     * Валидация описания (не пустое, длина до 1000 символов)
     */
    public function validateDescription(?string $description): bool
    {
        return ! empty($description) && mb_strlen($description, 'UTF-8') <= self::MAX_DESCRIPTION_LENGTH;
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
