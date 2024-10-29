<?php

namespace App\Http\Controllers;

use App\Helpers\Generator;

use App\Models\InventoryModel;

use Illuminate\Http\Request;

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
