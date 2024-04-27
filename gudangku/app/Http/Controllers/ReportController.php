<?php

namespace App\Http\Controllers;

use App\Helpers\Generator;

use App\Models\ReportModel;

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
                $report = ReportModel::selectRaw('
                        report_title, report_desc, report_category, report.is_reminder, remind_at, report.created_at, 
                        count(1) as total_variety, sum(item_qty) as total_item, GROUP_CONCAT(inventory.inventory_name SEPARATOR ", ") as report_items,
                        sum(item_price * item_qty) as item_price')
                    ->leftjoin('report_item','report_item.report_id','=','report.id')
                    ->leftjoin('inventory','inventory.id','=','report_item.inventory_id')
                    ->where('report.created_by',$user_id)
                    ->whereNull('report.deleted_at')
                    ->groupby('report.id')
                    ->orderby('report.created_at','desc')
                    ->get();
                
            return view('report.index')
                ->with('report',$report);
        } else {
            return redirect("/login");
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
