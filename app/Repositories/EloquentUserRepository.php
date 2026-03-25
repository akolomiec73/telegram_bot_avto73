<?php

/*
* Методы по работе с моделями
*/
declare(strict_types=1);

namespace App\Repositories;

use App\Models\BotUsers;
use App\Models\TempAdvUser;
use App\Repositories\Contracts\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByChatId(int $chatId): ?BotUsers
    {
        return BotUsers::where('chat_id', $chatId)->first();
    }

    public function UpdateUser(int $chatId, array $data): void
    {
        BotUsers::updateOrCreate(
            ['chat_id' => $chatId],
            $data
        );
    }

    public function updateTempAdv(int $userId, array $data): void
    {
        $user = BotUsers::find($userId);
        $user->tempAdv()->updateOrCreate(
            ['id_bot_user' => $user->id],
            $data
        );
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
