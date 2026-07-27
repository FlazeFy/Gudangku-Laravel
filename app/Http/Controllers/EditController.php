<?php

namespace App\Http\Controllers;

// Helpers
use App\Helpers\Generator;

class EditController extends Controller
{
    public function index($id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        return $user_id !== null ? view('edit.index')->with('id',$id) : redirect("/login");
    }
}
