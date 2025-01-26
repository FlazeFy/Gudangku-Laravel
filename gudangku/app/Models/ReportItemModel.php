<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ReportItem",
 *     type="object",
 *     required={"id", "report_id", "item_name", "item_qty", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the report item"),
 *     @OA\Property(property="inventory_id", type="string", format="uuid", description="ID of the inventory"),
 *     @OA\Property(property="report_id", type="string", format="uuid", description="ID of the report"),
 *     @OA\Property(property="item_name", type="string", description="Name of the item"),
 *     @OA\Property(property="item_desc", type="string", description="Description of the item"),
 *     @OA\Property(property="item_qty", type="integer", description="Quantity of the item"),
 *     @OA\Property(property="item_price", type="number", format="float", description="Price of the item"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the report item was created"),
 *     @OA\Property(property="created_by", type="string", format="uuid", description="ID of the user who created the report item")
 * )
 */

class ReportItemModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'report_item';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'inventory_id', 'report_id', 'item_name', 'item_desc', 'item_qty', 'item_price', 'created_at', 'created_by'];

    public static function getReportItem($user_id,$id,$type,$filter_in = null){
        $res = ReportItemModel::selectRaw($type == 'data' ? '*' : 'item_name, item_desc, item_qty, item_price')
            ->where('report_id',$id);

        if($type == 'data'){
            $res = $res->where('created_by',$user_id);
        }
        if($filter_in){
            $list_id = explode(",", $filter_in);
            $res = $res->where(function($query) use ($list_id) {
                foreach ($list_id as $dt) {
                    $query->orWhere('id', $dt);
                }
            });
        }

        return $res->get();
    }  

    public static function getReportInventoryDetailExport($user_id, $is_admin, $report_id){
        $res = ReportItemModel::selectRaw("inventory_name,inventory_category,inventory_desc,inventory_merk,inventory_color,inventory_room,inventory_storage,inventory_rack,inventory_price,inventory_image,inventory_unit,inventory_vol,inventory_capacity_unit,inventory_capacity_vol,is_favorite,is_reminder,inventory.created_at,inventory.updated_at")
            ->join('inventory','inventory.id','=','report_item.inventory_id');
        if(!$is_admin){
            $res = $res->where('inventory.created_by',$user_id);
        }
        $res = $res->where('report_item.report_id',$report_id)
            ->whereNull('deleted_at')
            ->orderBy('report_item.created_at', 'DESC');

        return $res->get();
    }
}
