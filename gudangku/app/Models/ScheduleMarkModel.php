<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleMarkModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'schedule_mark';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'reminder_id', 'last_execute'];
}
