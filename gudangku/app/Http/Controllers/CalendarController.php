<?php

namespace App\Http\Controllers;

// Helpers
use App\Helpers\Generator;

class CalendarController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('calendar.index');
        } else {
            return redirect("/login");
        }
    }
}
