<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

// Helpers
use App\Helpers\Generator;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            $search_key = $request->query('search_key');
            $filter_category = $request->query('filter_category');
            $sorting = $request->query('sorting');

            return view('report.index')
                ->with('search_key',$search_key)
                ->with('sorting',$sorting)
                ->with('filter_category',$filter_category);
        } else {
            return redirect("/login");
        }
    }
}
