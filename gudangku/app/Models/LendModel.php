<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="Lend",
 *     type="object",
 *     required={"id", "lend_qr_url", "qr_period", "lend_desc", "lend_status", "created_at", "created_by", "is_finished"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the lend"),
 *     @OA\Property(property="lend_qr_url", type="string", description="QR Code uploaded file url"),
 *     @OA\Property(property="qr_period", type="number", format="float", description="Period of qr to expired (in hour)"),
 *     @OA\Property(property="lend_desc", type="string", description="Description of the lending"),
 *     @OA\Property(property="lend_status", type="string", description="Status of the lend"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the lend was created"),
 *     @OA\Property(property="created_by", type="string", format="uuid", description="ID of the user who created the lend"),
 *     @OA\Property(property="is_finished", type="boolean", description="Finished status of lending")
 * )
 */
class LendModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'lend';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'lend_qr_url', 'qr_period', 'lend_desc', 'lend_status', 'created_at', 'created_by', 'is_finished'];

    public static function createLend($lend_qr_url,$qr_period,$lend_desc,$lend_status,$user_id){
        return LendModel::create([
            'id' => Generator::getUUID(), 
            'lend_qr_url' => $lend_qr_url, 
            'qr_period' => $qr_period, 
            'lend_desc' => $lend_desc, 
            'lend_status' => $lend_status, 
            'created_at' => date('Y-m-d H:i:s'), 
            'created_by' => $user_id, 
            'is_finished' => 0
        ]);
    }

    public static function getAllLend($user_id,$paginate){
        return LendModel::select('id','lend_qr_url','qr_period','lend_desc','lend_status','created_at','is_finished')
            ->where('created_by',$user_id)
            ->orderby('created_at','desc')
            ->paginate($paginate);
    }

    public static function getLendActive($user_id){
        return LendModel::select('id','lend_qr_url','qr_period','lend_desc','created_at')
            ->where('created_by',$user_id)
            ->where('is_finished',0)
            ->whereNot('lend_status','expired')
            ->first();
    }

    public static function getLendOwnerById($lend_id){
        return LendModel::select('users.id','username')
            ->join('users','users.id','=','lend.created_by')
            ->first();
    }

    public static function updateLendByUserId($data,$user_id,$id){
        $query = LendModel::where('id', $id);

        if (!is_null($user_id)) {
            $query->where('created_by', $user_id);
        }

        return $query->update($data);
    }

    public static function getAllLendInventory($lend_id,$paginate){
        return LendModel::select('inventory.id','inventory_name','inventory_category','inventory_desc','inventory_merk','inventory_room','inventory_storage','inventory_rack','inventory_image',
            'inventory_unit','inventory_vol','inventory_color','inventory.created_at')
            ->join('inventory','inventory.created_by','=','lend.created_by')
            ->where('lend.id',$lend_id)
            ->where('lend.lend_status','open')
            ->orderby('inventory.updated_at','desc')
            ->orderby('inventory.created_at','desc')
            ->paginate($paginate);
    }
}
