<?php

namespace App\Http\Controllers;

use App\Models\InventoryModel;

use App\Helpers\Generator;

use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            // Toogle total selection
            if(session()->get('toogle_total_stats') == 'item'){
                $query_total = 'COUNT(1)';
            } else if(session()->get('toogle_total_stats') == 'price'){
                $query_total = 'SUM(inventory_price)';
            }

            $total_inventory_by_cat = InventoryModel::selectRaw("inventory_category as context, $query_total as total")
                ->where('created_by', $user_id)
                ->groupby('inventory_category')
                ->get();

            $total_inventory_by_fav = InventoryModel::selectRaw("
                    CASE 
                        WHEN is_favorite = 1 THEN 'Favorite' 
                        ELSE 'Normal Item' 
                    END AS context, 
                    $query_total as total")
                ->where('created_by', $user_id)
                ->groupby('is_favorite')
                ->get();

            $total_inventory_by_room = InventoryModel::selectRaw("inventory_room as context, $query_total as total")
                ->where('created_by', $user_id)
                ->groupby('inventory_room')
                ->get();

            return view('stats.index')
                ->with('total_inventory_by_cat',$total_inventory_by_cat)
                ->with('total_inventory_by_room',$total_inventory_by_room)
                ->with('total_inventory_by_fav',$total_inventory_by_fav);
        } else {
            return redirect("/login");
        }
    }

    public function toogle_total(Request $request)
    {
        $request->session()->put('toogle_total_stats', $request->toogle_total);

        return redirect()->back();
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
