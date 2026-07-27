<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class AnalyzeController extends Controller
{
    public function index_inventory(Request $request, $id) {
        $user = $request->user();

        return $user !== null ? view('analyze.index')->with('type','inventory')->with('id',$user->id) : redirect('/login');
    }
}
