<?php

declare(strict_types=1);
/*
 * Сервис координации репозиториев
 */

namespace App\Services;

use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Repositories\Contracts\DatabaseRepositoryInterface;

class RepositoryService
{
    private DatabaseRepositoryInterface $dbRepo;

    private CacheRepositoryInterface $cacheRepo;

    public function __construct(
        DatabaseRepositoryInterface $dbRepo,
        CacheRepositoryInterface $cacheRepo
    ) {
        $this->dbRepo = $dbRepo;
        $this->cacheRepo = $cacheRepo;
    }

    public function getUser(int $chatId): ?array
    {
        $user = $this->cacheRepo->getUserInfo($chatId);
        if (! $user) {
            $user = $this->dbRepo->getUserInfo($chatId);
            $this->cacheRepo->setUserData($chatId, $user->username, $user->stage);
        }

        return $user;
    }

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
                $this->cacheRepo->setUserData($chatId, $username, $stage);
                $this->dbRepo->updateUserData($chatId, $updateData);
            }
        }
    }

    public function updateTempAdv(int $chatId, array $data): void
    {
        $this->dbRepo->updateTempAdvData($chatId, $data);
    }

    public function getUserDatePost(int $chatId): object
    {
        return $this->dbRepo->getUserDatePost($chatId);
    }

    public function updateUserDatePost(int $chatId, string $date): void
    {
        $this->dbRepo->updateUserDatePost($chatId, $date);
    }

    public function getAdvRow(int $chatId): ?object
    {
        return $this->dbRepo->getAdvRow($chatId);
    }
}
