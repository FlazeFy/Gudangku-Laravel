<?php

namespace App\Http\Controllers;

// Helpers
use App\Helpers\Generator;

class EditController extends Controller
{
    public function index($id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('edit.index')->with('id',$id);
        } else {
            return redirect("/login");
        }
    }
}
