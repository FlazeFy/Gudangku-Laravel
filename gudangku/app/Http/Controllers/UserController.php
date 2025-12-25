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
// Models
use App\Models\AdminModel;
use App\Models\UserModel;
// Export
use App\Exports\UserExport;
use App\Exports\UserNotActiveExport;

class UserController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));
        $check = AdminModel::find($user_id);

        if($check != null){
            return view('user.index');
        } else {
            return redirect("/login");
        }
    }

    public function save_as_csv(){
        $user_id = Generator::getUserId(session()->get('role_key'));
        $check_admin = AdminModel::find($user_id);
        
        $res_all = UserModel::getUserExport();
        $res_not_have_inventory = $res_all->filter(function ($user) {
            return $user->total_inventory == 0;
        });
        $res_not_have_report = $res_all->filter(function ($user) {
            return $user->total_report == 0;
        });

        if($res_all->isNotEmpty()){
            try {
                $user = UserModel::getSocial($user_id);
                $datetime = date('l, j F Y \a\t H:i:s');
                $file_name = "User Data-$datetime.xlsx";

                Audit::createHistory('Print item', 'User', $user_id);

                session()->flash('success_message', 'Success generate data');
                Excel::store(new class($res_all, $res_not_have_inventory, $res_not_have_report) implements WithMultipleSheets {
                    private $allUser;
                    private $userNotHaveInventory;
                    private $userNotHaveReport;
        
                    public function __construct($allUser, $userNotHaveInventory, $userNotHaveReport)
                    {
                        $this->allUser = $allUser;
                        $this->userNotHaveInventory = $userNotHaveInventory;
                        $this->userNotHaveReport = $userNotHaveReport;
                    }
        
                    public function sheets(): array
                    {
                        return [
                            new UserExport($this->allUser), 
                            new UserNotActiveExport($this->userNotHaveInventory, 'User With No Inventory'),
                            new UserNotActiveExport($this->userNotHaveReport, 'User With No Report')
                        ];
                    }
                }, $file_name, 'public');

                $storagePath = storage_path("app/public/$file_name");
                $publicPath = public_path($file_name);
                if (!file_exists($storagePath)) {
                    throw new \Exception("File not found: $storagePath");
                }
        
                if ($user && $user->telegram_is_valid == 1 && $user->telegram_user_id) {
                    $inputFile = InputFile::create($storagePath, $file_name);
        
                    Telegram::sendDocument([
                        'chat_id' => $user->telegram_user_id,
                        'document' => $inputFile,
                        'caption' => "Your user export is ready",
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
    }
}
