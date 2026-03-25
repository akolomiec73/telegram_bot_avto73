<?php

/*
 * Модель таблицы пользователей
 */
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotUsers extends Model
{
    protected $table = 'bot_users';

    protected $fillable = ['chat_id', 'username', 'stage'];

    public function tempAdv()
    {
        return $this->hasOne(TempAdvUser::class, 'id_bot_user', 'id');
    }
}
