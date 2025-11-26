<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request){
        $user_id = $request->user()->id;

        if($user_id){
            return view('chat.index');
        } else {
            return redirect("/login");
        }
    }
}
