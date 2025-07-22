<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Audit;

// Models
use App\Models\ReportModel;
use App\Models\ReportItemModel;
use App\Models\DictionaryModel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            $search_key = $request->query('search_key');
            $filter_category = $request->query('filter_category');
            $sorting = $request->query('sorting');

            $dct_cat = DictionaryModel::where('dictionary_type', 'report_category')
                ->get();
                
            return view('report.index')
                ->with('search_key',$search_key)
                ->with('sorting',$sorting)
                ->with('filter_category',$filter_category)
                ->with('dct_cat',$dct_cat);
        } else {
            return redirect("/login");
        }
    }
}
