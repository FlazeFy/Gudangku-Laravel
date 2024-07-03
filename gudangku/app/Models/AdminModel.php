<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class AdminModel extends Authenticatable
{
    use HasFactory;
    //use HasUuids;
    use HasApiTokens;
    public $incrementing = false;

    protected $table = 'admin';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'username', 'password', 'email','telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id', 'created_at', 'updated_at'];

    public static function  getAllContact(){
        $res = AdminModel::select('id','username','email','telegram_user_id','line_user_id','firebase_fcm_token')
            ->get();

        return $res;
    }
}
