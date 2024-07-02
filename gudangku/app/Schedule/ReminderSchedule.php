<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;

use App\Helpers\LineMessage;

use App\Models\ReminderModel;

use App\Mail\ScheduleEmail;
use Illuminate\Support\Facades\Mail;
use Telegram\Bot\Laravel\Facades\Telegram;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Service\FirebaseRealtime;

class ReminderSchedule
{
    public static function remind_inventory()
    {
        $summary = ReminderModel::getReminderJob();
        
        if($summary){
            $firebaseRealtime = new FirebaseRealtime();

            foreach($summary as $index => $dt){
                $message = "Hello $dt->username, your inventory $dt->inventory_name has remind $dt->reminder_desc";

                if($dt->telegram_user_id){
                    $response = Telegram::sendMessage([
                        'chat_id' => $dt->telegram_user_id,
                        'text' => $message,
                        'parse_mode' => 'HTML'
                    ]);
                }
                if($dt->line_user_id){
                    LineMessage::sendMessage('text',$message,$dt->line_user_id);
                }
                if($dt->firebase_fcm_token){
                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                        ->withNotification(Notification::create($message, $dt->id))
                        ->withData([
                            'id_context' => $dt->id,
                        ]);
                    $response = $messaging->send($message);
                }

                // Audit to firebase realtime
                $record = [
                    'context' => 'inventory',
                    'context_id' => $dt->id,
                    'inventory_name' => $dt->inventory_name,
                    'reminder_type' => $dt->reminder_type,
                    'reminder_context' => $dt->reminder_context,
                    'telegram_message' => $dt->telegram_user_id,
                    'line_message' => $dt->line_user_id,
                    'firebase_fcm_message' => $dt->firebase_fcm_token,
                ];
                $firebaseRealtime->insert_command('task_scheduling/reminder/' . uniqid(), $record);

                $total_calorie = 0;
                $total_payment = 0;
            }
        }
    }
}
