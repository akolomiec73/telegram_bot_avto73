<?php

/*
 * Текста сообщений и клавиатуры
 */
declare(strict_types=1);

namespace App\Services;

use App\Constant\UserStages;

class TextMessagesService
{
    public static function getStartMessage(): array
    {
        $result = [];
        $result['text'] = '🚗 <b>Главное меню</b>

Авто Барахолка Ульяновск | <b>avto73ru</b>

Город: <b>Ульяновск</b>
Канал: @avto73ru

Главная наша цель - создание удобной платформы для продажи и покупки б\у авто и запчастей в г.Ульяновск.';
        $keyboard = [
            [
                [
                    'text' => 'Подать объявление',
                    'callback_data' => UserStages::BUTTON_POST_ADV,
                ],
                [
                    'text' => 'Найти объявление',
                    'callback_data' => UserStages::BUTTON_SEARCH_ADV,
                ],
            ],
            [
                [
                    'text' => 'Объявления',
                    'url' => 'https://t.me/avto73ru',
                ],
            ],
        ];
        $reply_markup = [
            'inline_keyboard' => $keyboard,
        ];
        $result['keyboard'] = $reply_markup;

        return $result;
    }

    public static function getCarYearMessage(): string
    {
        return '🕑 <b>Год выпуска</b>

Укажите год выпуска авто.

<i>Например: 2016</i>';
    }

    public static function getPriceMessage(): string
    {
        return '💲 <b>Цена</b>

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

    public static function getTimeLimitMessage(int $count_minutes): string
    {
        return 'Публиковать обьявление можно раз <b>в 12 часов</b>

Повторите попытку через <b>'.(720 - $count_minutes).'</b> минут.';
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
        if ($username == 'unknown') {
            $contactText = "Продавец: $temp_adv_row->adv_extra_contact";
        } else {
            $contactText = "Продавец: @$username";
        }
        if ($temp_adv_row->adv_car_year_realise) {
            $text_car_year = ", $temp_adv_row->adv_car_year_realise  г.";
        } else {
            $text_car_year = '';
        }
        $simple_price = number_format($temp_adv_row->adv_price, 0, ',', ' ');

        return "<i>$temp_adv_row->adv_category > $temp_adv_row->adv_car_mark > </i>

<b>$temp_adv_row->adv_car_mark $text_car_year</b>

💲 <b>$simple_price руб.</b>

$temp_adv_row->adv_description

$contactText";
    }

    public static function getFinishMessage(): array
    {
        $result = [];
        $result['text'] = '👍 <b>Публикация</b>

    Объявление успешно опубликовано в канале @avto73ru';
        $keyboard = [
            [
                [
                    'text' => 'Главное меню',
                    'callback_data' => UserStages::BUTTON_BACK_MAIN_MENU,
                ],
            ],
        ];
        $reply_markup = [
            'inline_keyboard' => $keyboard,
        ];
        $result['keyboard'] = $reply_markup;

        return $result;
    }

    public static function getPostMessage(): array
    {
        $result = [];
        $result['text'] = '️ Перед подачей объявления - измените настройки приватности в Телеграме: Конфидициальность, Перессылка сообщений, Для всех! Иначе вам не смогут написать❗️

🚦 <b>Категория</b>

Выберите категорию объявления из представленных.';
        $keyboard = [
            [
                [
                    'text' => '🚗 Транспорт',
                    'callback_data' => UserStages::BUTTON_CATEGORY_CAR,
                ],
                [
                    'text' => '⚙️ Запчасти',
                    'callback_data' => UserStages::BUTTON_CATEGORY_DETAIL,
                ],
            ],
            [
                [
                    'text' => 'Главное меню',
                    'callback_data' => UserStages::BUTTON_BACK_MAIN_MENU,
                ],
            ],
        ];
        $reply_markup = [
            'inline_keyboard' => $keyboard,
        ];
        $result['keyboard'] = $reply_markup;

        return $result;
    }

    public static function getCategoryCarMessage(): array
    {
        $result = [];
        $result['text'] = '❗❔ <b>Марка и модель авто</b>

Укажите марку и модель вашего авто.

<i>Например Audi RS7 \ Mercedes C180 \ BMW 3 \ Chevrolet Niva \ Ford Focus \ Hyundai Solaris \ Volkswagen Golf \ ВАЗ 2114</i>';
        $result['keyboard'] = null;

        return $result;
    }

    public static function getCategoryDetailMessage(): array
    {
        $result = [];
        $result['text'] = '🚦 <b>Категория</b>

Выберите категорию объявления из представленных.';
        $keyboard = [
            [
                [
                    'text' => '⚙️ Запчасти',
                    'callback_data' => UserStages::BUTTON_CATEGORY_DETAIL_DETAIL,
                ],
                [
                    'text' => '⭕️ Колёса',
                    'callback_data' => UserStages::BUTTON_CATEGORY_DETAIL_WHEELS,
                ],
            ],
            [
                [
                    'text' => '🔊 Аудио и видео',
                    'callback_data' => UserStages::BUTTON_CATEGORY_DETAIL_AUDIO,
                ],
                [
                    'text' => '🧰 Инструменты',
                    'callback_data' => UserStages::BUTTON_CATEGORY_DETAIL_TOOLS,
                ],
            ],
            [
                [
                    'text' => '📦 Другое',
                    'callback_data' => UserStages::BUTTON_CATEGORY_DETAIL_OTHER,
                ],
            ],
            [
                [
                    'text' => 'Главное меню',
                    'callback_data' => UserStages::BUTTON_BACK_MAIN_MENU,
                ],
            ],
        ];
        $reply_markup = [
            'inline_keyboard' => $keyboard,
        ];
        $result['keyboard'] = $reply_markup;

        return $result;
    }

    public static function getCategoryDetailDetailMessage(): array
    {
        $result = [];
        $result['text'] = '❔ <b>Название</b>

Укажите короткое название объявления.

<i>Фара левая на ваз 2114 \ Двигатель mercedes c180</i>';
        $result['keyboard'] = null;
        return $result;
    }

    public static function getCategoryDetailWheelsMessage(): array
    {
        $result = [];
        $result['text'] = '❔ <b>Название</b>

Укажите короткое название объявления.

<i>диски R12 на ваз 2114 \ Зимняя резина </i>';
        $result['keyboard'] = null;
        return $result;
    }

    public static function getCategoryDetailAudioMessage(): array
    {
        $result = [];
        $result['text'] = '❔ <b>Название</b>

Укажите короткое название объявления.

<i>Магнитола pioneer \ Динамики Pride Ruby </i>';
        $result['keyboard'] = null;
        return $result;
    }

    public static function getCategoryDetailToolsMessage(): array
    {
        $result = [];
        $result['text'] = '❔ <b>Название</b>

Укажите короткое название объявления.

<i>Набор инструментов</i>';
        $result['keyboard'] = null;
        return $result;
    }

    public static function getCategoryDetailOthersMessage(): array
    {
        $result = [];
        $result['text'] = '❔ <b>Название</b>

Укажите короткое название объявления.

<i>Накидки на сидения</i>';
        $result['keyboard'] = null;
        return $result;
    }

    public static function getCorrectDescriptionMessage(): string
    {
        return '📝 <b>Описание</b>

Укажите <b>Корректное</b> описание объявления.

<b>ВАЖНО! Запрещено добавлять любые контакты, хештеги и ссылки, иначе объявление может быть удалено!</b>';
    }
}
