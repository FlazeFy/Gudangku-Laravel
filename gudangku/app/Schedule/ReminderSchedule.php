<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;

use App\Helpers\LineMessage;
use App\Helpers\Generator;

use App\Models\ReminderModel;
use App\Models\ScheduleMarkModel;

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
            $server_datetime = new DateTime();

            foreach($summary as $index => $dt){
                // User time config
                $user_timezone = $dt->timezone;
                $status_time_tz = $user_timezone[0];
                $split = explode(':',str_replace('+','',str_replace('-','',$user_timezone)));
                $hour_tz = $split[0];
                $minute_tz = $split[1];
                $interval = $status_time_tz.$hour_tz." hours $minute_tz minutes";

                // Server based user
                $server_datetime->modify($interval);

                $exec = false;

                $idempoten_pass = true;
                $idempoten = ScheduleMarkModel::select('id','last_execute','reminder_id')
                    ->where('reminder_id',$dt->id)
                    ->first();

                if($idempoten){
                    $idempoten_datetime = new DateTime($idempoten->last_execute);
                    $idempoten_datetime->modify($interval);
                    $idempoten_pass = false;
                }

                if($dt->reminder_type == 'Every Day'){
                    $server_day = $server_datetime->format('H');
                    $split_reminder_context = explode(" ", $dt->reminder_context);
                    $day_reminder = $split_reminder_context[1];
                    if($day_reminder == $server_day){
                        $exec = true;

                        // Check idempoten
                        if($idempoten){
                            $idempoten_day = $idempoten_datetime->format('H');
                            if($idempoten_day != $day_reminder){
                                $idempoten_pass = true;
                                ScheduleMarkModel::destroy($idempoten->id);
                            } else {
                                $idempoten_pass = false;
                            }
                        } else {
                            $idempoten_pass = true;
                        }
                    }
                } else if($dt->reminder_type == 'Every Week'){
                    $server_day = $server_datetime->format('D');
                    $split_reminder_context = explode(" ", $dt->reminder_context);
                    $day_reminder = substr($split_reminder_context[1],0,3);
                    if($day_reminder == $server_day){
                        $exec = true;

                        // Check idempoten
                        if($idempoten){
                            $idempoten_day = $idempoten_datetime->format('D');
                            if($idempoten_day != $day_reminder){
                                $idempoten_pass = true;
                                ScheduleMarkModel::destroy($idempoten->id);
                            } else {
                                $idempoten_pass = false;
                            }
                        } else {
                            $idempoten_pass = true;
                        }
                    }
                } else if($dt->reminder_type == 'Every Month' || $dt->reminder_type == 'Every Year'){
                    if($dt->reminder_type == 'Every Month'){
                        $server_day = $server_datetime->format('d');
                        $split_reminder_context = explode(" ", $dt->reminder_context);
                        $day_reminder = $split_reminder_context[1];
                    } else {
                        $server_day = $server_datetime->format('d F');
                        $split_reminder_context = explode(" ", $dt->reminder_context);
                        $day_reminder = $split_reminder_context[1]." ".$split_reminder_context[2];
                    }
                    
                    if($day_reminder == $server_day){
                        $exec = true;

                        if($idempoten){
                            if($dt->reminder_type == 'Every Month'){
                                // Check idempoten
                                $idempoten_day = $idempoten_datetime->format('d');
                                if($idempoten_day != $day_reminder){
                                    $idempoten_pass = true;
                                    ScheduleMarkModel::destroy($idempoten->id);
                                } else {
                                    $idempoten_pass = false;
                                }
                            } else {
                                // Check idempoten
                                $idempoten_day = $idempoten_datetime->format('d F');
                                if($idempoten_day != $day_reminder){
                                    $idempoten_pass = true;
                                    ScheduleMarkModel::destroy($idempoten->id);
                                } else {
                                    $idempoten_pass = false;
                                }
                            }
                        } else {
                            $idempoten_pass = true;
                        }
                    }
                }

                if($exec){
                    if($idempoten_pass){
                        ScheduleMarkModel::create([
                            'id' => Generator::getUUID(), 
                            'reminder_id' => $dt->id,
                            'last_execute' => date('Y-m-d H:i:s'), 
                        ]);

                        $message = (int)$server_day." Hello $dt->username, your inventory $dt->inventory_name has remind $dt->reminder_desc";

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
                    } 
                }
            }
        }
    }
}
