<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface CacheRepositoryInterface
{
    public function getUserInfo(int $chatId): ?array;

    public function setUserData(int $chatId, ?string $username, string $stage): void;
}
