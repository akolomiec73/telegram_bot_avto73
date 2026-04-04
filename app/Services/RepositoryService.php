<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Repositories\Contracts\DatabaseRepositoryInterface;

/**
 * Сервис координации репозиториев (БД, Redis)
 */
readonly class RepositoryService
{
    public function __construct(
        private DatabaseRepositoryInterface $dbRepo,
        private CacheRepositoryInterface $cacheRepo
    ) {}

    /**
     * Получение информации о пользователе(стадия, username)
     * Если нет в кэше - берём из бд и записываем в кэш
     */
    public function getUser(int $chatId): ?array
    {
        $user = $this->cacheRepo->getUserInfo($chatId);
        if (! $user) {
            $userObject = $this->dbRepo->getUserInfo($chatId);
            if ($userObject === null) {
                return null;
            }
            $this->cacheRepo->setUserData($chatId, $userObject->username, $userObject->stage);
            $user = [
                'username' => $userObject->username,
                'stage' => $userObject->stage,
            ];
        }

        return $user;
    }

    /**
     * Обновление информации о пользователе
     * Если нет в бд - создаём в бд и пищем в кеш
     * Если есть в бд - если значение полей отличается от значений в бд - меняем
     */
    public function updateUser(int $chatId, string $stage, ?string $username = null): void
    {
        $user = $this->dbRepo->getUserInfo($chatId);
        if (! $user) {
            $this->dbRepo->setUserData($chatId, $username, $stage);
            $this->cacheRepo->setUserData($chatId, $username, $stage);
        } else {
            $updateData = null;
            if ($user->stage !== $stage) {
                $updateData['stage'] = $stage;
            }
            if ($username !== null && $user->username !== $username) {
                $updateData['username'] = $username;
            }
            if ($updateData !== null) {
                $this->dbRepo->updateUserData($chatId, $updateData);
                $this->cacheRepo->setUserData($chatId, $username, $stage);
            }
        }
    }

    /**
     * Обновление информации о создаваемом объявлении пользователя
     */
    public function updateTempAdv(int $chatId, array $data): void
    {
        $this->dbRepo->updateTempAdvData($chatId, $data);
    }

    /**
     * Получение даты последней публикации пользователя
     */
    public function getUserDatePost(int $chatId): ?object
    {
        return $this->dbRepo->getUserDatePost($chatId);
    }

    /**
     * Обновление даты последней публикации пользователя
     */
    public function updateUserDatePost(int $chatId, string $date): void
    {
        $this->dbRepo->updateUserDatePost($chatId, $date);
    }

    /**
     * Получение информации о создаваемом объявлении пользователя
     */
    public function getAdvRow(int $chatId): ?object
    {
        return $this->dbRepo->getAdvRow($chatId);
    }

    /**
     * Получение значений фильтров пользователя
     */
    public function getFilterList(int $chatId): ?object
    {
        return $this->dbRepo->getFilterList($chatId);
    }

    /**
     * Обновление значений фильтров пользователя
     */
    public function updateFilter(int $chatId, string $stage): void
    {
        $this->dbRepo->updateFilterCategory($chatId, $stage);
    }

    /**
     * Обновление значений фильтров Цены пользователя
     */
    public function updateFilterPrice(int $chatId, string $column, ?string $value): void
    {
        $this->dbRepo->updateFilterPrice($chatId, $column, $value);
    }
}
