<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedJob extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'failed_jobs';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'type', 'status', 'payload', 'created_at'];
}
