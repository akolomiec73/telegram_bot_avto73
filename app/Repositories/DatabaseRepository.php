<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\BotUsers;
use App\Models\TempAdvUser;
use App\Models\UserFilters;
use App\Repositories\Contracts\DatabaseRepositoryInterface;
use App\Services\LoggerService;

/**
 * Методы по работе с БД
 */
readonly class DatabaseRepository implements DatabaseRepositoryInterface
{
    public function __construct(
        private LoggerService $logger
    ) {}

    /**
     * Получение информации о пользователе
     */
    public function getUserInfo(int $chatId): ?BotUsers
    {
        try {
            return BotUsers::select(['id', 'username', 'stage'])->where('chat_id', $chatId)->first();
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB getUserInfo', ['error_text' => $e->getMessage(), 'chat_id' => $chatId]);

            return null;
        }
    }

    /**
     * Создание строки о пользователе
     */
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

    /**
     * Обновление строки о пользователе
     */
    public function updateUserData(int $chatId, array $data): void
    {
        try {
            BotUsers::where('chat_id', $chatId)->update($data);
            $this->logger->debug('DB updateUserData', ['chat_id' => $chatId, 'data' => $data]);
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB updateUserData', ['error_text' => $e->getMessage(), 'chat_id' => $chatId, 'data' => $data]);
        }
    }

    /**
     * Обновление таблицы с создаваемым объявлением
     */
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

    /**
     * Получение даты последней публикации пользователя
     */
    public function getUserDatePost(int $chatId): ?BotUsers
    {
        try {
            return BotUsers::select(['date_send_add', 'username'])->where('chat_id', $chatId)->first();
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB getUserDatePost', ['error_text' => $e->getMessage(), 'chat_id' => $chatId]);

            return null;
        }
    }

    /**
     * Обновление даты последней публикации пользователя
     */
    public function updateUserDatePost(int $chatId, string $date): void
    {
        try {
            BotUsers::where('chat_id', $chatId)->update(['date_send_add' => $date]);
            $this->logger->debug('DB updateUserDatePost', ['chat_id' => $chatId, 'date' => $date]);
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB updateUserDatePost', ['error_text' => $e->getMessage(), 'chat_id' => $chatId, 'date' => $date]);
        }
    }

    /**
     * Получение полной информации о создаваемом объявлении
     */
    public function getAdvRow(int $chatId): ?TempAdvUser
    {
        try {
            $user = BotUsers::with('tempAdv')->where('chat_id', $chatId)->first();
            if (! $user || ! $user->tempAdv) {
                return null;
            }
            $this->logger->debug('DB getAdvRow', ['chat_id' => $chatId, 'id_row' => $user->id]);

            return $user->tempAdv;
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB getAdvRow', ['error_text' => $e->getMessage(), 'chat_id' => $chatId]);

            return null;
        }
    }

    /**
     * Получение значений фильтров пользователя
     */
    public function getFilterList(int $chatId): ?UserFilters
    {
        try {
            $userFilters = BotUsers::with('userFilters')->where('chat_id', $chatId)->first()->userFilters;
            if (! $userFilters) {
                $userFilters = $this->setUserFilters($chatId);
            }
            $this->logger->debug('getFilterList', ['result' => $userFilters, 'chat_id' => $chatId]);

            return $userFilters;
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB getAdvRow', ['error_text' => $e->getMessage(), 'chat_id' => $chatId]);

            return null;
        }
    }

    /**
     * Создание строки значений фильтров пользователя
     */
    private function setUserFilters(int $chatId): ?UserFilters
    {
        try {
            $user = $this->getUserInfo($chatId);
            if ($user == null) {
                return null;
            }
            $user->userFilters()->create([
                'id_bot_user' => $user->id,
            ]);

            return BotUsers::with('userFilters')->where('chat_id', $chatId)->first()->userFilters;
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB setUserFilters', ['error_text' => $e->getMessage(), 'chat_id' => $chatId]);

            return null;
        }
    }

    /**
     * Обновление значений фильтров пользователя
     */
    public function updateFilterCategory(int $chatId, string $stage): void
    {
        try {
            $user = $this->getUserInfo($chatId);
            $currentValue = UserFilters::where('id_bot_user', $user->id)->value($stage);
            if ($currentValue === 0) {
                $newValue = 1;
            } else {
                $newValue = 0;
            }
            UserFilters::where('id_bot_user', $user->id)->update([$stage => $newValue]);
            $this->logger->debug('DB updateFilterCategory', ['chat_id' => $chatId, 'column' => $stage, 'value' => $newValue]);
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB updateFilterCategory', ['error_text' => $e->getMessage(), 'chat_id' => $chatId, 'column' => $stage, 'value' => $newValue]);
        }
    }

    /**
     * Обновление значений фильтров Цены пользователя
     */
    public function updateFilterPrice(int $chatId, string $column, ?string $value): void
    {
        try {
            $user = $this->getUserInfo($chatId);
            UserFilters::where('id_bot_user', $user->id)->update([$column => $value]);
            $this->logger->debug('DB updateFilterPrice', ['chat_id' => $chatId, 'column' => $column, 'value' => $value]);
        } catch (\Exception $e) {
            $this->logger->error('ERROR DB updateFilterPrice', ['error_text' => $e->getMessage(), 'chat_id' => $chatId, 'column' => $column, 'value' => $value]);
        }
    }
}
