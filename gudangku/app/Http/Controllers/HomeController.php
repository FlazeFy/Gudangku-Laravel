<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

// Models
use App\Models\InventoryModel;
use App\Models\DictionaryModel;
use App\Models\ReminderModel;
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
            $search_key = $request->query('search_key');
            $filter_category = $request->query('filter_category');
            $sorting = $request->query('sorting');
            $selected = session()->get('toogle_view_inventory');

            $dct_reminder_type = DictionaryModel::where('dictionary_type', 'reminder_type')
                ->get();

            $dct_reminder_context = DictionaryModel::where('dictionary_type', 'reminder_context')
                ->get();
                
            if($selected == 'table'){
                return view('home.index')
                    ->with('search_key',$search_key)
                    ->with('sorting',$sorting)
                    ->with('dct_reminder_type', $dct_reminder_type)
                    ->with('filter_category',$filter_category)
                    ->with('dct_reminder_context', $dct_reminder_context);
            } elseif($selected == 'catalog'){
                $room = DictionaryModel::selectRaw('dictionary_name, COUNT(1) as total')
                    ->leftjoin('inventory','inventory_room','=','dictionary_name')
                    ->where('dictionary_type','inventory_room')
                    ->where('inventory.created_by',$user_id)
                    ->groupby('dictionary_name')
                    ->orderby('dictionary_name','ASC')
                    ->get();

                $category = DictionaryModel::selectRaw('dictionary_name, COUNT(1) as total')
                    ->leftjoin('inventory','inventory_category','=','dictionary_name')
                    ->where('dictionary_type','inventory_category')
                    ->where('inventory.created_by',$user_id)
                    ->groupby('dictionary_name')
                    ->orderby('dictionary_name','ASC')
                    ->get();

                $storage = InventoryModel::selectRaw('inventory_storage, COUNT(1) as total')
                    ->whereNotNull('inventory_storage')
                    ->where('inventory.created_by',$user_id)
                    ->where('created_by', $user_id)
                    ->groupby('inventory_storage')
                    ->orderby('inventory_storage','ASC')
                    ->get();

                return view('home.index')
                    ->with('search_key',$search_key)
                    ->with('filter_category',$filter_category)
                    ->with('sorting',$sorting)
                    ->with('room',$room)
                    ->with('category',$category)
                    ->with('storage',$storage)
                    ->with('dct_reminder_type', $dct_reminder_type)
                    ->with('dct_reminder_context', $dct_reminder_context);
            }
        } else {
            return redirect("/login");
        }
    }

    public function catalog_index($view, $context)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            if($view == "room"){
                $other = DictionaryModel::select('dictionary_name')
                    ->where('dictionary_type','inventory_room')
                    ->orderby('dictionary_name','ASC')
                    ->get();
            } else if($view == "category"){
                $other = DictionaryModel::select('dictionary_name')
                    ->where('dictionary_type','inventory_category')
                    ->orderby('dictionary_name','ASC')
                    ->get();
            } else if($view == "storage"){
                $other = InventoryModel::select('inventory_storage')
                    ->whereNotNull('inventory_storage')
                    ->where('created_by', $user_id)
                    ->groupby('inventory_storage')
                    ->orderby('inventory_storage','ASC')
                    ->get();
            }
            
            $inventory = InventoryModel::select('inventory.id', 'inventory_name', 'inventory_category', 'inventory_desc', 'inventory_merk', 'inventory_room', 
                'inventory_storage', 'inventory_rack', 'inventory_price', 'inventory_image', 'inventory_unit', 'inventory_vol', 'inventory_capacity_unit', 
                'inventory_capacity_vol', 'is_favorite', 'is_reminder', 'inventory.created_at', 'inventory.updated_at', 'inventory.deleted_at',
                'reminder.id as reminder_id', 'reminder_desc', 'reminder_type', 'reminder_context', 'reminder.created_at as reminder_created_at', 'reminder.updated_at as reminder_updated_at')
                ->leftjoin('reminder','reminder.inventory_id','=','inventory.id')
                ->where("inventory_$view", $context)
                ->where('inventory.created_by',$user_id)
                ->orderBy('is_favorite', 'desc')
                ->orderBy('inventory.created_at', 'desc')
                ->get();

            return view('home.catalog.index')
                ->with('other',$other)
                ->with('view',$view)
                ->with('context',$context)
                ->with('inventory',$inventory);
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
                copy($storagePath, $publicPath);
        
                if ($user && $user->telegram_is_valid == 1 && $user->telegram_user_id) {
                    if(TelegramMessage::checkTelegramID($user->telegram_user_id)){
                        $inputFile = InputFile::create($publicPath, $file_name);
            
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
