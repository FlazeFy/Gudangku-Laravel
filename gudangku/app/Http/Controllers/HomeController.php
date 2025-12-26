<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

// Models
use App\Models\InventoryModel;
use App\Models\UserModel;
use App\Models\AdminModel;
// Helpers
use App\Helpers\Generator;
use App\Helpers\Audit;
use App\Helpers\TelegramMessage;
// Export
use App\Exports\ActiveInventoryExport;
use App\Exports\DeletedInventoryExport;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            $selected = session()->get('toogle_view_inventory');

            if($selected == 'table'){
                $search_key = $request->query('search_key');
                $filter_category = $request->query('filter_category');
                $sorting = $request->query('sorting');

                return view('home.index')
                    ->with('search_key',$search_key)
                    ->with('sorting',$sorting)
                    ->with('filter_category',$filter_category);
            } elseif($selected == 'catalog'){
                return view('home.index');
            }
        } else {
            return redirect("/login");
        }
    }

    public function catalog_index($view, $context)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('home.catalog.index')
                ->with('view',$view)
                ->with('context',$context);
        } else {
            return redirect("/login");
        }
    }

    public function save_as_csv(){
        $user_id = Generator::getUserId(session()->get('role_key'));
        $check_admin = AdminModel::find($user_id);
        
        $res_active = InventoryModel::getInventoryExport($check_admin ? null : $user_id, $check_admin ? true : false, 'active');
        $res_deleted = InventoryModel::getInventoryExport($check_admin ? null : $user_id, $check_admin ? true : false, 'deleted');

        if($res_active->isNotEmpty()){
            try {
                $user = UserModel::getSocial($user_id);
                $datetime = date('l, j F Y \a\t H:i:s');
                $file_name = "Inventory Data-$user->username-$datetime.xlsx";

                Audit::createHistory('Print item', 'Inventory', $user_id);

                session()->flash('success_message', 'Success generate data');
                Excel::store(new class($res_active, $res_deleted) implements WithMultipleSheets {
                    private $activeInventory;
                    private $deletedInventory;
        
                    public function __construct($activeInventory, $deletedInventory)
                    {
                        $this->activeInventory = $activeInventory;
                        $this->deletedInventory = $deletedInventory;
                    }
        
                    public function sheets(): array
                    {
                        return [new ActiveInventoryExport($this->activeInventory), new DeletedInventoryExport($this->deletedInventory)];
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
                            'caption' => "Your inventory export is ready",
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

    public function toogle_view(Request $request)
    {
        $request->session()->put('toogle_view_inventory', $request->toogle_view);

        return redirect()->back();
    }
}
