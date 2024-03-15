<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\InventoryModel;

class LandingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(!session()->get('toogle_total_stats')){
            session()->put('toogle_total_stats', 'item');
        }

        $total_item = InventoryModel::selectRaw('COUNT(1) AS total')
            ->first();

        $total_fav = InventoryModel::selectRaw('COUNT(1) AS total')
            ->where('is_favorite','1')
            ->first();

        $total_low = InventoryModel::selectRaw('COUNT(1) AS total')
            ->where('inventory_capacity_unit','percentage')
            ->where('inventory_capacity_vol','<=',30)
            ->first();

        return view('landing.index')
            ->with('total_item',$total_item)
            ->with('total_fav',$total_fav)
            ->with('total_low',$total_low);
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
