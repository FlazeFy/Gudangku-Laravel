<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

// Models
use App\Models\ReportModel;
use App\Models\ReportItemModel;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Audit;

class EditController extends Controller
{
    public function index($id)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('edit.index')
                ->with('id',$id);
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
            // History
            Audit::createHistory('Create', $report->report_title, $user_id);
            $report_item = ReportItemModel::create([
                'id' => Generator::getUUID(), 
                'inventory_id' => $request->inventory_id, 
                'report_id' => $id_report, 
                'item_name' => $request->item_name, 
                'item_desc' => $request->item_desc,  
                'item_qty' => $request->item_qty, 
                'item_price' => $request->item_price ?? null, 
                'created_at' => date('Y-m-d H:i:s'), 
                'created_by' => $user_id, 
            ]);
            if($report_item){
                return redirect()->back()->with('success_mini_message', "Success create report and its item");
            } else {
                return redirect()->back()->with('failed_message', "Success create report. But failed add item to report");
            }
        } else {
            return redirect()->back()->with('failed_message', "Failed to create report");
        }
    }
}
