<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\BotUsers;
use App\Models\TempAdvUser;
use App\Models\UserFilters;

interface DatabaseRepositoryInterface
{
    public function getUserInfo(int $chatId): ?BotUsers;

    public function setUserData(int $chatId, ?string $username, string $stage): void;

    public function updateUserData(int $chatId, array $data): void;

    public function updateTempAdvData(int $chatId, array $data): void;

    public function getUserDatePost(int $chatId): ?BotUsers;

    public function updateUserDatePost(int $chatId, string $date): void;

    public function getAdvRow(int $chatId): ?TempAdvUser;

    public function getFilterList(int $chatId): ?UserFilters;

    public function updateFilterCategory(int $chatId, string $stage): void;

    public function updateFilterPrice(int $chatId, string $column, ?string $value): void;
}
