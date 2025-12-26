<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTime;

// Helper
use App\Helpers\Generator;

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
        return InventoryLayoutModel::select('id','inventory_storage','layout','storage_desc')
            ->where('created_by',$user_id)
            ->where('inventory_room',$room)
            ->get();
    }

    public static function getInventoryByRoomStorage($user_id,$room,$storage){
        return InventoryLayoutModel::select('id','inventory_storage','layout','storage_desc','created_at')
            ->where('created_by',$user_id)
            ->where('inventory_room',$room)
            ->where('inventory_storage',$storage)
            ->first();
    }
    
    public static function getLayoutByCoor($id, $user_id, $coor){
        return InventoryLayoutModel::select('layout','inventory_storage')
            ->where('id',$id)
            ->where('created_by',$user_id)
            ->where('layout', 'like', '%' . $coor . '%')
            ->first();
    }

    public static function createInventoryLayout($inventory_room, $inventory_storage, $storage_desc, $layout, $user_id){
        return InventoryLayoutModel::create([
            'id' => Generator::getUUID(),
            'inventory_room' => $inventory_room,
            'inventory_storage' => $inventory_storage,
            'storage_desc' => $storage_desc,
            'layout' => $layout,
            'created_at' => date('Y-m-d H:i'),
            'created_by' => $user_id
        ]);
    }
    
    public static function deleteInventoryLayoutByUserId($user_id){
        return InventoryLayoutModel::where('created_by',$user_id)->delete();
    }

    public static function updateInventoryLayoutById($user_id = null, $id, $data){
        return InventoryLayoutModel::where('id', $id)->where('created_by', $user_id)->update($data);
    }

    public static function isLayoutRoomUsedByCoordinate($user_id, $inventory_room, $layout){
        return InventoryLayoutModel::where('inventory_room',$inventory_room)
            ->where('created_by', $user_id)
            ->where('layout', 'like', '%' . $layout . '%')
            ->exists();
    }
}
