<?php

namespace App\Http\Controllers;

use App\Models\InventoryModel;
use App\Models\DictionaryModel;
use App\Models\ReminderModel;
use App\Models\UserModel;

use App\Helpers\Generator;
use App\Helpers\Audit;

use App\Exports\InventoryExport;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Twilio\Rest\Client;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            $selected = session()->get('toogle_view_inventory');

            $inventory_name = InventoryModel::select('inventory.id','inventory_name','inventory_category')
                ->leftjoin('reminder','reminder.inventory_id','=','inventory.id')
                ->where('inventory.created_by',$user_id)
                ->whereNull('deleted_at')
                ->whereNull('reminder.inventory_id')
                ->orderBy('inventory_name','DESC')
                ->get();

            $dct_reminder_type = DictionaryModel::where('dictionary_type', 'reminder_type')
                ->get();

            $dct_reminder_context = DictionaryModel::where('dictionary_type', 'reminder_context')
                ->get();
                
            if($selected == 'table'){
                $inventory = InventoryModel::select('inventory.id', 'inventory_name', 'inventory_category', 'inventory_desc', 'inventory_merk', 'inventory_room', 
                    'inventory_storage', 'inventory_rack', 'inventory_price', 'inventory_image', 'inventory_unit', 'inventory_vol', 'inventory_capacity_unit', 
                    'inventory_capacity_vol', 'is_favorite', 'is_reminder', 'inventory.created_at', 'inventory.updated_at', 'inventory.deleted_at',
                    'reminder.id as reminder_id', 'reminder_desc', 'reminder_type', 'reminder_context', 'reminder.created_at as reminder_created_at', 'reminder.updated_at as reminder_updated_at')
                    ->leftjoin('reminder','reminder.inventory_id','=','inventory.id')
                    ->where('inventory.created_by',$user_id)
                    ->orderBy('is_favorite', 'desc')
                    ->orderBy('inventory.created_at', 'desc')
                    ->paginate(15);

                return view('home.index')
                    ->with('inventory',$inventory)
                    ->with('inventory_name',$inventory_name)
                    ->with('dct_reminder_type', $dct_reminder_type)
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
                    ->with('room',$room)
                    ->with('category',$category)
                    ->with('storage',$storage)
                    ->with('inventory_name',$inventory_name)
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

    public function soft_delete(Request $request, $id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        $res = InventoryModel::where('id',$id)
            ->where('created_by', $user_id)
            ->update([
                'deleted_at' => date('Y-m-d H:i:s')
        ]);

        if($res){
            Audit::createHistory('Delete item', $request->inventory_name, $user_id);

            return redirect()->back()->with('success_message', "Success delete $request->inventory_name");
        } else {
            return redirect()->back()->with('failed_message', "Failed delete $request->inventory_name");
        }
    }

    public function hard_delete(Request $request, $id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        $res = InventoryModel::destroy($id);

        if($res){
            Audit::createHistory('Permentally delete item', $request->inventory_name, $user_id);

            return redirect()->back()->with('success_message', "Success permentally delete $request->inventory_name");
        } else {
            return redirect()->back()->with('failed_message', "Failed permentally delete $request->inventory_name");
        }
    }

    public function recover(Request $request, $id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        $res = InventoryModel::where('id',$id)
            ->where('created_by', $user_id)
            ->update([
                'deleted_at' => null
        ]);

        if($res){
            Audit::createHistory('Recover item', $request->inventory_name, $user_id);

            return redirect()->back()->with('success_message', "Success recover $request->inventory_name");
        } else {
            return redirect()->back()->with('failed_message', "Failed recover $request->inventory_name");
        }
    }

    public function save_as_csv(){
        $user_id = Generator::getUserId(session()->get('role_key'));

        $data = InventoryModel::select('*')
            ->where('created_by', $user_id)
            ->orderBy('created_at', 'DESC')
            ->get();

        if($data->isNotEmpty()){
            try {
                $file_name = date('l, j F Y \a\t H:i:s');
                Audit::createHistory('Print item', 'Inventory', $user_id);

                session()->flash('success_message', 'Success generate data');
                return Excel::download(new InventoryExport($data), "$file_name-Inventory Data.xlsx");
            } catch (\Exception $e) {
                return redirect()->back()->with('failed_message', 'Something is wrong. Please try again');
            }
        } else {
            return redirect()->back()->with('failed_message', "No Data to generated");
        }
    }

    public function fav_toogle(Request $request, $id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        $res = InventoryModel::where('id',$id)
            ->where('created_by', $user_id)
            ->update([
                'is_favorite' => $request->is_favorite
        ]);

        $ctx = 'Set';
        if($request->is_favorite == 0){
            $ctx = 'Unset';
        }

        if($res){
            Audit::createHistory($ctx.' to favorite', $request->inventory_name, $user_id);

            return redirect()->back()->with('success_mini_message', "$ctx $request->inventory_name to favorite");
        } else {
            return redirect()->back()->with('failed_message', "$ctx $request->inventory_name to favorite");
        }
    }

    public function toogle_view(Request $request)
    {
        $request->session()->put('toogle_view_inventory', $request->toogle_view);

        return redirect()->back();
    }

    public function hard_delete_reminder(Request $request, $id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        $res = ReminderModel::destroy($id);

        if($res){
            Audit::createHistory('Permentally delete reminder : ', $request->reminder_desc, $user_id);
            
            return redirect()->back()->with('success_message', "Success permentally delete reminder : $request->reminder_desc");
        } else {
            return redirect()->back()->with('failed_message', "Failed permentally delete reminder : $request->reminder_desc");
        }
    }

    public function copy_reminder(Request $request, $id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));
        $count = count($request->inventory_id);
        $success_exec = 0;
        $failed_exec = 0;

        for($i = 0; $i < $count; $i++){
            $res = ReminderModel::create([
                'id' => Generator::getUUID(), 
                'inventory_id' => $request->inventory_id[$i], 
                'reminder_desc' => $request->reminder_desc, 
                'reminder_type' => $request->reminder_type, 
                'reminder_context' => $request->reminder_context, 
                'created_at' => date('Y-m-d H:i:s'), 
                'created_by' => $user_id, 
                'updated_at' => null
            ]);
            
            if($res){
                $success_exec++;
            } else {
                $failed_exec++;
            }
        }

        if($failed_exec == 0 && $success_exec == $count){
            Audit::createHistory('Copy reminder', $request->reminder_desc, $user_id);

            return redirect()->back()->with('success_mini_message', "Success copy reminder : $request->reminder_desc");
        } else if($failed_exec > 0 && $success_exec > 0){
            Audit::createHistory('Copy reminder', $request->reminder_desc, $user_id);

            return redirect()->back()->with('success_mini_message', "Success some copy reminder : $request->reminder_desc. About $failed_exec inventory failed to copy");
        } else {
            return redirect()->back()->with('failed_message', "Failed copy reminder : $request->reminder_desc");
        }
    }

    public function edit_reminder(Request $request, $id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        $res = ReminderModel::where('id',$id)
            ->where('created_by', $user_id)
            ->update([
                'reminder_desc' => $request->reminder_desc,
                'reminder_type' => $request->reminder_type,
                'reminder_context' => $request->reminder_context,
                'updated_at' => date('Y-m-d H:i:s')
        ]);

        if($res){
            Audit::createHistory('Updated reminder', $request->reminder_desc, $user_id);

            return redirect()->back()->with('success_mini_message', "Success updated reminder : $request->reminder_desc");
        } else {
            return redirect()->back()->with('failed_message', "Failed updated reminder : $request->reminder_desc");
        }
    }

    public function get_all_inventory_wa_bot()
    {
        $sid    = env('TWILIO_SID');
        $token  = env('TWILIO_TOKEN');
        $twilio = new Client($sid, $token);
        $user_id = Generator::getUserId(session()->get('role_key'));
        $i = 1;
        $inventory_category_before = '';

        // Fetching
        $user = UserModel::select('phone','username')
            ->where('id',$user_id)
            ->first();

        $res = InventoryModel::select('inventory_name','inventory_category')
            ->where('created_by',$user_id)
            ->whereNull('deleted_at')
            ->orderBy('inventory_category', 'desc')
            ->orderBy('is_favorite', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Bot Exec
        $body = "Hello, $user->username. You have ".count($res)." item in your inventory.\nHere the list :\n";

        foreach($res as $dt){
            if($inventory_category_before == '' || $inventory_category_before != $dt->inventory_category){
                $body .= "\nCategory : $dt->inventory_category\n";
                $inventory_category_before = $dt->inventory_category;
            }
            $body .= "$i. $dt->inventory_name\n";
            $i++;
        }

        $message = $twilio->messages
            ->create("whatsapp:$user->phone", 
                [
                    "from" => "whatsapp:+".env('TWILIO_FROM'),
                    "body" => $body,
                ]
            );

        return redirect()->back();
    }
}
