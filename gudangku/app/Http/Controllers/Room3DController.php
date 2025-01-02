<?php

namespace App\Http\Controllers;

// Helpers
use App\Helpers\Generator;

class Room3DController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('room.3d.index');
        } else {
            return redirect("/login");
        }
    }
}
