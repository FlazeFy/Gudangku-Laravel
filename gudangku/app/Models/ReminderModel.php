<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Reminder",
 *     type="object",
 *     required={"id", "inventory_id", "reminder_desc", "reminder_type", "reminder_context", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the reminder"),
 *     @OA\Property(property="inventory_id", type="string", format="uuid", description="ID of the inventory"),
 *     @OA\Property(property="reminder_desc", type="string", description="Description of the reminder"),
 *     @OA\Property(property="reminder_type", type="string", description="Type of the reminder"),
 *     @OA\Property(property="reminder_context", type="string", description="Context of the reminder"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the reminder was created"),
 *     @OA\Property(property="created_by", type="string", format="uuid", description="ID of the user who created the reminder"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the reminder was updated")
 * )
 */

class ReminderModel extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'reminder';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'inventory_id', 'reminder_desc', 'reminder_type', 'reminder_context', 'created_at', 'created_by', 'updated_at'];

    public static function getReminderJob(){
        $res = ReminderModel::select('reminder.id','username','email','telegram_user_id','firebase_fcm_token','line_user_id','inventory_name','reminder_desc','reminder_type','reminder_context','timezone')
            ->join('inventory','inventory.id','=','reminder.inventory_id')
            ->join('users','users.id','=','reminder.created_by');

        return $res->get();
    }

    public static function getReminderByInventoryId($id,$user_id){
        $res = ReminderModel::select('id','reminder_desc','reminder_type','reminder_context','created_at')
            ->where('created_by',$user_id)
            ->where('inventory_id',$id);

        return $res->get();
    }
}
