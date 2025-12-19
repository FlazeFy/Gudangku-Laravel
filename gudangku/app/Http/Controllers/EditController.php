<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

// Models
use App\Models\ReportModel;
use App\Models\ReportItemModel;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Audit;

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
