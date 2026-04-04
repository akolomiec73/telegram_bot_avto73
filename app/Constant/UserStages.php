<?php

declare(strict_types=1);

namespace App\Constant;

/**
 * Наименование стадий пользователя, для сохранения в бд
 */
final class UserStages
{
    // Создание объявления Транспорт
    public const POST_ADV_STEP1 = 'post_adv_step1';

    public const POST_ADV_STEP2 = 'post_adv_step2';

    public const POST_ADV_STEP3 = 'post_adv_step3';

    public const POST_ADV_STEP4 = 'post_adv_step4';

    public const POST_ADV_STEP5 = 'post_adv_step5';

    public const POST_ADV_STEP6 = 'post_adv_step6';

    // Создание объявления Запчасти
    public const POST_ADV_DETAIL_STEP1 = 'post_adv_detail_step1';

    public const POST_ADV_DETAIL_STEP2 = 'post_adv_detail_step2';

    // Установки фильтров цены
    public const SET_FILTER_PRICE_MIN = 'set_filter_price_min';

    public const SET_FILTER_PRICE_MAX = 'set_filter_price_max';

    public const SET_FILTER_PRICE_APPLY = 'set_filter_price_apply';
}
