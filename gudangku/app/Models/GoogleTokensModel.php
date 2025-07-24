<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helpers
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="Google Tokens",
 *     type="object",
 *     required={"id", "access_token", "expiry", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="integer", description="Primary Key"),
 *     @OA\Property(property="access_token", type="string", description="Access token of Google Sign In"),
 *     @OA\Property(property="expiry", type="string", description="Expire datetime of access token"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the user created the history"),
 *     @OA\Property(property="created_by", type="string", format="uuid", description="ID of the user who created the history")
 * )
 */

class GoogleTokensModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'google_tokens';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'access_token', 'expiry', 'created_at', 'created_by'];

    public static function createGoogleTokens($access_token, $expiry_date, $user_id){
        return GoogleTokensModel::create([
            'id' => Generator::getUUID(), 
            'access_token' => $access_token, 
            'expiry' => $expiry_date, 
            'created_at' => date('Y-m-d H:i:s'),  
            'created_by' => $user_id
        ]);
    }

    public static function getGoogleTokensByUserId($user_id){
        return GoogleTokensModel::where('created_by', $user_id)->first();
    }

    public static function deleteGoogleTokensByUserId($user_id){
        return GoogleTokensModel::where('created_by', $user_id)->delete();
    }
}
