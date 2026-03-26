<?php

declare(strict_types=1);

namespace App\Constant;

class UserStages
{
    // Создание объявления Транспорт
    public const POST_ADV_STEP1 = 'post_set_title_step1';

    public const POST_ADV_STEP2 = 'post_set_caryear_step2';

    public const POST_ADV_STEP3 = 'post_set_price_step3';

    public const POST_ADV_STEP4 = 'post_set_description_step4';

    public const POST_ADV_STEP5 = 'post_set_photo_step5';

    public const POST_ADV_STEP6 = 'post_set_contact_step6';

    // Создание объявления Запчасти
    public const POST_ADV_DETAIL_STEP1 = 'post_set_title_detail_step1';

    public const POST_ADV_DETAIL_STEP2 = 'post_set_price_detail_step2';

    public const POST_ADV_DETAIL_STEP3 = 'post_set_description_detail_step3';

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

    // Имена подкатегории Запчасти
    public const CATEGORY_NAME_DETAIL = 'Запчасти';

    public const CATEGORY_NAME_WHEELS = 'Колёса';

    public const CATEGORY_NAME_AUDIO = 'Аудио';

    public const CATEGORY_NAME_TOOLS = 'Инструменты';

    public const CATEGORY_NAME_OTHERS = 'Другое';
}
