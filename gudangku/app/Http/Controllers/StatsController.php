<?php

namespace App\Http\Controllers;

use App\Models\InventoryModel;

use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $total_inventory_by_cat = InventoryModel::selectRaw('inventory_category as context, COUNT(1) as total')
            ->groupby('inventory_category')
            ->get();

        $total_inventory_by_room = InventoryModel::selectRaw('inventory_room as context, COUNT(1) as total')
            ->groupby('inventory_room')
            ->get();

        return view('stats.index')
            ->with('total_inventory_by_cat',$total_inventory_by_cat)
            ->with('total_inventory_by_room',$total_inventory_by_room);
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
