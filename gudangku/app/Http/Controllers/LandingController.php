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
            if(!session()->get('toogle_edit_report')){
                session()->put('toogle_edit_report', 'false');
            }
            if(!session()->get('room_opened')){
                session()->put('room_opened', 'Main Room');
            }

            return view('landing.index');
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
