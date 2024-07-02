<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'report';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'report_title', 'report_desc', 'report_category', 'is_reminder', 'remind_at', 'created_at', 'created_by', 'updated_at', 'deleted_at'];

    public static function getMyReport($user_id){
        $res = ReportModel::selectRaw('
                report.id, report_title, report_desc, report_category, report.is_reminder, remind_at, report.created_at, 
                count(1) as total_variety, CAST(SUM(item_qty) AS UNSIGNED) as total_item, GROUP_CONCAT(item_name SEPARATOR ", ") as report_items,
                CAST(SUM(item_price * item_qty) AS UNSIGNED) as item_price')
            ->leftjoin('report_item','report_item.report_id','=','report.id')
            ->leftjoin('inventory','inventory.id','=','report_item.inventory_id')
            ->where('report.created_by',$user_id)
            ->whereNull('report.deleted_at')
            ->groupby('report.id')
            ->orderby('report.created_at','desc');

        return $res->get();
    }   
}
