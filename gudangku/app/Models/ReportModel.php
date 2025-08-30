<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

// Helpers
use App\Helpers\Generator;

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
 *     @OA\Property(property="report_image", type="string", description="Images of the report"),
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
    protected $fillable = ['id', 'report_title', 'report_desc', 'report_category', 'report_image', 'is_reminder', 'remind_at', 'created_at', 'created_by', 'updated_at', 'deleted_at'];
    protected $casts = [
        'report_image' => 'array',
    ];

    public static function getReportPlanDestroy($days){
        $res = ReportModel::select('report.id','report_title','deleted_at','username','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','email')
            ->join('users','users.id','=','report.created_by')
            ->whereDate('deleted_at', '<', Carbon::now()
            ->subDays($days))
            ->orderby('username','asc')
            ->get();

        return count($res) > 0 ? $res : null;
    }

    public static function getMyReport($user_id, $search_item, $search_report_title, $id, $filter_category){
        $extra = "";
        if(!$user_id){
            $extra = ", username";
        }
        $res = ReportModel::selectRaw('
                report.id, report_title, report_desc, report_category, report.is_reminder, remind_at, report.created_at, 
                count(1) as total_variety, CAST(COALESCE(SUM(item_qty),0) AS UNSIGNED) as total_item, GROUP_CONCAT(item_name SEPARATOR ", ") as report_items,
                CAST(SUM(item_price * item_qty) AS UNSIGNED) as item_price'.$extra)
            ->leftjoin('report_item','report_item.report_id','=','report.id')
            ->leftjoin('inventory','inventory.id','=','report_item.inventory_id');
            if($user_id){
                $res->where('report.created_by',$user_id);
            } else {
                $res->join('users','users.id','=','report.created_by');
            }
        $res = $res->whereNull('report.deleted_at')
            ->groupby('report.id')
            ->orderby('report.created_at','desc');

        // Search by inventory name
        if ($search_item) {
            $res = $res->orWhere(function($query) use ($search_item, $id) {
                $query->whereRaw('LOWER(inventory_name) LIKE ?', ['%' . strtolower($search_item) . '%'])
                        ->orWhere('inventory_id', $id);
            });
            $res = $res->havingRaw('LOWER(report_items) LIKE ?', ['%' . strtolower($search_item) . '%']);
        }
        // Search by report title
        if ($search_report_title) {
            $res = $res->whereRaw('LOWER(report_title) LIKE ?', ['%' . strtolower($search_report_title) . '%']);
        }

        // Filtering by category
        if($filter_category){
            $res->where('report_category',$filter_category);
        }     

        return $res->get();
    }   

    public static function getReportDetail($user_id = null,$id,$type){
        $res = ReportModel::selectRaw($type == 'data' ? '*' : 'id,report_title, report_desc, report_category, report_image, created_at')
            ->where('id',$id);

        if($type == 'data' && $user_id != null){
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

    public static function getLastFoundInventoryReport($user_id,$inventory_id){
        return ReportModel::select('report.created_at','report_title','report_category')
            ->join('report_item','report_item.report_id','=','report.id')
            ->where('report.created_by',$user_id)
            ->where('inventory_id',$inventory_id)
            ->get();
    }

    public static function deleteReportById($user_id = null,$report_id){
        $rows = ReportModel::where('id', $report_id);
        
        if($user_id){
            $rows = $rows->where('created_by', $user_id);
        }

        return $rows->delete();
    }

    public static function getInventoryMonthlyInReport($user_id, $inventory_id, $year = null) {
        if ($year === null) {
            $year = date('Y');
        }        
        $res = ReportModel::selectRaw('MONTH(report.created_at) as context, CAST(COUNT(1) AS UNSIGNED) as total')
            ->join('report_item','report_item.report_id','=','report.id')
            ->where('report.created_by',$user_id)
            ->where('inventory_id',$inventory_id)
            ->whereRaw("YEAR(report.created_at) = '$year'")
            ->groupByRaw('MONTH(report.created_at)')
            ->get();

        $res_final = [];
        for ($i=1; $i <= 12; $i++) { 
            $total = 0;
            foreach ($res as $idx => $val) {
                if($i == $val->context){
                    $total = $val->total;
                    break;
                }
            }
            array_push($res_final, [
                'context' => Generator::generateMonthName($i,'short'),
                'total' => $total,
            ]);
        }

        return $res_final;
    }

    public static function getTotalReportCreatedOrSpendingPerMonth($user_id = null, $year, $is_admin, $type){
        if($type == "created"){
            $select_query = "COUNT(DISTINCT report.id) as total_report, CAST(SUM(item_qty) AS UNSIGNED) as total_item, MONTH(report.created_at) as context";
        } else if($type == "spending"){
            $select_query = "CAST(SUM(item_price) AS UNSIGNED) as total_price, CAST(SUM(item_qty) AS UNSIGNED) as total_item, MONTH(report.created_at) as context";
        }

        $res = ReportModel::selectRaw($select_query)
            ->join('report_item','report_item.report_id','=','report.id');
        if($user_id){
            $res = $res->where('report.created_by', $user_id);
        }
        if($type == "spending") {
            $res = $res->where('report_category','Shopping Cart');
        }
        $res = $res->whereRaw("YEAR(report.created_at) = '$year'")
            ->groupByRaw('MONTH(report.created_at)')
            ->get();

        return $res;
    }

    public static function getTotalReportUsedPerMonth($user_id, $year, $is_admin){
        $res = ReportModel::selectRaw("
                CAST(SUM(CASE WHEN report_category = 'Checkout' THEN 1 ELSE 0 END) AS UNSIGNED) as total_checkout,
                CAST(SUM(CASE WHEN report_category = 'Wash List' THEN 1 ELSE 0 END) AS UNSIGNED) as total_washlist,
                MONTH(report.created_at) as context
            ")
            ->join('report_item', 'report_item.report_id', '=', 'report.id');
            
        if(!$is_admin){
            $res = $res->where('report.created_by', $user_id);
        }

        return $res->whereIn('report_category', ['Checkout', 'Wash List'])
            ->whereRaw("YEAR(report.created_at) = '$year'")
            ->groupByRaw('MONTH(report.created_at)')
            ->get();
    }

    public static function createReport($report_title, $report_desc, $report_category, $report_image, $is_reminder, $remind_at, $user_id, $created_at) {
        $created_at = $created_at ?? date('Y-m-d H:i:s');

        return ReportModel::create([
            'id' => Generator::getUUID(), 
            'report_title' => $report_title, 
            'report_desc' => $report_desc, 
            'report_category' => $report_category,  
            'report_image' => $report_image,
            'is_reminder' => $is_reminder,  
            'remind_at' => $remind_at,  
            'created_at' => $created_at, 
            'created_by' => $user_id, 
            'updated_at' => null, 
            'deleted_at' => null
        ]);
    }

    public static function deleteReportByUserId($user_id){
        return ReportModel::where('created_by',$user_id)->delete();
    }
}
