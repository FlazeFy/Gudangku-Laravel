<?php

namespace App\Http\Controllers;

class LendController extends Controller
{
    public function index($lend_id)
    {
        return view('lend.index')->with('lend_id',$lend_id);
    }
}
