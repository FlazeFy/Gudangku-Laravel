<?php

namespace App\Http\Controllers;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Audit;

// Models
use App\Models\AdminModel;
use App\Models\UserModel;
use App\Models\ScheduleMarkModel;

// Exports
use App\Exports\ReminderExport;

class ReminderController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));
        $check = AdminModel::find($user_id);

        if($check != null){
            return view('reminder.index');
        } else {
            return redirect("/login");
        }
    }

    public function save_as_csv(){
        $user_id = Generator::getUserId(session()->get('role_key'));
        $check_admin = AdminModel::find($user_id);

        if($check_admin){
            $res = ScheduleMarkModel::getAllReminderMark(false);

            if(count($res) > 0){
                try {
                    $user = UserModel::getSocial($user_id);
                    $datetime = date('l, j F Y \a\t H:i:s');
                    $file_name = "Reminder Data-$datetime.xlsx";

                    Audit::createHistory('Print item', 'Schedule Mark', $user_id);

                    session()->flash('success_message', 'Success generate data');

                    Excel::store(new class($res) implements WithMultipleSheets {
                        private $res;
            
                        public function __construct($res)
                        {
                            $this->res = $res;
                        }
                        public function sheets(): array
                        {
                            return [new ReminderExport($this->res)];
                        }
                    }, $file_name, 'public');
    
                    $storagePath = storage_path("app/public/$file_name");
                    $publicPath = public_path($file_name);
                    if (!file_exists($storagePath)) {
                        throw new \Exception("File not found: $storagePath");
                    }
                    copy($storagePath, $publicPath);
            
                    if ($user && $user->telegram_is_valid == 1 && $user->telegram_user_id) {
                        $inputFile = InputFile::create($publicPath, $file_name);
            
                        Telegram::sendDocument([
                            'chat_id' => $user->telegram_user_id,
                            'document' => $inputFile,
                            'caption' => "[ADMIN] Reminder export is ready",
                            'parse_mode' => 'HTML',
                        ]);
                    }

                    return response()->download($publicPath)->deleteFileAfterSend(true);
                } catch (\Exception $e) {
                    return redirect()->back()->with('failed_message', 'Something is wrong. Please try again');
                }
            } else {
                return redirect()->back()->with('failed_message', "No Data to generated");
            }
        } else {
            return redirect()->back()->with('failed_message', "only admin can use this request");
        }
    }
}
