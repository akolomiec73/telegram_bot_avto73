<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Api;

class TelegramBotController extends Controller
{
    protected Api $telegram;

    public function __construct()
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

    }

    public function webhook(Request $request)
    {
        // Получаем обновление от Telegram
        $update = $this->telegram->getWebhookUpdates();

        // Извлекаем данные
        $message = $update->getMessage();
        if (!$message) {
            return;
        }

        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        // Обрабатываем команды
        switch ($text) {
            case '/start':
                $response = 'Привет! Я Telegram‑бот на Laravel.';
                break;
            default:
                $response = 'Вы сказали: '.$text;
                break;
        }

        // Отправляем ответ
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $response,
        ]);

        return response('OK', 200);
    }

    public function setWebhook()
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $url = env('APP_URL') . '/api/webhook';

        $response = file_get_contents(
            "https://api.telegram.org/bot{$token}/setWebhook?url={$url}"
        );

        return response()->json([
            'status' => 'Webhook установлен',
            'response' => json_decode($response)
        ]);
    }
}
