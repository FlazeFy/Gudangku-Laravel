<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

// Helpers
use App\Helpers\Generator;

class StatsController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('stats.index');
        } else {
            return redirect("/login");
        }
    }

    public function toogle_total(Request $request)
    {
        $request->session()->put('toogle_total_stats', $request->toogle_total);

        return redirect()->back();
    }

    public function toogle_view(Request $request)
    {
        $request->session()->put('toogle_view_stats', $request->toogle_view);

        return redirect()->back();
    }

    public function toogle_year(Request $request)
    {
        $request->session()->put('toogle_select_year', $request->toogle_year);

        return redirect()->back();
    }
}
