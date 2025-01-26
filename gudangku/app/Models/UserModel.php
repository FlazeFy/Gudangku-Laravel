<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\AdminModel;
use App\Models\InventoryModel;
use App\Models\ReportModel;

/**
 * @OA\Schema(
 *     schema="Users",
 *     type="object",
 *     required={"id", "username", "password", "email", "telegram_is_valid", "password", "created_at"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="telegram_user_id", type="string", description="Telegram Account ID for Bot Apps"),
 *     @OA\Property(property="telegram_is_valid", type="bool", description="Validation status of attached telegram account"),
 *     @OA\Property(property="firebase_fcm_token", type="string", description="FCM Notification Token for Mobile Apps"),
 *     @OA\Property(property="line_user_id", type="string", description="Line Account ID for Bot Apps"),
 * 
 *     @OA\Property(property="username", type="string", description="Unique Identifier for user"),
 *     @OA\Property(property="email", type="string", description="Email for Auth and Task Scheduling"),
 *     @OA\Property(property="password", type="string", description="Sanctum Hashed Password"),
 *     @OA\Property(property="phone", type="string", description="Phone number for Task Scheduling and OTP Auth"),
 *     @OA\Property(property="timezone", type="string", description="UTC timezone for Task Scheduling"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the user was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the user was updated")
 * )
 */

class UserModel extends Authenticatable
{
    use HasFactory;
    //use HasUuids;
    use HasApiTokens;
    public $incrementing = false;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'username', 'password','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','email','phone','timezone','created_at', 'updated_at'];

    public static function getSocial($id){
        $res = UserModel::select('username','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','email')
            ->where('id',$id)
            ->first();

        if($res == null){
            $res = AdminModel::select('username','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','email')
                ->where('id',$id)
                ->first();
        }

        return $res;
    }

    public static function getRandom($null){
        if($null == 0){
            $data = UserModel::inRandomOrder()->take(1)->first();
            $res = $data->id;
        } else {
            $res = null;
        }
        
        return $res;
    }

    public static function getUserById($user_id){
        $select_query = 'id,username,email,telegram_user_id,telegram_is_valid,created_at';

        $res = UserModel::selectRaw($select_query)
            ->where('id',$user_id)
            ->first();
        if($res){
            $res->role = 'user';
        }
        if(!$res){
            $res = AdminModel::selectRaw($select_query)
                ->where('id',$user_id)
                ->first();
            if($res){
                $res->role = 'admin';
            }
        }

        return $res;
    }

    public static function getAllUser($paginate){
        $res = UserModel::select('id','username','email','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','phone','timezone','created_at','updated_at')
            ->orderby('created_at','desc')
            ->paginate($paginate);

        return $res;
    }

    public static function getAvailableYear($user_id, $is_admin){
        $res_inventory = InventoryModel::selectRaw('YEAR(created_at) as year');
        if (!$is_admin) {
            $res_inventory = $res_inventory->where('created_by', $user_id);
        }
        $res_inventory = $res_inventory->groupBy('year')
            ->get();
    
        $res_report = ReportModel::selectRaw('YEAR(created_at) as year');
        if (!$is_admin) {
            $res_report = $res_report->where('created_by', $user_id);
        }
        $res_report = $res_report->groupBy('year')
            ->get();
    
        $res = $res_inventory->concat($res_report)
            ->unique('year') 
            ->sortBy('year')
            ->values(); 

        return $res;
    }
}
