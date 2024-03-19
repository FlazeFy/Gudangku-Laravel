<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\Generator;

use App\Models\InventoryModel;

class LandingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            if(!session()->get('toogle_total_stats')){
                session()->put('toogle_total_stats', 'item');
            }
            if(!session()->get('toogle_view_inventory')){
                session()->put('toogle_view_inventory', 'table');
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

            $last_added = InventoryModel::select('inventory_name')
                ->whereNull('deleted_at')
                ->orderBy('created_at','DESC')
                ->first();

            $most_category = InventoryModel::selectRaw('inventory_category as context, COUNT(1) as total')
                ->whereNull('deleted_at')
                ->groupBy('inventory_category')
                ->orderBy('total','DESC')
                ->first();

            $highest_price = InventoryModel::select('inventory_name', 'inventory_price')
                ->whereNull('deleted_at')
                ->orderBy('inventory_price','DESC')
                ->first();

            return view('landing.index')
                ->with('total_item',$total_item)
                ->with('total_fav',$total_fav)
                ->with('total_low',$total_low)
                ->with('last_added',$last_added)
                ->with('most_category',$most_category)
                ->with('highest_price',$highest_price);
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
