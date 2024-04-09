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
}
