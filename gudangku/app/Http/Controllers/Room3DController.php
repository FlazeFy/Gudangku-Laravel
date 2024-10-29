<?php

namespace App\Http\Controllers;

use App\Models\InventoryModel;

use App\Helpers\Generator;

use Illuminate\Http\Request;

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
