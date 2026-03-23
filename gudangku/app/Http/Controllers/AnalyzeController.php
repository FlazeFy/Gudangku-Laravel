<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class AnalyzeController extends Controller
{
    public function index_inventory(Request $request, $id) {
        $user_id = $request->user()->id;

        return $user_id != null ? view('analyze.index')->with('type','inventory')->with('id',$id) : redirect('/login');
    }
}
