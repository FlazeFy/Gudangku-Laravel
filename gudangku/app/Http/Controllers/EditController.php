<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\InventoryModel;
use App\Models\DictionaryModel;
use App\Models\ReminderModel;

use App\Helpers\Generator;
use App\Helpers\Audit;

class EditController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){

            $dct_cat = DictionaryModel::where('dictionary_type', 'inventory_category')
                ->get();
            
            $dct_unit = DictionaryModel::where('dictionary_type', 'inventory_unit')
                ->get();

            $dct_room = DictionaryModel::where('dictionary_type', 'inventory_room')
                ->get();

            $dct_reminder_type = DictionaryModel::where('dictionary_type', 'reminder_type')
                ->get();
    
            $dct_reminder_context = DictionaryModel::where('dictionary_type', 'reminder_context')
                ->get();

            $inventory = InventoryModel::select('inventory.id', 'inventory_name', 'inventory_category', 'inventory_desc', 'inventory_merk', 'inventory_room', 
                'inventory_storage', 'inventory_rack', 'inventory_price', 'inventory_image', 'inventory_unit', 'inventory_vol', 'inventory_capacity_unit', 
                'inventory_capacity_vol', 'is_favorite', 'is_reminder', 'inventory.created_at', 'inventory.updated_at', 'inventory.deleted_at',
                'reminder.id as reminder_id', 'reminder_desc', 'reminder_type', 'reminder_context', 'reminder.created_at as reminder_created_at', 'reminder.updated_at as reminder_updated_at')
                ->leftjoin('reminder','reminder.inventory_id','=','inventory.id')
                ->where('inventory.created_by',$user_id)
                ->where('inventory.id',$id)
                ->orderBy('is_favorite', 'desc')
                ->orderBy('inventory.created_at', 'desc')
                ->first();

            return view('edit.index')
                ->with('inventory',$inventory)
                ->with('dct_reminder_type', $dct_reminder_type)
                ->with('dct_reminder_context', $dct_reminder_context)
                ->with('dct_cat',$dct_cat)
                ->with('dct_unit',$dct_unit)
                ->with('dct_room',$dct_room);
        } else {
            return redirect("/login");
        }
    }

    public function update(Request $request, $id)
    {
        $ctx = 'Update item';
        $user_id = Generator::getUserId(session()->get('role_key'));
        $email = Generator::getUserEmail($user_id);

        // Nullable
        $inventory_capacity_unit = $request->inventory_capacity_unit;
        $inventory_capacity_vol = $request->inventory_capacity_vol;

        if($inventory_capacity_unit == '-'){
            $inventory_capacity_unit = null;
            $inventory_capacity_vol = null;
        }

        $data = [
            'inventory_name' => $request->inventory_name, 
            'inventory_category' => $request->inventory_category, 
            'inventory_desc' => $request->inventory_desc, 
            'inventory_merk' => $request->inventory_merk, 
            'inventory_room' => $request->inventory_room, 
            'inventory_storage' => $request->inventory_storage, 
            'inventory_rack' => $request->inventory_rack, 
            'inventory_price' => $request->inventory_price, 
            'inventory_unit' => $request->inventory_unit, 
            'inventory_vol' => $request->inventory_vol, 
            'inventory_capacity_unit' => $inventory_capacity_unit, 
            'inventory_capacity_vol' => $inventory_capacity_vol, 
            'updated_at' => date("Y-m-d H:i:s")
        ];

        InventoryModel::where('id',$id)->update($data);

        // History
        Audit::createHistory($ctx, $request->inventory_name, $user_id);

        return redirect()->back();
    }
}
