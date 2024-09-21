<?php

namespace App\Schedule;

use Carbon\Carbon;
use DateTime;

use App\Helpers\LineMessage;
use App\Helpers\Generator;

use App\Models\ErrorModel;
use App\Models\AdminModel;

use App\Mail\ScheduleEmail;
use Illuminate\Support\Facades\Mail;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;

use App\Service\FirebaseRealtime;
use App\Service\FirebaseStorage;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Factory;

use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Canvas\Factory as CanvasFactory;
use Dompdf\Options as DompdfOptions;
use Dompdf\Adapter\CPDF;

class AuditSchedule
{
    public static function audit_error()
    {
        $summary = ErrorModel::selectRaw('message,created_at,faced_by,COUNT(1) as total')
            ->where('is_fixed','0')
            ->orderby('total','desc')
            ->orderby('message','asc')
            ->orderby('created_at','asc')
            ->groupby('message')
            ->get();
        
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
            $header_template = Generator::generateDocTemplate('header');
            $style_template = Generator::generateDocTemplate('style');
            $footer_template = Generator::generateDocTemplate('footer');
    
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
}
