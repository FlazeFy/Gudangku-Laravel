<?php

namespace App\Http\Controllers;

// Helpers
use App\Helpers\Generator;

// Models
use App\Models\DictionaryModel;

class AddController extends Controller
{
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
}
