<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Audit;
use App\Helpers\TelegramMessage;
// Models
use App\Models\ReportItemModel;
use App\Models\AdminModel;
use App\Models\UserModel;
// Export 
use App\Exports\ReportItemExport;
use App\Exports\ActiveInventoryExport;

class ReportDetailController extends Controller
{
    public function index($id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('report.detail.index')->with('id',$id);
        } else {
            return redirect("/login");
        }
    }

    public function toogle_edit(Request $request)
    {
        $request->session()->put('toogle_edit_report', $request->toogle_edit);

        return redirect()->back();
    }

    public function save_as_csv($id){
        $user_id = Generator::getUserId(session()->get('role_key'));
        $check_admin = AdminModel::find($user_id);
        
        $res_detail = ReportItemModel::getReportItem($user_id,$id,'data');
        $res_detail = $res_detail->map(function ($dt) {
            unset($dt['id'], $dt['inventory_id'], $dt['report_id'], $dt['created_by']);
            return $dt;
        });
        $res_inventory = ReportItemModel::getReportInventoryDetailExport($check_admin ? null : $user_id, $check_admin ? true : false, $id);

        if($res_detail->isNotEmpty()){
            try {
                $user = UserModel::getSocial($user_id);
                $datetime = date('l, j F Y \a\t H:i:s');
                $file_name = "Report Detail Data-$user->username-$datetime.xlsx";

                Audit::createHistory('Print item', 'Report Detail', $user_id);

                session()->flash('success_message', 'Success generate data');
                Excel::store(new class($res_detail, $res_inventory) implements WithMultipleSheets {
                    private $reportDetail;
                    private $inventoryDetail;
        
                    public function __construct($reportDetail, $inventoryDetail)
                    {
                        $this->reportDetail = $reportDetail;
                        $this->inventoryDetail = $inventoryDetail;
                    }
        
                    public function sheets(): array
                    {
                        return [new ReportItemExport($this->reportDetail), new ActiveInventoryExport($this->inventoryDetail)];
                    }
                }, $file_name, 'public');

                $storagePath = storage_path("app/public/$file_name");
                $publicPath = public_path($file_name);
                if (!file_exists($storagePath)) {
                    throw new \Exception("File not found: $storagePath");
                }
        
                if ($user && $user->telegram_is_valid == 1 && $user->telegram_user_id) {
                    if(TelegramMessage::checkTelegramID($user->telegram_user_id)){
                        $inputFile = InputFile::create($storagePath, $file_name);
        
                        Telegram::sendDocument([
                            'chat_id' => $user->telegram_user_id,
                            'document' => $inputFile,
                            'caption' => "Your report detail export is ready",
                            'parse_mode' => 'HTML',
                        ]);
                    } else {
                        if (file_exists($publicPath)) {
                            unlink($publicPath);
                        }
                        return redirect()->back()->with('failed_message', 'Telegram ID is invalid. Please check your Telegram ID');
                    }
                }

                return response()->download($publicPath)->deleteFileAfterSend(true);
            } catch (\Exception $e) {
                if (file_exists($publicPath)) {
                    unlink($publicPath);
                }
                return redirect()->back()->with('failed_message', 'Something is wrong. Please try again');
            }
        } else {
            return redirect()->back()->with('failed_message', "No Data to generated");
        }
    }
}
