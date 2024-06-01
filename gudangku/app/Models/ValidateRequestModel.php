<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidateRequestModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'validate_request';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'request_type', 'request_context', 'created_at', 'created_by']; 

    public static function getActiveRequest($user_id){
        $res = ValidateRequestModel::select('id','request_type', 'request_context', 'created_at')
            ->where('created_by', $user_id)
            ->first();

        return $res;
    }   
}
