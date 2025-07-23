<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use DateTime;

/**
 * @OA\Schema(
 *     schema="Inventory",
 *     type="object",
 *     required={"id", "inventory_name", "inventory_category", "inventory_room", "inventory_price", "inventory_unit", "is_favorite", "is_reminder", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the inventory"),
 *     @OA\Property(property="inventory_name", type="string", description="Name of the inventory"),
 *     @OA\Property(property="inventory_category", type="string", description="Category of the inventory"),
 *     @OA\Property(property="inventory_desc", type="string", description="Description of the inventory"),
 *     @OA\Property(property="inventory_merk", type="string", description="Merk or brand of the inventory"),
 *     @OA\Property(property="inventory_color", type="string", description="Color of the inventory"),
 *     @OA\Property(property="inventory_room", type="string", description="Room where the inventory is located"),
 *     @OA\Property(property="inventory_storage", type="string", description="Storage location of the inventory"),
 *     @OA\Property(property="inventory_rack", type="string", description="Rack or location within the storage"),
 *     @OA\Property(property="inventory_price", type="number", format="float", description="Price of the inventory"),
 *     @OA\Property(property="inventory_image", type="string", format="url", description="Firebase Storage URL to the image of the inventory"),
 *     @OA\Property(property="inventory_unit", type="string", description="Unit of measurement for the inventory"),
 *     @OA\Property(property="inventory_vol", type="number", format="float", description="Volume of the inventory"),
 *     @OA\Property(property="inventory_capacity_unit", type="string", description="Unit of capacity for the inventory"),
 *     @OA\Property(property="inventory_capacity_vol", type="number", format="float", description="Capacity volume of the inventory"),
 *     @OA\Property(property="is_favorite", type="boolean", description="Indicates if the inventory is marked as a favorite"),
 *     @OA\Property(property="is_reminder", type="boolean", description="Indicates if a reminder is set for the inventory"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the inventory was created"),
 *     @OA\Property(property="created_by", type="string", format="uuid", description="ID of the user who created the inventory"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the inventory was updated"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="Timestamp when the inventory was deleted")
 * )
 */
class InventoryModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'inventory';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'inventory_name', 'inventory_category', 'inventory_desc', 'inventory_merk', 'inventory_color', 'inventory_room', 'inventory_storage', 'inventory_rack', 'inventory_price', 'inventory_image', 'inventory_unit', 'inventory_vol', 'inventory_capacity_unit', 'inventory_capacity_vol', 'is_favorite', 'is_reminder', 'created_at', 'created_by', 'updated_at', 'deleted_at'];

    public static function getInventoryNameById($id){
        $res = InventoryModel::select('inventory_name')
                ->where('id',$id);

        return $res->first();
    }

    public static function getInventoryPlanDestroy($days){
        $res = InventoryModel::select('inventory.id','inventory_name','deleted_at','username','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id')
            ->join('users','users.id','=','inventory.created_by')
            ->whereDate('deleted_at', '<', Carbon::now()->subDays($days))
            ->orderby('username','asc');

        return $res->get();
    }

    public static function getInventoryByRoom($room,$user_id){
        $res = InventoryModel::select('inventory_name','inventory_desc','inventory_vol','inventory_unit', 'inventory_category', 'inventory_price','inventory_storage')
            ->where('created_by',$user_id)
            ->where('inventory_room',$room)
            ->orderby('inventory_storage','ASC')
            ->orderby('inventory_name','ASC');

        return $res->get();
    }

    public static function getInventoryByStorage($storage,$room,$user_id){
        $res = InventoryModel::select('id','inventory_name','inventory_vol','inventory_unit', 'inventory_category', 'inventory_price')
            ->where('created_by',$user_id)
            ->where('inventory_storage',$storage)
            ->where('inventory_room',$room)
            ->orderby('inventory_name','ASC');

        return $res->get();
    }

    public static function getInventoryStatsByStorage($storage,$room,$user_id){
        $res = InventoryModel::selectRaw('inventory_category as context, COUNT(1) as total')
            ->where('created_by',$user_id)
            ->where('inventory_storage',$storage)
            ->where('inventory_room',$room)
            ->groupby('inventory_category')
            ->get();

        return $res->get();
    }

    public static function getInventoryDetail($id,$user_id){
        if (strpos($id, ',') == null) {
            $res = InventoryModel::select('id', 'inventory_name', 'inventory_category', 'inventory_desc', 'inventory_merk', 'inventory_color', 'inventory_room', 'inventory_storage', 'inventory_rack', 'inventory_price', 'inventory_image', 'inventory_unit', 'inventory_vol', 'inventory_capacity_unit', 'inventory_capacity_vol', 'is_favorite', 'is_reminder', 'created_at', 'updated_at')
                ->where('created_by',$user_id)
                ->where('id',$id);

            return $res->first();
        } else {
            $ids = explode(',',$id);
            $res = InventoryModel::select('id', 'inventory_name', 'inventory_category', 'inventory_desc', 'inventory_merk', 'inventory_color', 'inventory_room', 'inventory_storage', 'inventory_rack', 'inventory_price', 'inventory_image', 'inventory_unit', 'inventory_vol', 'inventory_capacity_unit', 'inventory_capacity_vol', 'is_favorite', 'is_reminder', 'created_at', 'updated_at')
                ->where('created_by',$user_id)
                ->whereIn('id',$ids);

            return $res->get();
        }
    }

    public static function getCheckInventoryAvaiability($inventory_name, $user_id, $inventory_id){
        $check = InventoryModel::selectRaw('1')
            ->where('inventory_name',$inventory_name)
            ->where('created_by',$user_id);

        if($inventory_id != null){
            $check->whereNot('id',$inventory_id);
        }

        $check = $check->first();

        if($check){
            return false;
        } else {
            return true;
        }
    }

    public static function getRandom($null,$user_id){
        if($null == 0){
            $res = InventoryModel::inRandomOrder()->take(1)->where('created_by',$user_id)->first();
        } else {
            $res = null;
        }
        
        return $res;
    }

    public static function getAnalyzeMost($user_id, $col){
        $avgCol = $col === 'inventory_price' ? "CAST(AVG($col) as UNSIGNED) as average_$col, CAST(SUM($col) as UNSIGNED) as sub_total, " : "";
        $maxCol = $col === 'inventory_price' ? "CAST(MAX($col) as UNSIGNED)" : "MAX($col)";
        $minCol = $col === 'inventory_price' ? "CAST(MIN($col) as UNSIGNED)" : "MIN($col)";
        $selectColumns = trim("$avgCol $maxCol as max_$col, $minCol as min_$col");

        $res = InventoryModel::selectRaw($selectColumns)
            ->where('created_by', $user_id)
            ->first();

        return $res;
    }

    public static function getAnalyzeContext($user_id, $col, $target){
        $res = InventoryModel::selectRaw("COUNT(1) as total, CAST(AVG(inventory_price)as UNSIGNED) as average_price")
            ->where($col,$target)
            ->where('created_by', $user_id)
            ->first();

        return $res;
    }

    public static function getAnalyzeHistory($user_id,$id){
        $res = ReportModel::selectRaw("COUNT(1) as total, report_category")
            ->join('report_item','report_item.report_id','=','report.id')
            ->where('report_item.created_by',$user_id)
            ->where('report_item.inventory_id',$id)
            ->groupby('report_category')
            ->orderby('total','desc')
            ->get();

        return count($res) > 0 ? $res : null;
    }

    public static function getContextTotalStats($context,$type,$user_id){
        function get_inventory_stats_view($type){
            if($type == "price"){
                return "CAST(SUM(inventory_price) as UNSIGNED)";
            } else if($type == "item") {
                return "COUNT(1)";
            }
        }

        $res = InventoryModel::selectRaw("$context as context, ".get_inventory_stats_view($type)." as total");
        if($user_id){
            $res = $res->where('created_by', $user_id);
        }
        if($context == "inventory_merk"){
            $res = $res->whereNotNull("inventory_merk");
        }
        $res = $res->groupby($context)
            ->orderby('total','desc')
            ->limit(7)
            ->get();
        
        return count($res) > 0 ? $res : null;
    }

    public static function getMostExpensiveInventoryPerContext($user_id,$context){
        $res = InventoryModel::selectRaw("CONCAT($context,' (',inventory_name,')') as context, inventory_price");
        if($user_id){
            $res = $res->where('created_by', $user_id);
        }
        if($context == 'inventory_storage'){
            $res = $res->whereNotNull('inventory_storage');
        }
        $res = $res->groupby($context)
            ->orderby('inventory_price','desc')
            ->limit(7)
            ->get();

        return count($res) > 0 ? $res : null;
    }

    public static function getInventoryByNameSearch($search){
        $search = explode(',', $search);
        $query = InventoryModel::select('id','inventory_name','inventory_desc','inventory_vol','inventory_unit', 'inventory_category', 'inventory_price','inventory_storage','inventory_room');
        foreach ($search as $dt) {
            $dt = trim($dt);
            $query->orWhere('inventory_name', 'like', "%$dt%");
        }
        $res = $query->get();

        $final_res = [];
        foreach ($res as $dt) {
            $status = 'similar'; 
            foreach ($search as $sc) {
                $sc = trim($sc);
                if (strcasecmp($dt->inventory_name, $sc) === 0) {
                    $status = 'matched';
                    break;
                }
            }
    
            $final_res[] = [
                'id' => $dt->id,
                'inventory_name' => $dt->inventory_name,
                'inventory_desc' => $dt->inventory_desc,
                'inventory_vol' => $dt->inventory_vol,
                'inventory_unit' => $dt->inventory_unit,
                'inventory_category' => $dt->inventory_category,
                'inventory_price' => $dt->inventory_price,
                'inventory_room' => $dt->inventory_room,
                'inventory_storage' => $dt->inventory_storage,
                'status' => $status 
            ];
        }

        usort($final_res, function($a, $b) {
            return ($a['status'] === 'matched' && $b['status'] !== 'matched') ? -1 : 1;
        });        

        return count($final_res) > 0 ? $final_res : null;
    }

    public static function getAnalyzeActivityInReport($user_id,$id){
        $start_date = Carbon::now()->subDays(31)->startOfDay();
        $end_date = Carbon::now()->startOfDay();

        $date_res = [];
        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $date_res[] = [
                'total' => 0,
                'context' => $date->format('Y-m-d')
            ];
        }
        
        $query_res = InventoryModel::selectRaw("COUNT(1) as total, DATE(report.created_at) as context")
            ->join('report_item','report_item.inventory_id','=','inventory.id')
            ->join('report','report.id','=','report_item.report_id')
            ->where('report_item.created_by',$user_id)
            ->where('report_item.inventory_id',$id)
            ->whereBetween('report.created_at', [Carbon::now()->subDays(31), Carbon::now()])
            ->groupbyRaw('DATE(report.created_at)')
            ->orderby('total','desc')
            ->get();

        if(count($query_res) > 0){
            $final_res = [];
            foreach ($date_res as $dt_date) {
                $found = false;
                $day_name = (new DateTime($dt_date['context']))->format('D');

                foreach ($query_res as $dt_query) {
                    if($dt_date['context'] == $dt_query->context){
                        $found = true;
                        $final_res[] = [
                            'total' => $dt_query->total,
                            'context' => $dt_date['context'],
                            'day' => $day_name
                        ];
                        break;
                    }
                }

                if(!$found){
                    $final_res[] = [
                        'total' => $dt_date['total'],
                        'context' => $dt_date['context'],
                        'day' => $day_name
                    ];
                }
            }
        } else {
            $final_res = $date_res;
        }

        return $final_res;
    }

    public static function getTotalInventoryCreatedPerMonth($user_id, $year, $is_admin){
        $res = InventoryModel::selectRaw("COUNT(1) as total, MONTH(created_at) as context");
            if(!$is_admin){
                $res = $res->where('created_by', $user_id);
            }
        $res = $res->whereRaw("YEAR(created_at) = '$year'")
            ->groupByRaw('MONTH(created_at)')
            ->get();

        return $res;
    }

    public static function getTotalInventory($user_id,$type){
        $res = InventoryModel::selectRaw('COUNT(1) AS total');

        if($user_id){
            $res = $res->where('created_by', $user_id);
        }

        if($type == "favorite"){
            $res = $res->where('is_favorite','1');
        } else if($type == "low"){
            $res = $res->where('inventory_capacity_unit','percentage')
                ->where('inventory_capacity_vol','<=',30);
        }
       
        return $res->first();
    }

    public static function getLastAddedInventory($user_id){
        $res = InventoryModel::select('inventory_name')
            ->whereNull('deleted_at');

        if($user_id){
            $res = $res->where('created_by', $user_id);
        }
            
        $res = $res->orderBy('created_at','DESC')
            ->first();

        return $res;
    }

    public static function getMostCategoryInventory($user_id){
        $res = InventoryModel::selectRaw('inventory_category as context, COUNT(1) as total')
            ->whereNull('deleted_at');

        if($user_id){
            $res = $res->where('created_by', $user_id);
        }

        $res = $res->groupBy('inventory_category')
            ->orderBy('total','DESC')
            ->first();

        return $res;
    }

    public static function getHighestPriceInventory($user_id){
        $res = InventoryModel::select('inventory_name', 'inventory_price')
            ->whereNull('deleted_at');

        if($user_id){
            $res = $res->where('created_by', $user_id);
        }

        $res = $res->orderBy('inventory_price','DESC')
            ->first();

        return $res;
    }

    public static function getInventoryExport($user_id, $is_admin, $type){
        $extra_col = "";
        if($type == "deleted"){
            $extra_col = ",deleted_at";
        }
        $res = InventoryModel::selectRaw("inventory_name,inventory_category,inventory_desc,inventory_merk,inventory_color,inventory_room,inventory_storage,inventory_rack,inventory_price,inventory_image,inventory_unit,inventory_vol,inventory_capacity_unit,inventory_capacity_vol,is_favorite,is_reminder,created_at,updated_at$extra_col");
        if(!$is_admin){
            $res = $res->where('created_by',$user_id);
        }
        if($type == "deleted"){
            $res = $res->whereNotNull('deleted_at')
                ->orderBy('deleted_at', 'DESC')
                ->orderBy('created_at', 'DESC');
        } else if($type == "active"){
            $res = $res->whereNull('deleted_at')
                ->orderBy('created_at', 'DESC');
        }

        return $res->get();
    }

    public static function getAllLowCapacity(){
        $res = InventoryModel::selectRaw("
            GROUP_CONCAT(CONCAT(inventory_name, ' (', inventory_capacity_vol, '%)') SEPARATOR ', ') as list_inventory,
            username, telegram_user_id, telegram_is_valid, line_user_id, firebase_fcm_token")
            ->join('users','users.id','=','inventory.created_by')
            ->where('inventory_capacity_unit','percentage')
            ->where('inventory_capacity_vol','<=',30)
            ->groupby('inventory.created_by')
            ->get();

        return count($res) > 0 ? $res : null;
    } 

    public static function getAllDashboard(){
        $res = DB::table('inventory as i')
            ->select(
                'username','telegram_user_id','telegram_is_valid','line_user_id',
                DB::raw('COUNT(i.id) as total_inventory'),
                DB::raw('SUM(CASE WHEN i.is_favorite = 1 THEN 1 ELSE 0 END) as total_favorite'),
                DB::raw("SUM(CASE WHEN i.inventory_capacity_unit = 'percentage' AND i.inventory_capacity_vol <= 30 THEN 1 ELSE 0 END) as total_low_capacity"),
                DB::raw('MAX(i.inventory_price) as max_price'),
                // SubQuery : The Highest Price
                DB::raw('(
                    SELECT inventory_name 
                    FROM inventory 
                    WHERE created_by = i.created_by 
                    ORDER BY inventory_price DESC 
                    LIMIT 1
                ) as max_price_inventory_name'),
                // SubQuery : Most Category
                DB::raw('(
                    SELECT inventory_category 
                    FROM inventory 
                    WHERE created_by = i.created_by 
                    GROUP BY inventory_category 
                    ORDER BY COUNT(*) DESC 
                    LIMIT 1
                ) as most_category'),
                // SubQuery : Total Of Most Category
                DB::raw('(
                    SELECT COUNT(*) 
                    FROM inventory 
                    WHERE created_by = i.created_by 
                    AND inventory_category = (
                        SELECT inventory_category 
                        FROM inventory 
                        WHERE created_by = i.created_by 
                        GROUP BY inventory_category 
                        ORDER BY COUNT(*) DESC 
                        LIMIT 1
                    )
                ) as most_category_count'),
                // SubQuery : Last Created
                DB::raw('(
                    SELECT inventory_name 
                    FROM inventory 
                    WHERE created_by = i.created_by 
                    ORDER BY created_at DESC 
                    LIMIT 1
                ) as last_created_inventory_name')
            )
            ->join('users as u', 'u.id', '=', 'i.created_by')
            ->groupBy('i.created_by')
            ->get();

        return count($res) > 0 ? $res : null;
    }

    public static function deleteInventoryById($id, $user_id){
        $res = InventoryModel::where('id',$id)
            ->where('created_by',$user_id)
            ->whereNotNull('deleted_at')
            ->delete();

        return $res;
    } 
}
