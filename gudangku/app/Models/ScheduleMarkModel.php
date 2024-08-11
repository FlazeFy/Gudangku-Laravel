<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
