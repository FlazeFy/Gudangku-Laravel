<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="ScheduleMark",
 *     type="object",
 *     required={"id", "reminder_id", "last_execute"},
 * 
 *     @OA\Property(property="id", type="integer", description="Primary Key"),
 *     @OA\Property(property="reminder_id", type="string", description="ID of the reminder"),
 * 
 *     @OA\Property(property="last_execute", type="string", format="date-time", description="Timestamp when the reminder was executed"),
 * )
 */

class ScheduleMarkModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'schedule_mark';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'reminder_id', 'last_execute'];

    public static function getAllReminderMark($is_paginate){
        $res = ScheduleMarkModel::select('reminder.id','inventory_name','inventory_category','reminder_desc','reminder_type','reminder_context','reminder.created_at','last_execute','username')
            ->join('reminder','reminder.id','=','schedule_mark.reminder_id')
            ->join('inventory','reminder.inventory_id','=','inventory.id')
            ->join('users','reminder.created_by','=','users.id')
            ->orderby('last_execute','DESC');
        
        return $is_paginate ? $res->paginate(12) : $res->get();
    }

    public static function getAllReminderHistory($user_id,$is_paginate){
        $res = ScheduleMarkModel::select('inventory.id','inventory_name','reminder_desc','reminder_type','reminder_context','last_execute')
            ->join('reminder','reminder.id','=','schedule_mark.reminder_id')
            ->join('inventory','reminder.inventory_id','=','inventory.id')
            ->orderby('last_execute','DESC');
        
        return $is_paginate ? $res->paginate(12) : $res->get();
    }

    public static function createScheduleMark($id){
        return ScheduleMarkModel::create([
            'id' => Generator::getUUID(), 
            'reminder_id' => $id,
            'last_execute' => date('Y-m-d H:i:s'), 
        ]);
    }
}
