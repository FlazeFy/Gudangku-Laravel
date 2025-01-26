<?php

namespace App\Http\Controllers;

// Helpers
use App\Helpers\Generator;

class LandingController extends Controller
{
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
            if(!session()->get('toogle_view_stats')){
                session()->put('toogle_view_stats', 'top chart');
            }
            if(!session()->get('toogle_select_year')){
                session()->put('toogle_select_year', date('Y'));
            }

            return view('landing.index');
        } else {
            return redirect("/login");
        }
    }
}
