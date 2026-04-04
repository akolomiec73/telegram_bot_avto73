<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Services\LoggerService;
use Illuminate\Support\Facades\Redis;

/**
 * Методы по работе с кэшем (Redis)
 */
readonly class CacheRepository implements CacheRepositoryInterface
{
    private const USER_KEY_PREFIX = 'user:';

    public function __construct(
        private LoggerService $logger
    ) {}

    /**
     * Получение информации о пользователе
     */
    public function getUserInfo(int $chatId): ?array
    {
        try {
            $result = Redis::hgetall(self::USER_KEY_PREFIX.$chatId);
            $this->logger->debug('Redis getUserInfo', ['chat_id' => $chatId, 'result' => $result]);

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('ERROR Redis getUserInfo', ['error_text' => $e->getMessage(), 'chat_id' => $chatId]);

            return null;
        }
    }

    /**
     * Запись информации о пользователе в кэш
     */
    public function setUserData(int $chatId, ?string $username, string $stage): void
    {
        try {
            Redis::hset(self::USER_KEY_PREFIX.$chatId, 'stage', $stage);
            if ($username !== null) {
                Redis::hset(self::USER_KEY_PREFIX.$chatId, 'username', $username);
            }
            Redis::expire(self::USER_KEY_PREFIX.$chatId, 3600);
            $this->logger->debug('Redis setUserData', ['chat_id' => $chatId, 'stage' => $stage, 'username' => $username]);
        } catch (\Exception $e) {
            $this->logger->error('ERROR Redis setUserData', ['error_text' => $e->getMessage(), 'chat_id' => $chatId, 'stage' => $stage, 'username' => $username]);
        }
    }
}
