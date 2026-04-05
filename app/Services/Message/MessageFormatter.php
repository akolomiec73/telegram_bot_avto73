<?php

declare(strict_types=1);

namespace App\Services\Message;

/**
 * Динамические текста сообщений
 */
readonly class MessageFormatter
{
    public function getTimeLimitMessage(int $count_minutes): string
    {
        return 'Публиковать обьявление можно раз <b>в 2 часа</b>

Повторите попытку через <b>'.(120 - $count_minutes).'</b> минут.';
    }

    public function getFullAdvMessage(object $temp_adv_row, ?string $username): string
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

    public function getFilterInfoMessage(object $filterList): string
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
}
