<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request) {
        $user_id = $request->user()->id;

        return $user_id != null ? view('chat.index') : redirect("/login");
    }
}
