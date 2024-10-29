<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index()
    {
        return view('login.index');
    }

    public function login_auth(Request $request){
        $request->session()->put('username_key', $request->username);
        $request->session()->put('role_key', $request->role);
        $request->session()->put('token_key', $request->token);
        $request->session()->put('email_key', $request->email);
        $request->session()->put('id_key', $request->id);

        return redirect()->route('landing');
    }
}
