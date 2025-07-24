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
use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\PiePlot;
use Amenadiel\JpGraph\Plot\PiePlot3D;
use Amenadiel\JpGraph\Plot\BarPlot;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

// Helpers
use App\Helpers\LineMessage;
use App\Helpers\Generator;
// Models
use App\Models\ErrorModel;
use App\Models\AdminModel;
use App\Models\UserModel;
use App\Models\InventoryModel;
// Service
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

    public static function audit_weekly_stats() {
        $listCols = ["inventory_category","inventory_room","inventory_merk"];
        $users = UserModel::getUserBroadcastAll();
    
        foreach ($users as $us) {
            $chartFiles = []; 
    
            foreach ($listCols as $col) {
                $type = ["price","item"];

                foreach ($type as $tp) {
                    // Model
                    $res = InventoryModel::getContextTotalStats($col, $tp, $us->id);
        
                    if ($res == null || $res->isEmpty()) continue;
        
                    // Dataset
                    $labels = $res->pluck('context')->map(fn($c) => Str::upper(str_replace('_', ' ', $c)))->all();
                    $values = $res->pluck('total')->all();
        
                    // Filename
                    $chartFilename = "bar_chart_$tp-$col-$us->id.png";
                    $chartPath = storage_path("app/public/$chartFilename");

                    // Generate chart
                    $graph = new Graph(800, 500);
                    $graph->SetScale("textlin");
                    $graph->xaxis->SetTickLabels($labels);
                    $graph->xaxis->SetLabelAngle(35);
                    $graph->xaxis->SetFont(FF_ARIAL, FS_NORMAL, 7);
                    $graph->yaxis->SetFont(FF_ARIAL, FS_NORMAL, 7);
                    $graph->title->SetFont(FF_ARIAL, FS_BOLD, 10);
                    $barPlot = new BarPlot($values);
                    $barPlot->SetFillColor("navy");
                    $graph->Add($barPlot);
                    $graph->title->Set("Total ".Str::headline($tp)." Inventory By ".Str::headline($col));
                    $graph->Stroke($chartPath);

                    $chartFiles[] = $chartFilename;
                }
            }
    
            if (empty($chartFiles)) continue;
    
            // Render PDF
            $generatedDate = now()->format('d F Y');
            $datetime = now()->format('d M Y h:i');
            $tmpPdfPath = storage_path("app/public/Weekly Inventory Audit - ".$us->username.".pdf");

            Pdf::loadView('components.pdf.inventory_chart', [
                'charts' => $chartFiles,
                'date' => $generatedDate,
                'datetime' => $datetime,
                'username' => $us->username
            ])->save($tmpPdfPath);

            // Send Telegram
            if ($us->telegram_user_id) {
                $message = "[ADMIN] Hello {$us->username}, here is your weekly inventory audit report.";

                Telegram::sendDocument([
                    'chat_id' => $us->telegram_user_id,
                    'document' => fopen($tmpPdfPath, 'rb'),
                    'caption' => $message,
                    'parse_mode' => 'HTML'
                ]);
            }

            // Clean up File
            foreach ($chartFiles as $file) {
                $chartPath = storage_path("app/public/$file");
                if (file_exists($chartPath)) {
                    unlink($chartPath);
                }
            }

            if (file_exists($tmpPdfPath)) {
                unlink($tmpPdfPath);
            }
        }
    }

    public static function audit_yearly_stats() {
        $users = UserModel::getUserBroadcastAll();
        $year = 2025;
    
        foreach ($users as $us) {
            $chartFiles = []; 

            // Model
            $res_inventory_monthly = InventoryModel::getTotalInventoryCreatedPerMonth($us->id, $year, false);

            if ($res_inventory_monthly == null || $res_inventory_monthly->isEmpty()) continue;
            $res_final_inventory_monthly = [];
            for ($i=1; $i <= 12; $i++) { 
                $total = 0;
                foreach ($res_inventory_monthly as $idx => $val) {
                    if($i == $val->context){
                        $total = $val->total;
                        break;
                    }
                }
                array_push($res_final_inventory_monthly, [
                    'context' => Generator::generateMonthName($i,'short'),
                    'total' => $total,
                ]);
            }

            // Dataset
            $labels_inventory_monthly = collect($res_final_inventory_monthly)->pluck('context')->map(fn($c) => Str::upper(str_replace('_', ' ', $c)))->all();
            $values_inventory_monthly = collect($res_final_inventory_monthly)->pluck('total')->all();

            // Filename
            $chartFilename = "bar_chart_inventory_monthly_$year-$us->id.png";
            $chartPath = storage_path("app/public/$chartFilename");

            // Generate chart
            $graph = new Graph(800, 500);
            $graph->SetScale("textlin");
            $graph->xaxis->SetTickLabels($labels_inventory_monthly);
            $graph->xaxis->SetLabelAngle(35);
            $graph->xaxis->SetFont(FF_ARIAL, FS_NORMAL, 7);
            $graph->yaxis->SetFont(FF_ARIAL, FS_NORMAL, 7);
            $graph->title->SetFont(FF_ARIAL, FS_BOLD, 10);
            $barPlot = new BarPlot($values_inventory_monthly);
            $barPlot->SetFillColor("navy");
            $graph->Add($barPlot);
            $graph->title->Set("Total Inventory Created Per Month ($year)");
            $graph->Stroke($chartPath);

            $chartFiles[] = $chartFilename;
    
            if (empty($chartFiles)) continue;
    
            // Render PDF
            $generatedDate = now()->format('d F Y');
            $datetime = now()->format('d M Y h:i');
            $tmpPdfPath = storage_path("app/public/Yearly Inventory Audit - ".$us->username.".pdf");

            Pdf::loadView('components.pdf.inventory_chart', [
                'charts' => $chartFiles,
                'date' => $generatedDate,
                'datetime' => $datetime,
                'username' => $us->username
            ])->save($tmpPdfPath);

            // Send Telegram
            if ($us->telegram_user_id) {
                $message = "[ADMIN] Hello {$us->username}, here is your yearly inventory audit report.";

                Telegram::sendDocument([
                    'chat_id' => $us->telegram_user_id,
                    'document' => fopen($tmpPdfPath, 'rb'),
                    'caption' => $message,
                    'parse_mode' => 'HTML'
                ]);
            }

            // Clean up File
            foreach ($chartFiles as $file) {
                $chartPath = storage_path("app/public/$file");
                if (file_exists($chartPath)) {
                    unlink($chartPath);
                }
            }

            if (file_exists($tmpPdfPath)) {
                unlink($tmpPdfPath);
            }
        }
    }
}
