<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Report",
 *     type="object",
 *     required={"id", "report_title", "report_category", "is_reminder", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="integer", description="Primary Key"),
 *     @OA\Property(property="report_title", type="string", description="Title of the report"),
 *     @OA\Property(property="report_desc", type="string", description="Description of the report"),
 *     @OA\Property(property="report_category", type="string", description="Category of the report"),
 *     @OA\Property(property="is_reminder", type="string", description="Indicates if a reminder is set for the report"),
 * 
 *     @OA\Property(property="remind_at", type="string", format="date-time", description="Timestamp when the report remind to user"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the user created the report"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the user updated the report"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="Timestamp when the user deleted the report"),
 *     @OA\Property(property="created_by", type="string", format="uuid", description="ID of the user who created the report")
 * )
 */

class ReportModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'report';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'report_title', 'report_desc', 'report_category', 'is_reminder', 'remind_at', 'created_at', 'created_by', 'updated_at', 'deleted_at'];

    public static function getMyReport($user_id, $search,$id){
        $res = ReportModel::selectRaw('
                report.id, report_title, report_desc, report_category, report.is_reminder, remind_at, report.created_at, 
                count(1) as total_variety, CAST(COALESCE(SUM(item_qty),0) AS UNSIGNED) as total_item, GROUP_CONCAT(item_name SEPARATOR ", ") as report_items,
                CAST(SUM(item_price * item_qty) AS UNSIGNED) as item_price')
            ->leftjoin('report_item','report_item.report_id','=','report.id')
            ->leftjoin('inventory','inventory.id','=','report_item.inventory_id')
            ->where('report.created_by',$user_id)
            ->whereNull('report.deleted_at')
            ->groupby('report.id')
            ->orderby('report.created_at','desc');

        if ($search) {
            $res = $res->orWhere(function($query) use ($search, $id) {
                $query->whereRaw('LOWER(inventory_name) LIKE ?', ['%' . strtolower($search) . '%'])
                        ->orWhere('inventory_id', $id);
            });
            $res = $res->havingRaw('LOWER(report_items) LIKE ?', ['%' . strtolower($search) . '%']);
        }

        return $res->get();
    }   

    public static function getReportDetail($user_id,$id,$type){
        $res = ReportModel::selectRaw($type == 'data' ? '*' : 'id,report_title, report_desc, report_category, created_at')
            ->where('id',$id);

        if($type == 'data'){
            $res = $res->where('created_by',$user_id);
        }

        return $res->first();
    }   

    public static function getRandom($null,$user_id){
        if($null == 0){
            $res = ReportModel::inRandomOrder()->take(1)->where('created_by',$user_id)->first();
        } else {
            $res = null;
        }
        
        return $res;
    }

    public static function getLastFoundInventoryReport($user_id,$invetory_id){
        $res = ReportModel::select('report.created_at','report_title','report_category')
            ->join('report_item','report_item.report_id','=','report.id')
            ->where('report.created_by',$user_id)
            ->where('inventory_id',$invetory_id)
            ->get();

        return $res;
    }
}
