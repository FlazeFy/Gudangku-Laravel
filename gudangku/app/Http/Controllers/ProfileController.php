<?php

namespace App\Http\Controllers;

use App\Helpers\Generator;
use App\Models\UserModel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            $profile = UserModel::select('*')
                ->where('id',$user_id)
                ->first();

            return view('profile.index')
                ->with('profile',$profile);
        } else {
            return redirect("/login");
        }
    }

    public function sign_out()
    {
        Session::flush();

        return redirect('/login')->with('success_message', 'Successfully sign out'); 
    }
}
