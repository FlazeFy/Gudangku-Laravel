<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\Generator;
use App\Helpers\Audit;

use App\Models\HistoryModel;

use App\Exports\HistoryExport;

use Maatwebsite\Excel\Facades\Excel;

class HistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            $history = HistoryModel::select('*')
                ->where('created_by',$user_id)
                ->orderby('created_at', 'DESC')
                ->get();

            return view('history.index')
                ->with('history',$history);
        } else {
            return redirect("/login");
        }
    }

    public function hard_delete($id)
    {
        HistoryModel::destroy($id);

        return redirect()->back();
    }

    public function save_as_csv(){
        $data = HistoryModel::all();
        $file_name = date('l, j F Y \a\t H:i:s');

        Audit::createHistory('Print item', 'History');

        return Excel::download(new HistoryExport($data), "$file_name-History Data.xlsx");
    }
}
