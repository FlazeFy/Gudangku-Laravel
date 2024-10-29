<?php

namespace App\Http\Controllers;

use App\Helpers\Generator;

use Illuminate\Http\Request;

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
}
