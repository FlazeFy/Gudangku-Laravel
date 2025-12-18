<?php

namespace App\Http\Controllers;

// Helpers
use App\Helpers\Generator;
// Models
use App\Models\DictionaryModel;

class AddReportController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            $dct_cat = DictionaryModel::where('dictionary_type', 'report_category')->get();

            return view('add_report.index')->with('dct_cat',$dct_cat);
        } else {
            return redirect("/login");
        }
    }
}
