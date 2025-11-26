<?php
namespace App\Helpers;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;

class TelegramMessage
{
    public static function checkTelegramID($telegram_id){ 
        try {
            $chat = Telegram::getChat([
                'chat_id' => $telegram_id,
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}