<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;

use App\Helpers\LineMessage;

use App\Models\HistoryModel;
use App\Models\InventoryModel;
use App\Models\AdminModel;
use App\Models\ReportModel;
use App\Models\ReportItemModel;

use App\Mail\ScheduleEmail;
use Illuminate\Support\Facades\Mail;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Service\FirebaseRealtime;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Factory;

class CleanSchedule
{
    public static function clean_history()
    {
        $days = 30;
        $summary = HistoryModel::whereDate('created_at', '<', Carbon::now()->subDays($days))->delete();
        
        if($summary){
            $firebaseRealtime = new FirebaseRealtime();
            $message = "[ADMIN] Hello $dt->username, the system just run a clean history, with result of $summary history executed";
            $admin = AdminModel::getAllContact();

            foreach($admin as $dt){
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
                    'context' => 'history',
                    'context_id' => $dt->id,
                    'clean_type' => 'destroy',
                    'telegram_message' => $dt->telegram_user_id,
                    'line_message' => $dt->line_user_id,
                    'firebase_fcm_message' => $dt->firebase_fcm_token,
                ];
                $firebaseRealtime->insert_command('task_scheduling/clean/' . uniqid(), $record);
            }
        }
    }

    public static function clean_deleted_inventory()
    {
        $days = 30;
        $summary = InventoryModel::getInventoryPlanDestroy($days);
        
        if($summary){
            $firebaseRealtime = new FirebaseRealtime();
            $admin = AdminModel::getAllContact();
            $summary_exec = "";
            $username_before = "";
            $items = "";
            $message = "";
            $total = count($summary); 

            foreach($summary as $index => $in) {
                $items .= $in->inventory_name;
                if($index < $total - 1) {
                    if($summary[$index + 1]->username == $in->username) {
                        $items .= ", ";
                    } 
                }
            
                if($index == $total - 1 || $summary[$index + 1]->username != $in->username) {
                    $message = "Hello $in->username, your inventory $items is permanently deleted";
            
                    // Report to user & execute destroy
                    if($in->telegram_user_id){
                        $response = Telegram::sendMessage([
                            'chat_id' => $in->telegram_user_id,
                            'text' => $message,
                            'parse_mode' => 'HTML'
                        ]);
                    }
                    if($in->line_user_id){
                        LineMessage::sendMessage('text',$message,$in->line_user_id);
                    }
                    if($in->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
                        $messaging = $factory->createMessaging();
                        $message = CloudMessage::withTarget('token', $in->firebase_fcm_token)
                            ->withNotification(Notification::create($message, $in->username));
                        $response = $messaging->send($message);
                    }
            
                    // Destroy inventory items
                    // InventoryModel::destroy($in->id);
            
                    $summary_exec .= "- $items owned by #$in->username\n";
                    $items = "";
                }
            }

            // Report to admin
            foreach($admin as $dt){
                $message_admin = "[ADMIN] Hello $dt->username, the system just run a clean inventory, here's the detail:\n\n$summary_exec";

                if($dt->telegram_user_id){
                    $response = Telegram::sendMessage([
                        'chat_id' => $dt->telegram_user_id,
                        'text' => $message_admin,
                        'parse_mode' => 'HTML'
                    ]);
                }
                if($dt->line_user_id){
                    LineMessage::sendMessage('text',$message_admin,$dt->line_user_id);
                }
                if($dt->firebase_fcm_token){
                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                        ->withNotification(Notification::create($message_admin, $dt->id));
                    $response = $messaging->send($message);
                }

                // Audit to firebase realtime
                $record = [
                    'context' => 'inventory_report_admin',
                    'context_id' => $dt->id,
                    'clean_type' => 'destroy',
                    'telegram_message' => $dt->telegram_user_id,
                    'line_message' => $dt->line_user_id,
                    'firebase_fcm_message' => $dt->firebase_fcm_token,
                ];
                $firebaseRealtime->insert_command('task_scheduling/clean/' . uniqid(), $record);
            }
        }
    }

    public static function clean_deleted_report()
    {
        $days = 30;
        $summary = ReportModel::getReportPlanDestroy($days);
        
        if($summary){
            $firebaseRealtime = new FirebaseRealtime();
            $admin = AdminModel::getAllContact();
            $summary_exec = "";
            $username_before = "";
            $items = "";
            $message = "";
            $total = count($summary); 

            foreach($summary as $index => $rp) {
                $items .= $rp->report_title;
                if($index < $total - 1) {
                    if($summary[$index + 1]->username == $rp->username) {
                        $items .= ", ";
                    } 
                }
            
                if($index == $total - 1 || $summary[$index + 1]->username != $rp->username) {
                    $message = "Hello $rp->username, your report $items is permanently deleted";
            
                    // Report to user & execute destroy
                    if($rp->telegram_user_id){
                        $response = Telegram::sendMessage([
                            'chat_id' => $rp->telegram_user_id,
                            'text' => $message,
                            'parse_mode' => 'HTML'
                        ]);
                    }
                    if($rp->line_user_id){
                        LineMessage::sendMessage('text',$message,$rp->line_user_id);
                    }
                    if($rp->firebase_fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
                        $messaging = $factory->createMessaging();
                        $message = CloudMessage::withTarget('token', $rp->firebase_fcm_token)
                            ->withNotification(Notification::create($message, $rp->username));
                        $response = $messaging->send($message);
                    }
            
                    // Destroy Report items
                    ReportModel::destroy($rp->id);
                    ReportItemModel::where('report_id',$rp->id)->delete();
            
                    $summary_exec .= "- $items owned by #$rp->username\n";
                    $items = "";
                }
            }

            // Report to admin
            foreach($admin as $dt){
                $message_admin = "[ADMIN] Hello $dt->username, the system just run a clean inventory, here's the detail:\n\n$summary_exec";

                if($dt->telegram_user_id){
                    $response = Telegram::sendMessage([
                        'chat_id' => $dt->telegram_user_id,
                        'text' => $message_admin,
                        'parse_mode' => 'HTML'
                    ]);
                }
                if($dt->line_user_id){
                    LineMessage::sendMessage('text',$message_admin,$dt->line_user_id);
                }
                if($dt->firebase_fcm_token){
                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                        ->withNotification(Notification::create($message_admin, $dt->id));
                    $response = $messaging->send($message);
                }

                // Audit to firebase realtime
                $record = [
                    'context' => 'inventory_report_admin',
                    'context_id' => $dt->id,
                    'clean_type' => 'destroy',
                    'telegram_message' => $dt->telegram_user_id,
                    'line_message' => $dt->line_user_id,
                    'firebase_fcm_message' => $dt->firebase_fcm_token,
                ];
                $firebaseRealtime->insert_command('task_scheduling/clean/' . uniqid(), $record);
            }
        }
    }
}
