<?php

/*
* Методы по работе с БД
*/
declare(strict_types=1);

namespace App\Repositories;

use App\Models\BotUsers;
use App\Models\TempAdvUser;
use App\Repositories\Contracts\DatabaseRepositoryInterface;
use App\Services\LoggerService;

class DatabaseRepository implements DatabaseRepositoryInterface
{
    protected LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function getUserInfo(int $chatId): ?BotUsers
    {
        try {
            return BotUsers::select(['id', 'username', 'stage'])->where('chat_id', $chatId)->first();
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB getUserInfo', ['error_text' => $e->getMessage(), 'chat_id' => $chatId]);

            return null;
        }

    }

    public function setUserData(int $chatId, ?string $username, string $stage): void
    {
        try {
            $result = BotUsers::create([
                'chat_id' => $chatId,
                'username' => $username,
                'stage' => $stage,
            ]);
            $this->logger->debug('DB setUserData', ['chat_id' => $chatId, 'id_row' => $result->id]);
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB setUserData', ['error_text' => $e->getMessage(), 'chat_id' => $chatId, 'stage' => $stage, 'username' => $username]);
        }
    }

    public function updateUserData(int $chatId, array $data): void
    {
        try {
            BotUsers::where('chat_id', $chatId)->update($data);
            $this->logger->debug('DB updateUserData', ['chat_id' => $chatId, 'data' => $data]);
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB updateUserData', ['error_text' => $e->getMessage(), 'chat_id' => $chatId, 'data' => $data]);
        }
    }

    public function updateTempAdvData(int $chatId, array $data): void
    {
        try {
            $user = $this->getUserInfo($chatId);
            $user->tempAdv()->updateOrCreate(['id_bot_user' => $user->id], $data);
            $this->logger->debug('DB updateTempAdvData', ['chat_id' => $chatId, 'data' => $data]);
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB updateTempAdvData', ['error_text' => $e->getMessage(), 'chat_id' => $chatId, 'data' => $data]);
        }
    }

    public function getUserDatePost(int $chatId): ?BotUsers
    {
        try {
            return BotUsers::select(['date_send_add', 'username'])->where('chat_id', $chatId)->first();
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB getUserDatePost', ['error_text' => $e->getMessage(), 'chat_id' => $chatId]);

            return null;
        }
    }

    public function updateUserDatePost(int $chatId, string $date): void
    {
        try {
            BotUsers::where('chat_id', $chatId)->update(['date_send_add' => $date]);
            $this->logger->debug('DB updateUserDatePost', ['chat_id' => $chatId, 'date' => $date]);
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB updateUserDatePost', ['error_text' => $e->getMessage(), 'chat_id' => $chatId, 'date' => $date]);
        }
    }

    public function getAdvRow(int $chatId): ?TempAdvUser
    {
        try {
            $user = BotUsers::with('tempAdv')->where('chat_id', $chatId)->first();
            $this->logger->debug('DB getAdvRow', ['chat_id' => $chatId, 'id_row' => $user->id]);
            if (! $user || ! $user->tempAdv) {
                return null;
            }

            return $user->tempAdv;
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB getAdvRow', ['error_text' => $e->getMessage(), 'chat_id' => $chatId]);

            return null;
        }
    }
}
