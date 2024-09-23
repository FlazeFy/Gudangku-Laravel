<?php

namespace App\Http\Controllers;

use App\Helpers\Generator;
use App\Helpers\Audit;

use Illuminate\Http\Request;

class ReportDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('report.detail.index')
                ->with('id',$id);
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
