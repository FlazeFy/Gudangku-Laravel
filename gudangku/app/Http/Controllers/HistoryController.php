<?php

namespace App\Http\Controllers;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Audit;
use App\Helpers\TelegramMessage;

// Export
use App\Exports\HistoryExport;

// Models
use App\Models\HistoryModel;
use App\Models\AdminModel;
use App\Models\UserModel;

class HistoryController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('history.index');
        } else {
            return redirect("/login");
        }
    }

    public function hard_delete($id)
    {
        $res = HistoryModel::destroy($id);

        if($res){
            return redirect()->back()->with('success_mini_message', "Success delete history");
        } else {
            return redirect()->back()->with('failed_message', "Failed delete history");
        }
    }

    public function save_as_csv(){
        $user_id = Generator::getUserId(session()->get('role_key'));
        $check_admin = AdminModel::find($user_id);

        $res = HistoryModel::select('*');
            if(!$check_admin){
                $res->where('created_by',$user_id);
            }
        $res = $res->orderBy('created_at', 'DESC')
            ->get();

        if($res->isNotEmpty()){
            try {
                $datetime = date('l, j F Y \a\t H:i:s');
                $user = UserModel::getSocial($user_id);
                $username = "";
                if(!$check_admin){
                    $username = "-$user->username";
                }
                $file_name = "History Data$username-$datetime.xlsx";

                Audit::createHistory('Print item', 'History', $user_id);

                session()->flash('success_message', 'Success generate data');
                
                Excel::store(new class($res) implements WithMultipleSheets {
                    private $res;
        
                    public function __construct($res)
                    {
                        $this->res = $res;
                    }
        
                    public function sheets(): array
                    {
                        return [new HistoryExport($this->res)];
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
                            'caption' => "[ADMIN] History export is ready",
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
