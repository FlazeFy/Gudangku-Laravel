<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\Generator;
use App\Helpers\Audit;

use App\Models\DictionaryModel;
use App\Models\InventoryModel;

use App\Jobs\ProcessMailer;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewInventoryMail;


class AddController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            $dct_cat = DictionaryModel::where('dictionary_type', 'inventory_category')
                ->get();
            
            $dct_unit = DictionaryModel::where('dictionary_type', 'inventory_unit')
                ->get();

            $dct_room = DictionaryModel::where('dictionary_type', 'inventory_room')
                ->get();

            return view('add.index')
                ->with('dct_cat',$dct_cat)
                ->with('dct_unit',$dct_unit)
                ->with('dct_room',$dct_room);
        } else {
            return redirect("/login");
        }
    }

    public function create(Request $request)
    {
        $ctx = 'Create item';
        $user_id = Generator::getUserId(session()->get('role_key'));
        $email = Generator::getUserEmail($user_id);

        $data = [
            'id' => Generator::getUUID(), 
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
            'inventory_capacity_unit' => $request->inventory_capacity_unit, 
            'inventory_capacity_vol' => $request->inventory_capacity_vol, 
            'is_favorite' => 0, 
            'is_reminder' => 0, 
            'created_at' => date("Y-m-d H:i:s"), 
            'created_by' => $user_id,
            'updated_at' => null, 
            'deleted_at' => null
        ];

        InventoryModel::create($data);

        // Send email
        dispatch(new ProcessMailer($ctx, $data, session()->get('username_key'), $email));

        // History
        Audit::createHistory($ctx, $request->inventory_name, $user_id);

        return redirect()->route('home');
    }
}
