<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

// Models
use App\Models\InventoryModel;
use App\Models\DictionaryModel;
use App\Models\ReminderModel;
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

    public function update(Request $request, $id)
    {
        $ctx = 'Update item';
        $user_id = Generator::getUserId(session()->get('role_key'));

        // Nullable
        $inventory_capacity_unit = $request->inventory_capacity_unit;
        $inventory_capacity_vol = $request->inventory_capacity_vol;

        if($inventory_capacity_unit == '-'){
            $inventory_capacity_unit = null;
            $inventory_capacity_vol = null;
        }

        $inventory_name = $request->inventory_name;
        $check = InventoryModel::getCheckInventoryAvaiability($inventory_name,$user_id,$id);

        if($check){
            $data = [
                'inventory_name' => $inventory_name, 
                'inventory_category' => $request->inventory_category, 
                'inventory_desc' => $request->inventory_desc, 
                'inventory_merk' => $request->inventory_merk, 
                'inventory_room' => $request->inventory_room, 
                'inventory_storage' => $request->inventory_storage, 
                'inventory_rack' => $request->inventory_rack, 
                'inventory_price' => $request->inventory_price, 
                'inventory_unit' => $request->inventory_unit, 
                'inventory_vol' => $request->inventory_vol, 
                'inventory_capacity_unit' => $inventory_capacity_unit, 
                'inventory_capacity_vol' => $inventory_capacity_vol, 
                'created_at' => $request->created_at_edit,
                'updated_at' => date("Y-m-d H:i:s")
            ];

            $res = InventoryModel::where('id',$id)->update($data);

            if($res){
                // History
                Audit::createHistory($ctx, $request->inventory_name, $user_id);

                return redirect()->back()->with('success_message', "Inventory : $inventory_name successfully updated");
            } else {
                return redirect()->back()->with('failed_message', "Inventory : $inventory_name failed to create");
            }
        } else {
            return redirect()->back()->with('failed_message', "Inventory : $inventory_name failed to update. Name has been used");
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
