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

    public static function getLendActive($user_id){
        return LendModel::select('id','lend_qr_url','qr_period','lend_desc')
            ->where('created_by',$user_id)
            ->where('is_finished',0)
            ->first();
    }

    public static function getLendByUserId($user_id){
        return LendModel::where('created_by',$user_id)
            ->where('is_finished',0)
            ->first();
    }

    public static function updateLendByUserId($data,$user_id){
        return LendModel::where('created_by',$user_id)->update($data);
    }
}
