<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="PersonalAccessToken",
 *     type="object",
 *     required={"id", "tokenable_type", "tokenable_id", "name", "token", "abilities", "created_at"},
 * 
 *     @OA\Property(property="id", type="integer", description="Primary Key"),
 *     @OA\Property(property="tokenable_type", type="string", description="Model / Role who generated the auth token"),
 *     @OA\Property(property="tokenable_id", type="string", description="ID of the user who generated the auth token"),
 *     @OA\Property(property="name", type="string", description="Source of the auth token generated"),
 *     @OA\Property(property="token", type="string", description="Auth token that will be used for Auth Bearer all the protected API"),
 *     @OA\Property(property="abilities", type="string", description="Ability of auth token"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the auth token was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the auth token was updated"),
 *     @OA\Property(property="expires_at", type="string", format="date-time", description="Timestamp when the auth token will expired"),
 *     @OA\Property(property="last_used_at", type="string", format="date-time", description="Timestamp when the last time token was used for calling other protected API")
 * )
 */

class PersonalAccessToken extends Model
{
    use HasFactory;
    //public $incrementing = false;

    protected $table = 'personal_access_tokens';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'tokenable_type', 'tokenable_id', 'name', 'token', 'abilities', 'last_used_at', 'expires_at', 'created_at', 'updated_at'];

    public static function deletePersonalAccessTokenByUserId($user_id){
        return PersonalAccessToken::where('tokenable_id',$user_id)->delete();
    }
}
