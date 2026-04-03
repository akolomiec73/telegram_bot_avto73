<?php

declare(strict_types=1);

namespace App\Constant;

class UserStages
{
    // Создание объявления Транспорт
    public const POST_ADV_STEP1 = 'post_set_category_step1';

    public const POST_ADV_STEP2 = 'post_set_title_step2';

    public const POST_ADV_STEP3 = 'post_set_caryear_step3';

    public const POST_ADV_STEP4 = 'post_set_price_step4';

    public const POST_ADV_STEP5 = 'post_set_description_step5';

    public const POST_ADV_STEP6 = 'post_set_photo_step6';

    public const POST_ADV_STEP7 = 'post_set_contact_step7';

    // Создание объявления Запчасти
    public const POST_ADV_DETAIL_STEP1 = 'post_set_category_detail_step1';

    public const POST_ADV_DETAIL_STEP2 = 'post_set_title_detail_step2';

    // Установки фильтров цены
    public const SET_FILTER_PRICE_MIN = 'set_filter_price_min';

    public const SET_FILTER_PRICE_MAX = 'set_filter_price_max';

    public const SET_FILTER_PRICE_APPLY = 'set_filter_price_apply';

    // callback для кнопок
    public const BUTTON_BACK_MAIN_MENU = 'back_main_menu';

    public const BUTTON_POST_ADV = 'post_adv';

    public const BUTTON_SEARCH_ADV = 'search_adv';

    public const BUTTON_CATEGORY_CAR = 'category_car';

    public const BUTTON_CATEGORY_DETAIL = 'category_detail';

    public const BUTTON_CATEGORY_DETAIL_DETAIL = 'category_detail_detail';

    public const BUTTON_CATEGORY_DETAIL_WHEELS = 'category_detail_wheels';

    public const BUTTON_CATEGORY_DETAIL_AUDIO = 'category_detail_audio';

    public const BUTTON_CATEGORY_DETAIL_TOOLS = 'category_detail_tools';

    public const BUTTON_CATEGORY_DETAIL_OTHER = 'category_detail_other';

    public const BUTTON_FILTER_ADD = 'filter_add';

    public const BUTTON_FILTER_CATEGORY = 'filter_category';

    public const BUTTON_FILTER_PRICE = 'filter_price';

    public const BUTTON_FILTER_STATUS = 'filter_status';

    public const BUTTON_FILTER_CATEGORY_CAR = 'filter_category_car';

    public const BUTTON_FILTER_CATEGORY_DETAIL = 'filter_category_detail';

    public const BUTTON_FILTER_APPLY = 'apply_filter';

    // Имена подкатегории Запчасти
    public const CATEGORY_NAME_DETAIL = 'Запчасти';

    public const CATEGORY_NAME_WHEELS = 'Колёса';

    public const CATEGORY_NAME_AUDIO = 'Аудио';

    public const CATEGORY_NAME_TOOLS = 'Инструменты';

    public const CATEGORY_NAME_OTHERS = 'Другое';
}
