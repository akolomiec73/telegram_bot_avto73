<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFilters extends Model
{
    protected $table = 'user_filters';

    protected $fillable = ['id_bot_user', 'filter_status', 'filter_price_min', 'filter_price_max', 'filter_category_car', 'filter_category_detail'];
}
