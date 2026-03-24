<?php

declare(strict_types=1);

namespace App\Services;

class TextMessagesService
{
    public static function getStartMessage(): string
    {
        return '🚗 <b>Главное меню</b>

Авто Барахолка Ульяновск | <b>avto73ru</b>

Город: <b>Ульяновск</b>
Канал: @avto73ru

Главная наша цель - создание удобной платформы для продажи и покупки б\у авто и запчастей в г.Ульяновск.';
    }

    public static function getCarYearMessage(): string
    {
        return '🕑 <b>Год выпуска</b>

Укажите год выпуска авто.

<i>Например: 2016</i>';
    }

    public static function getPriceMessage(): string
    {
        return ' <b>Цена</b>

Укажите цену.';
    }

    public static function getCorrectCarYearMessage(): string
    {
        return 'Укажите корректный год выпуска авто.

<i>Например: 2016</i>';
    }

    public static function getDescriptionMessage(): string
    {
        return '📝 <b>Описание</b>

Укажите описание объявления.

<b>ВАЖНО! Запрещено добавлять любые контакты, хештеги и ссылки, иначе объявление может быть удалено!</b>';
    }

    public static function getCorrectPriceMessage(): string
    {
        return 'Введите цену в российских рублях.

<i>Например: 150000</i>';
    }

    public static function getPhotoMessage(): string
    {
        return '📷 <b>Фото</b>

Добавьте <b>одно</b> фото.

<i>Остальные фотографии можно прикрепить в комментариях</i>';
    }

    public static function getTimeLimitMessage(int $min_around): string
    {
        return 'Публиковать обьявление можно раз <b>в 12 часов</b>

Повторите попытку через <b>'.(960 - $min_around).'</b> минут.';
    }

    public static function getContactMessage(): string
    {
        return '❗️ Контакты

❗️У вас скрытый никнейм, вам не смогут написать.
Перед подачей объявления - измените настройки приватности в Телеграме: Конфидициальность, Перессылка сообщений, Для всех!

Или укажите дополнительные контакты

<i>Например: телефон 8-902-210-99-99</i>';
    }

    public static function getFullAdvMessage(object $temp_adv_row, string $username): string
    {
        return "<i>$temp_adv_row->adv_category > $temp_adv_row->adv_car_mark > </i>

<b>$temp_adv_row->adv_car_mark, $temp_adv_row->adv_car_year_realise г.</b>

💲 <b>number_format($temp_adv_row->adv_price, 0, ',', ' ') руб.</b>

$temp_adv_row->adv_description

Продавец: @$username";
    }

    public static function getFinishMessage(): string
    {
        return '👍 <b>Публикация</b>

    Объявление успешно опубликовано в канале @avto73ru';
    }

    public static function getPostMessage(): string
    {
        return '️ Перед подачей объявления - измените настройки приватности в Телеграме: Конфидициальность, Перессылка сообщений, Для всех! Иначе вам не смогут написать❗️

🚦 <b>Категория</b>

Выберите категорию объявления из представленных.';
    }

    public static function getCategoryCarMessage(): string
    {
        return '❗❔ <b>Марка и модель авто</b>

Укажите марку и модель вашего авто.

<i>Например Audi RS7 \ Mercedes C180 \ BMW 3 \ Chevrolet Niva \ Ford Focus \ Hyundai Solaris \ Volkswagen Golf \ ВАЗ 2114</i>';
    }
}
