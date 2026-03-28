<?php

/*
* Методы по работе с БД
*/
declare(strict_types=1);

namespace App\Repositories;

use App\Models\BotUsers;
use App\Models\TempAdvUser;
use App\Repositories\Contracts\DatabaseRepositoryInterface;

class DatabaseRepository implements DatabaseRepositoryInterface
{
    public function getUserInfo(int $chatId): ?BotUsers
    {
        return BotUsers::select(['id', 'username', 'stage'])->where('chat_id', $chatId)->first();
    }

    public function setUserData(int $chatId, string $username, string $stage): void
    {
        BotUsers::create([
            'chat_id' => $chatId,
            'username' => $username,
            'stage' => $stage,
        ]);
    }

    public function updateUserData(int $chatId, array $data): void
    {
        BotUsers::where('chat_id', $chatId)->update($data);
    }

    public function updateTempAdvData(int $chatId, array $data): void
    {
        $user = $this->getUserInfo($chatId);
        $user->tempAdv()->updateOrCreate(['id_bot_user' => $user->id], $data);
    }

    public function getUserDatePost(int $chatId): BotUsers
    {
        return BotUsers::select(['date_send_add', 'username'])->where('chat_id', $chatId)->first();
    }

    public function updateUserDatePost(int $chatId, string $date): void
    {
        BotUsers::where('chat_id', $chatId)->update(['date_send_add' => $date]);
    }

    public function getAdvRow(int $chatId): ?TempAdvUser
    {
        $user = BotUsers::with('tempAdv')->where('chat_id', $chatId)->first();
        if (! $user || ! $user->tempAdv) {
            return null;
        }

        return $user->tempAdv;
    }
}
