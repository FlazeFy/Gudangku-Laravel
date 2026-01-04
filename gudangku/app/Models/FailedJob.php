<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="FailedJobs",
 *     type="object",
 *     required={"id", "type", "status", "payload", "created_at"},
 * 
 *     @OA\Property(property="id", type="string", description="Primary Key"),
 *     @OA\Property(property="type", type="string", description="Type of the failed jobs on Task Scheduling"),
 *     @OA\Property(property="status", type="string", description="Status of the failed jobs on Task Scheduling"),
 *     @OA\Property(property="payload", type="string", description="Respond / Payload from the error"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the jobs had failed"),
 * )
 */

class FailedJob extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'failed_jobs';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'type', 'status', 'payload', 'created_at'];

    public static function createFailedJob($type, $obj){
        return FailedJob::create([
            'id' => Generator::getUUID(), 
            'type' => $type, 
            'status' => "failed",  
            'payload' => json_encode($obj),
            'created_at' => date("Y-m-d H:i:s"), 
        ]);
    }
}
