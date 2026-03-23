<?php

namespace App\Http\Controllers;

// Helpers
use App\Helpers\Generator;

class AddReportController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        return $user_id != null ? view('add_report.index') : redirect('/login');
    }
}
