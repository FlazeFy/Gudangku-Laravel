<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\Generator;
use App\Models\HistoryModel;

class HistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        $history = HistoryModel::select('*')
            ->where('created_by',$user_id)
            ->orderby('created_at', 'DESC')
            ->get();

        return view('history.index')
            ->with('history',$history);
    }

    public function hard_delete($id)
    {
        HistoryModel::destroy($id);

        return redirect()->back();
    }
}
