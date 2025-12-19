<?php

namespace App\Http\Controllers;

// Helpers
use App\Helpers\Generator;

class AddReportController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('add_report.index');
        } else {
            return redirect("/login");
        }
    }
}
