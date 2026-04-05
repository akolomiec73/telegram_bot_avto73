<?php

declare(strict_types=1);

namespace App\Services\Message;

use App\Constant\CallbackData;

/**
 * Клавиатуры для сообщений
 */
readonly class KeyboardBuilder
{
    public function getStartKeyboard(): array
    {
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

        return [
            'inline_keyboard' => $keyboard,
        ];
    }

    public function getFinishKeyboard(): array
    {
        $keyboard = [
            [
                [
                    'text' => 'Главное меню',
                    'callback_data' => CallbackData::BACK_MAIN_MENU,
                ],
            ],
        ];

        return [
            'inline_keyboard' => $keyboard,
        ];
    }

    public function getPostKeyboard(): array
    {
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

        return [
            'inline_keyboard' => $keyboard,
        ];
    }

    public function getCategoryDetailKeyboard(): array
    {
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

        return [
            'inline_keyboard' => $keyboard,
        ];
    }

    public function getSearchKeyboard(): array
    {
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

        return [
            'inline_keyboard' => $keyboard,
        ];
    }

    public function getFilterListKeyboard(int $filterStatus): ?array
    {
        if ($filterStatus == 1) {
            $textButton = 'Выключить';
        } else {
            $textButton = 'Включить';
        }
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
                    'text' => $textButton,
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

        return [
            'inline_keyboard' => $keyboard,
        ];
    }

    public function getFilterCategoryKeyboard(object $filterList): ?array
    {
        $text_button_0 = '';
        $text_button_1 = '';

        if ($filterList->filter_category_car == 1) {
            $text_button_0 = '✅ ';
        }
        if ($filterList->filter_category_detail == 1) {
            $text_button_1 = '✅ ';
        }
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

        return [
            'inline_keyboard' => $keyboard,
        ];
    }
}
