<?php

namespace App\Http\Controllers;

use App\Helpers\Generator;
use App\Models\AdminModel;

class UserController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));
        $check = AdminModel::find($user_id);

        if($check != null){
            return view('user.index');
        } else {
            return redirect("/login");
        }
    }
}
