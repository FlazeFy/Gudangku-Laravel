<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Audit;

// Models
use App\Models\DictionaryModel;

class ReportDetailController extends Controller
{
    public function index($id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            $dct_cat = DictionaryModel::where('dictionary_type', 'report_category')
                ->get();

            return view('report.detail.index')
                ->with('id',$id)
                ->with('dct_cat',$dct_cat);
        } else {
            return redirect("/login");
        }
    }

    public function toogle_edit(Request $request)
    {
        $request->session()->put('toogle_edit_report', $request->toogle_edit);

        return redirect()->back();
    }
}
