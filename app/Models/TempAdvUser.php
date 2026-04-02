<?php

/*
 * Модель таблицы запоминающей объявление пользователя
 */
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempAdvUser extends Model
{
    protected $table = 'temp_adv_users';

    protected $fillable = ['adv_category', 'adv_car_mark', 'adv_car_year_realise', 'adv_price', 'adv_description', 'adv_photo', 'adv_extra_contact'];

    public function botUser()
    {
        return $this->belongsTo(BotUsers::class, 'id_bot_user');
    }
}
