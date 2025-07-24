<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

// Other Models
use App\Models\ErrorModel;
use App\Models\InventoryModel;
use App\Models\ReportModel;
use App\Models\UserModel;

/**
 * @OA\Schema(
 *     schema="Admin",
 *     type="object",
 *     required={"id", "username", "password", "email", "telegram_is_valid", "password", "created_at"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="telegram_user_id", type="string", description="Telegram Account ID for Bot Apps"),
 *     @OA\Property(property="telegram_is_valid", type="bool", description="Validation status of attached telegram account"),
 *     @OA\Property(property="firebase_fcm_token", type="string", description="FCM Notification Token for Mobile Apps"),
 *     @OA\Property(property="line_user_id", type="string", description="Line Account ID for Bot Apps"),
 * 
 *     @OA\Property(property="username", type="string", description="Unique Identifier for admin"),
 *     @OA\Property(property="email", type="string", description="Email for Auth and Task Scheduling"),
 *     @OA\Property(property="password", type="string", description="Sanctum Hashed Password"),
 *     @OA\Property(property="timezone", type="string", description="UTC timezone for Task Scheduling"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the admin was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the admin was updated")
 * )
 */

class AdminModel extends Authenticatable
{
    use HasFactory;
    //use HasUuids;
    use HasApiTokens;
    public $incrementing = false;

    protected $table = 'admin';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'username', 'password', 'email','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','timezone', 'created_at', 'updated_at'];

    public static function  getAllContact(){
        $res = AdminModel::select('id','username','email','telegram_user_id','telegram_is_valid','line_user_id','firebase_fcm_token')
            ->get();

        return $res;
    }

    public static function getAppsSummaryForLastNDays($days){
        $res_inventory = InventoryModel::selectRaw('count(1) as total')
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))
            ->first();

        $res_user = UserModel::selectRaw('count(1) as total')
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))
            ->first();

        $res_report = ReportModel::selectRaw('count(1) as total')
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))
            ->first();

        $res_error = ErrorModel::selectRaw('count(1) as total')
            ->whereDate('created_at', '>=', Carbon::now()->subDays($days))
            ->first();

        $final_res = (object)[
            'inventory_created' => $res_inventory->total,
            'new_user' => $res_user->total,
            'report_created' => $res_report->total,
            'error_happen' => $res_error->total,
        ];

        return $final_res;
    }
}
