<?php

namespace App\Http\Controllers;
use Maatwebsite\Excel\Facades\Excel;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Audit;

// Models
use App\Models\AdminModel;
use App\Models\ScheduleMarkModel;

// Exports
use App\Exports\HistoryExport;

class ReminderController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));
        $check = AdminModel::find($user_id);

        if($check != null){
            return view('reminder.index');
        } else {
            return redirect("/login");
        }
    }

    public function save_as_csv(){
        $user_id = Generator::getUserId(session()->get('role_key'));
        $check_admin = AdminModel::find($user_id);

        if($check_admin){
            $res = ScheduleMarkModel::getAllReminderMark(false);

            if($res->isNotEmpty()){
                try {
                    $file_name = date('l, j F Y \a\t H:i:s');
                    Audit::createHistory('Print item', 'Schedule Mark', $user_id);

                    session()->flash('success_message', 'Success generate data');
                    return Excel::download(new HistoryExport($res), "$file_name-Schedule Mark Data.xlsx");
                } catch (\Exception $e) {
                    return redirect()->back()->with('failed_message', 'Something is wrong. Please try again');
                }
            } else {
                return redirect()->back()->with('failed_message', "No Data to generated");
            }
        } else {
            return redirect()->back()->with('failed_message', "only admin can use this request");
        }
    }
}
