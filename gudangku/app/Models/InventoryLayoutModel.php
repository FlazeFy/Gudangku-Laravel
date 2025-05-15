<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;
use DateTime;

/**
 * @OA\Schema(
 *     schema="inventory_layout",
 *     type="object",
 *     required={"id", "inventory_room", "inventory_storage", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the inventory layout"),
 *     @OA\Property(property="inventory_room", type="string", description="Room where the inventory is located"),
 *     @OA\Property(property="inventory_storage", type="string", description="Storage location of the inventory"),
 *     @OA\Property(property="storage_desc", type="string", description="Storage description"),
 *     @OA\Property(property="layout", type="string", description="Coordinate of the storage"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the inventory layout was created"),
 *     @OA\Property(property="created_by", type="string", format="uuid", description="ID of the user who created the inventory layout"),
 * )
 */
class InventoryLayoutModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'inventory_layout';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'inventory_room', 'inventory_storage', 'layout', 'storage_desc', 'created_at', 'created_by'];

    public static function getInventoryByLayout($user_id, $room){
        $res = InventoryLayoutModel::select('id','inventory_storage','layout','storage_desc')
            ->where('created_by',$user_id)
            ->where('inventory_room',$room);
        
        return $res->get();
    }

    public static function getFindInventoryByRoomStorage($user_id,$room,$storage){
        $res = InventoryLayoutModel::select('inventory_storage','layout','storage_desc','created_at')
            ->where('created_by',$user_id)
            ->where('inventory_room',$room)
            ->where('inventory_storage',$storage);
        
        return $res->first();
    }
    
    public static function getLayoutByCoor($id, $user_id, $coor){
        $res = InventoryLayoutModel::select('layout','inventory_storage')
            ->where('id',$id)
            ->where('created_by',$user_id)
            ->where('layout', 'like', '%' . $coor . '%')
            ->first();

        return $res;
    }
}
