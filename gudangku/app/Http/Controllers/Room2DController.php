<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

// Helpers
use App\Helpers\Generator;

class Room2DController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('room.2d.index');
        } else {
            return redirect("/login");
        }
    }

    public function select_room(Request $request)
    {
        $request->session()->put('room_opened', $request->select_room);

        return redirect()->back();
    }
}
