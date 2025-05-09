<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Factory;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Canvas\Factory as CanvasFactory;
use Dompdf\Options as DompdfOptions;
use Dompdf\Adapter\CPDF;

use App\Helpers\LineMessage;
use App\Helpers\Generator;

use App\Models\ErrorModel;
use App\Models\AdminModel;
use App\Models\InventoryModel;

use App\Service\FirebaseRealtime;
use App\Service\FirebaseStorage;

class AuditSchedule
{
    public static function audit_error()
    {
        $summary = ErrorModel::getAllErrorAudit();
        
        if($summary){
            $firebaseRealtime = new FirebaseRealtime();
            $audit = "";
            $total = count($summary);

            foreach($summary as $dt){
                $audit .= "
                    <tr>
                        <td>$dt->message</td>
                        <td style='text-align:center;'>$dt->created_at</td>
                        <td style='text-align:center;'>";
                        if($dt->faced_by){
                            $audit .= $dt->faced_by;
                        } else {
                            $audit .= "-";
                        }
                        $audit.= "</td>
                        <td style='text-align:center;'>$dt->total</td>
                    </tr>
                ";
            }
            
            $admin = AdminModel::getAllContact();
            $datetime = date("Y-m-d H:i:s");    
            $options = new DompdfOptions();
            $options->set('defaultFont', 'Helvetica');
            $dompdf = new Dompdf($options);
            $header_template = Generator::getDocTemplate('header');
            $style_template = Generator::getDocTemplate('style');
            $footer_template = Generator::getDocTemplate('footer');
    
            $html = "
            <html>
                <head>
                    $style_template
                </head>
                <body>
                    $header_template
                    <h2>Audit - Error</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Error Message</th>
                                <th>Datetime</th>
                                <th>Faced By</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>$audit</tbody>
                    </table>
                    $footer_template
                </body>
            </html>";
    
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
    
            $pdfContent = $dompdf->output();
            $pdfFilePath = public_path("audit_error_$datetime.pdf");
            file_put_contents($pdfFilePath, $pdfContent);
            $inputFile = InputFile::create($pdfFilePath, $pdfFilePath);

            foreach($admin as $dt){
                $message = "[ADMIN] Hello $dt->username, the system just run an audit error, with result of $total error found. Here's the document";
                
                if($dt->telegram_user_id){
                    $response = Telegram::sendDocument([
                        'chat_id' => $dt->telegram_user_id,
                        'document' => $inputFile,
                        'caption' => $message,
                        'parse_mode' => 'HTML'
                    ]);
                }
                if($dt->line_user_id){
                    LineMessage::sendMessage('text',"Error has been audited",$dt->line_user_id);
                }
                if($dt->firebase_fcm_token){
                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                        ->withNotification(Notification::create("Error has been audited", $dt->id))
                        ->withData([
                            'id_context' => $dt->id,
                        ]);
                    $response = $messaging->send($message);
                }

                // Audit to firebase realtime
                $record = [
                    'context' => 'audit',
                    'context_id' => $dt->id,
                    'clean_type' => 'error',
                    'telegram_message' => $dt->telegram_user_id,
                    'line_message' => $dt->line_user_id,
                    'firebase_fcm_message' => $dt->firebase_fcm_token,
                ];
                $firebaseRealtime->insert_command('task_scheduling/audit/' . uniqid(), $record);
            }

            $firebaseService = new FirebaseStorage();
            $firebaseUrl = $firebaseService->uploadFile($pdfFilePath, "audit/error", "audit_error_$datetime.pdf");
    
            unlink($pdfFilePath);
        }
    }

    public static function audit_dashboard(){
        $dashboard = InventoryModel::getAllDashboard();

        if($dashboard){
            foreach($dashboard as $index => $dt){
                $message_template = "Hello $dt->username, here's the weekly dashboard we've gathered so far from your inventory :";
                $message = "$message_template\n\n- Total Item : $dt->total_inventory\n- Favorite Item : $dt->total_favorite\n- Low Capacity : $dt->total_low_capacity\n- Last Added : $dt->last_created_inventory_name\n- Most Category : $dt->most_category ($dt->most_category_count)\n- The Highest Price : Rp. ".number_format($dt->max_price)." ($dt->max_price_inventory_name)";

                if($dt->telegram_user_id && $dt->telegram_is_valid == 1){
                    $response = Telegram::sendMessage([
                        'chat_id' => $dt->telegram_user_id,
                        'text' => $message,
                        'parse_mode' => 'HTML'
                    ]);
                }
                if($dt->line_user_id){
                    LineMessage::sendMessage('text',$message,$dt->line_user_id);
                }
            }
        }
    }

    public static function audit_apps(){
        $days = 7;
        $summary = AdminModel::getAppsSummaryForLastNDays($days);

        if($summary){
            $admin = AdminModel::getAllContact();

            foreach($admin as $dt){
                $message_template = "[ADMIN] Hello $dt->username, here's the apps summary for the last $days days:";
                $message = "$message_template\n\n- Inventory Created: $summary->inventory_created\n- New User : $summary->new_user\n- Report Created : $summary->report_created\n- Error Happen : $summary->error_happen";

                if($dt->telegram_user_id && $dt->telegram_is_valid == 1){
                    $response = Telegram::sendMessage([
                        'chat_id' => $dt->telegram_user_id,
                        'text' => $message,
                        'parse_mode' => 'HTML'
                    ]);
                }
                if($dt->line_user_id){
                    LineMessage::sendMessage('text',$message,$dt->line_user_id);
                }
            }
        }
    }
}
