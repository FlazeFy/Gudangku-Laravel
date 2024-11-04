<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalyzeController extends Controller
{

    public function index_inventory(Request $request, $id){
        $user_id = $request->user()->id;

        if($user_id){
            return view('analyze.index')
                ->with('type','inventory')
                ->with('id',$id);
        } else {
            return redirect("/login");
        }
    }
}
