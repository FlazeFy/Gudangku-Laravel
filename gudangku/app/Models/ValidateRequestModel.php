<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ValidateRequest",
 *     type="object",
 *     required={"id", "request_type", "request_context", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="integer", description="Primary Key"),
 *     @OA\Property(property="request_type", type="string", description="Type of the request"),
 *     @OA\Property(property="request_context", type="string", description="Context of the request"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the user make the request"),
 *     @OA\Property(property="created_by", type="string", format="uuid", description="ID of the user who make the request")
 * )
 */

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
