<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\Generator;

use App\Models\DictionaryModel;
use App\Models\InventoryModel;

class AddController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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
    }

    public function create(Request $request)
    {
        InventoryModel::create([
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
            'updated_at' => null, 
            'deleted_at' => null
        ]);

        return redirect()->route('/');
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