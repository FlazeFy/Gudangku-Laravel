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

    public static function getReportItem($user_id,$id){
        $res = ReportItemModel::selectRaw('*')
            ->where('created_by',$user_id)
            ->where('report_id',$id);

        return $res->get();
    }  
}
