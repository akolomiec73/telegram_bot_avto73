<?php

/*
* Методы по работе с кешем (Redis)
*/
declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Services\LoggerService;
use Illuminate\Support\Facades\Redis;

class CacheRepository implements CacheRepositoryInterface
{
    protected LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function getUserInfo(int $chatId): ?array
    {
        try {
            $result = Redis::hgetall("user:$chatId");
            $this->logger->debug('Redis getUserInfo', ['chat_id' => $chatId, 'result' => $result]);

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('ERROR Redis getUserInfo', ['error_text' => $e->getMessage(), 'chat_id' => $chatId]);

            return null;
        }

    }

    public function setUserData(int $chatId, ?string $username, string $stage): void
    {
        try {
            Redis::hset("user:$chatId", 'stage', $stage);
            if ($username !== null) {
                Redis::hset("user:$chatId", 'username', $username);
            }
            Redis::expire("user:$chatId", 3600);
            $this->logger->debug('Redis setUserData', ['chat_id' => $chatId, 'stage' => $stage, 'username' => $username]);
        } catch (\Exception $e) {
            $this->logger->error('ERROR Redis setUserData', ['error_text' => $e->getMessage(), 'chat_id' => $chatId, 'stage' => $stage, 'username' => $username]);
        }
    }
}
