<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Audit;

// Models
use App\Models\ReportModel;
use App\Models\ReportItemModel;
use App\Models\DictionaryModel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            $search_key = $request->query('search_key');
            $filter_category = $request->query('filter_category');
            $sorting = $request->query('sorting');

            $dct_cat = DictionaryModel::where('dictionary_type', 'report_category')
                ->get();
                
            return view('report.index')
                ->with('search_key',$search_key)
                ->with('sorting',$sorting)
                ->with('filter_category',$filter_category)
                ->with('dct_cat',$dct_cat);
        } else {
            return redirect("/login");
        }
    }

    public function create_report(Request $request)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        $id_report = Generator::getUUID();
        $report = ReportModel::create([
            'id' => $id_report, 
            'report_title' => $request->report_title,  
            'report_desc' => $request->report_desc,  
            'report_category' => $request->report_category, 
            'is_reminder' => 0, 
            'remind_at' => null, 
            'created_at' => date('Y-m-d H:i:s'), 
            'created_by' => $user_id, 
            'updated_at' => null, 
            'deleted_at' => null
        ]);

        if($report){
            $item_count = count($request->item_name);
            $success_exec = 0;
            $failed_exec = 0;
            for($i = 0; $i < $item_count; $i++){
                $res = ReportItemModel::create([
                    'id' => Generator::getUUID(), 
                    'inventory_id' => null, 
                    'report_id' => $id_report, 
                    'item_name' => $request->item_name[$i], 
                    'item_desc' => $request->item_desc[$i],  
                    'item_qty' => $request->item_qty[$i], 
                    'item_price' => $request->item_price[$i] ?? null, 
                    'created_at' => date('Y-m-d H:i:s'), 
                    'created_by' => $user_id, 
                ]);

                if($res){
                    $success_exec++;
                } else {
                    $failed_exec++;
                }
            }

            if($failed_exec == 0 && $success_exec == $item_count){
                // History
                Audit::createHistory('Create', $report->report_title, $user_id);

                return redirect()->back()->with('success_mini_message', "Success create report and its item");
            } else if($failed_exec > 0 && $success_exec > 0){
                // History
                Audit::createHistory('Create', $report->report_title, $user_id);

                return redirect()->back()->with('success_mini_message', "Success create report and some item has been added: $success_exec. About $failed_exec inventory failed to add");
            } else {
                return redirect()->back()->with('failed_message', "Failed add item to report");
            }
        } else {
            return redirect()->back()->with('failed_message', "Failed to create report");
        }
    }
}
