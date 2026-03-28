<?php

/*
* Методы по работе с кешем (Redis)
*/
declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\CacheRepositoryInterface;
use Illuminate\Support\Facades\Redis;

class CacheRepository implements CacheRepositoryInterface
{
    public function getUserInfo(int $chatId): ?array
    {
        return Redis::hgetall("user:$chatId");
    }

    public function setUserData(int $chatId, ?string $username, string $stage): void
    {
        Redis::hset("user:$chatId", 'stage', $stage);
        if ($username !== null) {
            Redis::hset("user:$chatId", 'username', $username);
        }
        Redis::expire("user:$chatId", 3600);
    }
}
