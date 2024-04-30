<?php

namespace App\Http\Controllers;

use App\Helpers\Generator;
use App\Helpers\Audit;

use App\Models\ReportModel;
use App\Models\ReportItemModel;
use App\Models\DictionaryModel;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            $report = ReportModel::getMyReport($user_id);

            $dct_cat = DictionaryModel::where('dictionary_type', 'report_category')
                ->get();
                
            return view('report.index')
                ->with('report',$report)
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

        $item_count = count($request->item_name);
    
        for($i = 0; $i < $item_count; $i++){
            ReportItemModel::create([
                'id' => Generator::getUUID(), 
                'inventory_id' => null, 
                'report_id' => $id_report, 
                'item_name' => $request->item_name[$i], 
                'item_desc' => $request->item_desc[$i],  
                'item_qty' => $request->item_qty[$i], 
                'item_price' => $request->item_price[$i], 
                'created_at' => date('Y-m-d H:i:s'), 
                'created_by' => $user_id, 
            ]);
        }

        // History
        Audit::createHistory('Create', $report->report_title, $user_id);

        return redirect()->back();
    }
}
