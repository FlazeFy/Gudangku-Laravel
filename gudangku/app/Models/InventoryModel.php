<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;
use DateTime;

class InventoryModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'inventory';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'inventory_name', 'inventory_category', 'inventory_desc', 'inventory_merk', 'inventory_room', 'inventory_storage', 'inventory_rack', 'inventory_price', 'inventory_image', 'inventory_unit', 'inventory_vol', 'inventory_capacity_unit', 'inventory_capacity_vol', 'is_favorite', 'is_reminder', 'created_at', 'created_by', 'updated_at', 'deleted_at'];

    public static function getInventoryPlanDestroy($days){
        $res = InventoryModel::select('inventory.id','inventory_name','deleted_at','username','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','email')
            ->join('users','users.id','=','inventory.created_by')
            ->whereDate('deleted_at', '<', Carbon::now()
            ->subDays($days))
            ->orderby('username','asc');

        return $res->get();
    }
}
