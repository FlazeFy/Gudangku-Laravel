<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\InventoryModel;
use App\Models\DictionaryModel;
use App\Models\ReminderModel;

use App\Helpers\Generator;

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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
