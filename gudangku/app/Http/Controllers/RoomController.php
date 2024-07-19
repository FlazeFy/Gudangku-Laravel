<?php

namespace App\Http\Controllers;

use App\Models\InventoryModel;

use App\Helpers\Generator;

use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('room.index');
        } else {
            return redirect("/login");
        }
    }
}
