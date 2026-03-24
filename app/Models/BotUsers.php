<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotUsers extends Model
{
    protected $table = 'bot_users'; // название таблицы отличается от названия класса

    // Поля для массового присваивания
    protected $fillable = ['chat_id', 'username', 'stage'];
}
