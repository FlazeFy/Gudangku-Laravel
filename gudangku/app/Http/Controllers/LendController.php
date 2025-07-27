<?php

namespace App\Http\Controllers;

class LendController extends Controller
{
    public function index($id)
    {
        return view('lend.index');
    }
}
