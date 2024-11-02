<?php

namespace App\Http\Controllers;

use App\Helpers\Generator;
use App\Helpers\Document;

use Illuminate\Http\Request;

use App\Models\ReportModel;
use App\Models\InventoryModel;
use App\Models\InventoryLayoutModel;
use App\Models\UserModel;
use App\Models\ReminderModel;
use App\Models\ReportItemModel;

use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Canvas\Factory as CanvasFactory;
use Dompdf\Options as DompdfOptions;
use Dompdf\Adapter\CPDF;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;

class DocumentController extends Controller
{
    public function index_report(Request $request, $id)
    {
        $report = ReportModel::getReportDetail(null,$id,'doc');
        $user_id = $request->user()->id ?? null;

        if($report){
            $report_item = ReportItemModel::getReportItem(null,$id,'doc');
            $options = new DompdfOptions();
            $options->set('defaultFont', 'Helvetica');
            $dompdf = new Dompdf($options);
            $datetime = now();

            $html = Document::documentTemplateReport(null,null,null,$report,$report_item);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $file_name = "report-$id-$datetime.pdf";
            if($user_id){
                $user = UserModel::select('telegram_user_id','username')
                    ->where('id',$user_id)
                    ->where('telegram_is_valid',1)
                    ->first();
                if($user->telegram_user_id){
                    $pdfContent = $dompdf->output();
                    $pdfFilePath = public_path($file_name);
                    file_put_contents($pdfFilePath, $pdfContent);
                    $inputFile = InputFile::create($pdfFilePath, $pdfFilePath);

                    $response = Telegram::sendDocument([
                        'chat_id' => $user->telegram_user_id,
                        'document' => $inputFile,
                        'caption' => "Hello $user->username, you just preview the report document. Here's the document",
                        'parse_mode' => 'HTML'
                    ]);

                    unlink($pdfFilePath);
                }
            }

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "inline; filename='$file_name'");
        } else {
            return redirect("/login");
        }
    }

    public function custom_report(Request $request, $id){
        $user_id = $request->user()->id;

        if($user_id){
            $filter_in = $request->query('filter_in', null);
            return view('custom.index')
                ->with('type','report')
                ->with('id',$id)
                ->with('filter_in',$filter_in);
        } else {
            return redirect("/login");
        }
    }

    public function index_layout(Request $request, $room)
    {
        $user_id = $request->user()->id;
        $inventory = InventoryModel::getInventoryByRoom($room,$user_id);
        $layout = InventoryLayoutModel::getInventoryByLayout($user_id, $room);

        if($inventory || $layout){
            $options = new DompdfOptions();
            $options->set('defaultFont', 'Helvetica');
            $dompdf = new Dompdf($options);
            $datetime = now();
    
            $html = Document::documentTemplateLayout(null,null,null,$layout,$inventory,$room);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $user_id = Generator::getUserId(session()->get('role_key'));
            $file_name = "layout-$room-$datetime.pdf";

            if($user_id){
                $user = UserModel::select('telegram_user_id','username')
                    ->where('id',$user_id)
                    ->where('telegram_is_valid',1)
                    ->first();

                if($user->telegram_user_id){
                    $pdfContent = $dompdf->output();
                    $pdfFilePath = public_path($file_name);
                    file_put_contents($pdfFilePath, $pdfContent);
                    $inputFile = InputFile::create($pdfFilePath, $pdfFilePath);

                    $response = Telegram::sendDocument([
                        'chat_id' => $user->telegram_user_id,
                        'document' => $inputFile,
                        'caption' => "Hello $user->username, you just preview the $room layout document. Here's the document",
                        'parse_mode' => 'HTML'
                    ]);

                    unlink($pdfFilePath);
                }
            }

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "inline; filename='$file_name'");
        } else {
            return redirect("/login");
        }
    }

    public function custom_layout(Request $request, $id){
        $user_id = $request->user()->id;

        if($user_id){
            return view('custom.index')
                ->with('type','layout')
                ->with('id',$id)
                ->with('filter_in',null);
        } else {
            return redirect("/login");
        }
    }

    public function index_inventory(Request $request, $id)
    {
        $user_id = $request->user()->id;
        $inventory = InventoryModel::getInventoryDetail($id,$user_id);

        if($inventory){
            $reminder = ReminderModel::getReminderByInventoryId($id,$user_id);
            $options = new DompdfOptions();
            $options->set('defaultFont', 'Helvetica');
            $dompdf = new Dompdf($options);
            $datetime = now();

            $html = Document::documentTemplateInventory(null,null,null,$inventory,$reminder);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $file_name = "inventory-$id-$datetime.pdf";
            if($user_id){
                $user = UserModel::select('telegram_user_id','username')
                    ->where('id',$user_id)
                    ->where('telegram_is_valid',1)
                    ->first();

                if($user->telegram_user_id){
                    $pdfContent = $dompdf->output();
                    $pdfFilePath = public_path($file_name);
                    file_put_contents($pdfFilePath, $pdfContent);
                    $inputFile = InputFile::create($pdfFilePath, $pdfFilePath);

                    $response = Telegram::sendDocument([
                        'chat_id' => $user->telegram_user_id,
                        'document' => $inputFile,
                        'caption' => "Hello $user->username, you just preview the inventory document. Here's the document",
                        'parse_mode' => 'HTML'
                    ]);

                    unlink($pdfFilePath);
                }
            }

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "inline; filename='$file_name'");
        } else {
            return redirect("/login");
        }
    }

    public function custom_inventory(Request $request, $id){
        $user_id = $request->user()->id;

        if($user_id){
            return view('custom.index')
                ->with('type','inventory')
                ->with('id',$id)
                ->with('filter_in',null);
        } else {
            return redirect("/login");
        }
    }
}
