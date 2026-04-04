<?php

/*
 * Текста сообщений и клавиатуры
 */
declare(strict_types=1);

namespace App\Services;

use App\Constant\CallbackData;

class TextMessagesService
{
    protected RepositoryService $repository;

    public function __construct(RepositoryService $repository)
    {
        $this->repository = $repository;
    }

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
                    'callback_data' => CallbackData::POST_ADV,
                ],
                [
                    'text' => 'Найти объявление',
                    'callback_data' => CallbackData::SEARCH_ADV,
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
        return 'Укажите корректную в российских рублях.

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
        return 'Публиковать обьявление можно раз <b>в 2 часа</b>

Повторите попытку через <b>'.(120 - $count_minutes).'</b> минут.';
    }

    public static function getContactMessage(): string
    {
        return '❗️ Контакты

❗️У вас скрытый никнейм, вам не смогут написать.
Перед подачей объявления - измените настройки приватности в Телеграме: Конфидициальность, Перессылка сообщений, Для всех!

Или укажите дополнительные контакты

<i>Например: телефон 8-902-210-99-99</i>';
    }

    public static function getFullAdvMessage(object $temp_adv_row, ?string $username): string
    {
        if (empty($username)) {
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
                    'callback_data' => CallbackData::BACK_MAIN_MENU,
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
                    'callback_data' => CallbackData::CATEGORY_CAR,
                ],
                [
                    'text' => '⚙️ Запчасти',
                    'callback_data' => CallbackData::CATEGORY_DETAIL,
                ],
            ],
            [
                [
                    'text' => 'Главное меню',
                    'callback_data' => CallbackData::BACK_MAIN_MENU,
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
                    'callback_data' => CallbackData::CATEGORY_DETAIL_DETAIL,
                ],
                [
                    'text' => '⭕️ Колёса',
                    'callback_data' => CallbackData::CATEGORY_DETAIL_WHEELS,
                ],
            ],
            [
                [
                    'text' => '🔊 Аудио и видео',
                    'callback_data' => CallbackData::CATEGORY_DETAIL_AUDIO,
                ],
                [
                    'text' => '🧰 Инструменты',
                    'callback_data' => CallbackData::CATEGORY_DETAIL_TOOLS,
                ],
            ],
            [
                [
                    'text' => '📦 Другое',
                    'callback_data' => CallbackData::CATEGORY_DETAIL_OTHER,
                ],
            ],
            [
                [
                    'text' => 'Главное меню',
                    'callback_data' => CallbackData::BACK_MAIN_MENU,
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

Укажите <b>Корректное</b> описание объявления, не более 1000 символов.

<b>ВАЖНО! Запрещено добавлять любые контакты, хештеги, ссылки и символы!</b>';
    }

    public static function getSearchMessage(): array
    {
        $result = [];
        $result['text'] = '️🔍 <b>Найти объявление</b>

<b>Фильтр объявлений</b>

Настройте фильтр и получайте уведомления о новых объявлениях с указанными вами параметрами.';
        $keyboard = [
            [
                [
                    'text' => 'Фильтр объявлений',
                    'callback_data' => CallbackData::FILTER_ADD,
                ],
                [
                    'text' => 'Главное меню',
                    'callback_data' => CallbackData::BACK_MAIN_MENU,
                ],
            ],
        ];
        $reply_markup = [
            'inline_keyboard' => $keyboard,
        ];
        $result['keyboard'] = $reply_markup;

        return $result;
    }

    public function getFilterListMessage(int $chatId): ?array
    {
        $filterList = $this->repository->getFilterList($chatId);
        if ($filterList == null) {
            return null;
        }
        if ($filterList->filter_status == 1) {
            $status_filter = 'Выключить';
        } else {
            $status_filter = 'Включить';
        }
        $result['text'] = $this->getFilterInfoMessage($filterList);
        $keyboard = [
            [
                [
                    'text' => 'Категория',
                    'callback_data' => CallbackData::FILTER_CATEGORY,
                ],
                [
                    'text' => 'Цена',
                    'callback_data' => CallbackData::FILTER_PRICE,
                ],
            ],
            [
                [
                    'text' => $status_filter,
                    'callback_data' => CallbackData::FILTER_STATUS,
                ],
            ],
            [
                [
                    'text' => 'Главное меню',
                    'callback_data' => CallbackData::BACK_MAIN_MENU,
                ],
            ],
        ];
        $reply_markup = [
            'inline_keyboard' => $keyboard,
        ];
        $result['keyboard'] = $reply_markup;

        return $result;
    }

    public function getFilterCategoryMessage(int $chatId): ?array
    {
        $filterList = $this->repository->getFilterList($chatId);
        if ($filterList == null) {
            return null;
        }
        $text_button_0 = '';
        $text_button_1 = '';

        if ($filterList->filter_category_car == 1) {
            $text_button_0 = '✅ ';
        }
        if ($filterList->filter_category_detail == 1) {
            $text_button_1 = '✅ ';
        }

        $result['text'] = $this->getFilterInfoMessage($filterList);
        $result['text'] .= '

<b>Выберите категории:</b>';
        $keyboard = [
            [
                [
                    'text' => $text_button_0.'Транспорт',
                    'callback_data' => CallbackData::FILTER_CATEGORY_CAR,
                ],
                [
                    'text' => $text_button_1.'Запчасти',
                    'callback_data' => CallbackData::FILTER_CATEGORY_DETAIL,
                ],
            ],
            [
                [
                    'text' => 'Сохранить',
                    'callback_data' => CallbackData::FILTER_APPLY,
                ],
            ],
        ];
        $reply_markup = [
            'inline_keyboard' => $keyboard,
        ];
        $result['keyboard'] = $reply_markup;

        return $result;
    }

    private function getFilterInfoMessage(object $filterList): string
    {

        if ($filterList->filter_category_car === false && $filterList->filter_category_detail === false) {
            $text_add_type = 'Не выбрано';
        } else {
            $text_add_type = '';
            if ($filterList->filter_category_car == 1) {
                $text_add_type .= '<b>•</b><i>Транспорт</i> ';
            }
            if ($filterList->filter_category_detail == 1) {
                $text_add_type .= ' <b>•</b><i>Запчасти</i>';
            }
        }
        if ($filterList->filter_price_min == null) {
            $text_add_price = 'Не выбрано';
        } else {
            $text_add_price = "От <b>$filterList->filter_price_min</b> до <b>$filterList->filter_price_max</b>";
        }
        if ($filterList->filter_status == 1) {
            $text_status_filter = 'ВКЛЮЧЕН 💚';
        } else {
            $text_status_filter = 'ВЫКЛЮЧЕН ❤️';
        }

        return "⚙️ <b>Фильтр объявлений</b>

О появлении новых объявлений будут приходить уведомления!

    <b>Категории объявлений:</b>
    $text_add_type

    <b>Цена:</b>
    $text_add_price

Фильтр <b>$text_status_filter</b>";
    }

    public static function getFilterPriceMessage(): array
    {
        $result = [];
        $result['text'] = '💲 <b>Минимальная Цена</b>

Укажите минимальную цену

<i>Например: 10000</i>';
        $result['keyboard'] = null;

        return $result;
    }

    public static function getFilterPriceMaxMessage(): string
    {
        return '💲 <b>Максимальная Цена</b>

Укажите максимальную цену

<i>Например: 2000000</i>';
    }

    public static function getUnsupportedMediaMessage(): string
    {
        return 'Бот принимает только текстовые сообщения и команды.';
    }

    public static function getErrorMessage(): string
    {
        return 'Произошла ошибка, попробуйте позже.';
    }

    public static function getUnknownCommandMessage(): string
    {
        return 'Неизвестная команда.';
    }

    public static function getCorrectTitleMessage(): string
    {
        return 'Отправьте текст, не более 100 символов.';
    }

    public static function getCorrectPhotoMessage(): string
    {
        return 'Отправьте фотографию, а не файл\текст';
    }

    public static function getCorrectExtraContactMessage(): string
    {
        return 'Отправьте текст, не более 100 символов';
    }
}
