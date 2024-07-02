<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderModel extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'reminder';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'inventory_id', 'reminder_desc', 'reminder_type', 'reminder_context', 'created_at', 'created_by', 'updated_at'];

    public static function getReminderJob(){
        $res = ReminderModel::select('reminder.id','username','email','telegram_user_id','firebase_fcm_token','line_user_id','inventory_name','reminder_desc','reminder_type','reminder_context')
            ->join('inventory','inventory.id','=','reminder.inventory_id')
            ->join('users','users.id','=','reminder.created_by');

        return $res->get();
    }
}
