<?php

declare(strict_types=1);

namespace App\Services\Message;

/**
 * Статические текста сообщений
 */
readonly class MessageTemplates
{
    public function getCarYearMessage(): string
    {
        return '🕑 <b>Год выпуска</b>

Укажите год выпуска авто.

<i>Например: 2016</i>';
    }

    public function getStartMessage(): string
    {
        return '🚗 <b>Главное меню</b>

Авто Барахолка Ульяновск | <b>avto73ru</b>

Город: <b>Ульяновск</b>
Канал: @avto73ru

Главная наша цель - создание удобной платформы для продажи и покупки б\у авто и запчастей в г.Ульяновск.';
    }

    public function getPriceMessage(): string
    {
        return '💲 <b>Цена</b>

Укажите цену.';
    }

    public function getCorrectCarYearMessage(): string
    {
        return 'Укажите корректный год выпуска авто.

<i>Например: 2016</i>';
    }

    public function getDescriptionMessage(): string
    {
        return '📝 <b>Описание</b>

Укажите описание объявления.

<b>ВАЖНО! Запрещено добавлять любые контакты, хештеги и ссылки, иначе объявление может быть удалено!</b>';
    }

    public function getCorrectPriceMessage(): string
    {
        return 'Укажите корректную в российских рублях.

<i>Например: 150000</i>';
    }

    public function getPhotoMessage(): string
    {
        return '📷 <b>Фото</b>

Добавьте <b>одно</b> фото.

<i>Остальные фотографии можно прикрепить в комментариях</i>';
    }

    public function getContactMessage(): string
    {
        return '❗️ Контакты

❗️У вас скрытый никнейм, вам не смогут написать.
Перед подачей объявления - измените настройки приватности в Телеграме: Конфидециальность, Пересылка сообщений, Для всех!

Или укажите дополнительные контакты

<i>Например: телефон 8-902-210-99-99</i>';
    }

    public function getFinishMessage(): string
    {
        return '👍 <b>Публикация</b>

    Объявление успешно опубликовано в канале @avto73ru';
    }

    public function getPostMessage(): string
    {
        return '️ Перед подачей объявления - измените настройки приватности в Телеграме: Конфидициальность, Перессылка сообщений, Для всех! Иначе вам не смогут написать❗️

🚦 <b>Категория</b>

Выберите категорию объявления из представленных.';
    }

    public function getCategoryCarMessage(): string
    {
        return '️❗❔ <b>Марка и модель авто</b>

Укажите марку и модель вашего авто.

<i>Например Audi RS7 \ Mercedes C180 \ BMW 3 \ Chevrolet Niva \ Ford Focus \ Hyundai Solaris \ Volkswagen Golf \ ВАЗ 2114</i>';
    }

    public function getCategoryDetailMessage(): string
    {
        return '️🚦 <b>Категория</b>

Выберите категорию объявления из представленных.';
    }

    public function getCategoryDetailDetailMessage(): string
    {
        return '❔ <b>Название</b>

Укажите короткое название объявления.

<i>Фара левая на ваз 2114 \ Двигатель mercedes c180</i>';
    }

    public function getCategoryDetailWheelsMessage(): string
    {
        return '❔ <b>Название</b>

Укажите короткое название объявления.

<i>диски R12 на ваз 2114 \ Зимняя резина </i>';
    }

    public function getCategoryDetailAudioMessage(): string
    {
        return '❔ <b>Название</b>

Укажите короткое название объявления.

<i>Магнитола pioneer \ Динамики Pride Ruby </i>';
    }

    public function getCategoryDetailToolsMessage(): string
    {
        return '❔ <b>Название</b>

Укажите короткое название объявления.

<i>Набор инструментов</i>';
    }

    public function getCategoryDetailOthersMessage(): string
    {
        return '❔ <b>Название</b>

Укажите короткое название объявления.

<i>Накидки на сидения</i>';
    }

    public function getCorrectDescriptionMessage(): string
    {
        return '📝 <b>Описание</b>

Укажите <b>Корректное</b> описание объявления, не более 1000 символов.

<b>ВАЖНО! Запрещено добавлять любые контакты, хештеги, ссылки и символы!</b>';
    }

    public function getSearchMessage(): string
    {
        return '️🔍 <b>Найти объявление</b>

<b>Фильтр объявлений</b>

Настройте фильтр и получайте уведомления о новых объявлениях с указанными вами параметрами.';
    }

    public function getFilterPriceMessage(): string
    {
        return '💲 <b>Минимальная Цена</b>

Укажите минимальную цену

<i>Например: 10000</i>';
    }

    public function getFilterPriceMaxMessage(): string
    {
        return '💲 <b>Максимальная Цена</b>

Укажите максимальную цену

<i>Например: 2000000</i>';
    }

    public function getUnsupportedMediaMessage(): string
    {
        return 'Бот принимает только текстовые сообщения и команды.';
    }

    public function getErrorMessage(): string
    {
        return 'Произошла ошибка, попробуйте позже.';
    }

    public function getUnknownCommandMessage(): string
    {
        return 'Неизвестная команда.';
    }

    public function getCorrectTitleMessage(): string
    {
        return 'Отправьте текст, не более 100 символов.';
    }

    public function getCorrectPhotoMessage(): string
    {
        return 'Отправьте фотографию, а не файл\текст';
    }

    public function getCorrectExtraContactMessage(): string
    {
        return 'Отправьте текст, не более 100 символов';
    }
}
