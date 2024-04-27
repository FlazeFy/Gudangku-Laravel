<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'report';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'report_title', 'report_desc', 'report_category', 'is_reminder', 'remind_at', 'created_at', 'created_by', 'updated_at', 'deleted_at'];
}
