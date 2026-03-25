<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\BotUsers;
use App\Models\TempAdvUser;

interface UserRepositoryInterface
{
    public function findByChatId(int $chatId): ?BotUsers;

    public function updateUser(int $chatId, array $data): void;

    public function updateTempAdv(int $userId, array $data): void;

    public function getAdvRow(int $chatId): ?TempAdvUser;
}
