<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Audit;

// Models
use App\Models\HistoryModel;

// Exports
use App\Exports\HistoryExport;

use Maatwebsite\Excel\Facades\Excel;

class HistoryController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('history.index');
        } else {
            return redirect("/login");
        }
    }

    public function hard_delete($id)
    {
        $res = HistoryModel::destroy($id);

        if($res){
            return redirect()->back()->with('success_mini_message', "Success delete history");
        } else {
            return redirect()->back()->with('failed_message', "Failed delete history");
        }
    }

    public function save_as_csv(){
        $user_id = Generator::getUserId(session()->get('role_key'));

        $data = HistoryModel::select('*')
            ->where('created_by', $user_id)
            ->orderBy('created_at', 'DESC')
            ->get();

        if($data->isNotEmpty()){
            try {
                $file_name = date('l, j F Y \a\t H:i:s');
                Audit::createHistory('Print item', 'History', $user_id);

                session()->flash('success_message', 'Success generate data');
                return Excel::download(new HistoryExport($data), "$file_name-History Data.xlsx");
            } catch (\Exception $e) {
                return redirect()->back()->with('failed_message', 'Something is wrong. Please try again');
            }
        } else {
            return redirect()->back()->with('failed_message', "No Data to generated");
        }
    }
}
