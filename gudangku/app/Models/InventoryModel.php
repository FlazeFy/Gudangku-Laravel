<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public static function getInventoryPlanDestroy($days){
        $res = InventoryModel::select('inventory.id','inventory_name','deleted_at','username','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','email')
            ->join('users','users.id','=','inventory.created_by')
            ->whereDate('deleted_at', '<', Carbon::now()
            ->subDays($days))
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
        $avgCol = $col === 'inventory_price' ? "CAST(AVG($col) as UNSIGNED) as average_$col," : "";
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
}
