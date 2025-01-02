<?php

namespace App\Http\Controllers;
use Maatwebsite\Excel\Facades\Excel;

// Models
use App\Models\AdminModel;
use App\Models\ErrorModel;

// Helpers
use App\Helpers\Audit;
use App\Helpers\Generator;

// Export
use App\Exports\ErrorExport;

class ErrorController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));
        $check = AdminModel::find($user_id);

        if($check != null){
            return view('error.index');
        } else {
            return redirect("/login");
        }
    }

    public function save_as_csv(){
        $user_id = Generator::getUserId(session()->get('role_key'));
        $check_admin = AdminModel::find($user_id);

        if($check_admin){
            $res = ErrorModel::getAllError(false);

            if($res->isNotEmpty()){
                try {
                    $file_name = date('l, j F Y \a\t H:i:s');
                    Audit::createHistory('Print item', 'Error History', $user_id);

                    session()->flash('success_message', 'Success generate data');
                    return Excel::download(new ErrorExport($res), "$file_name-Error History Data.xlsx");
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
