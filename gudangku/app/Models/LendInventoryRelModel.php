<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="Lend Inventory Relation",
 *     type="object",
 *     required={"id", "lend_id", "inventory_id", "borrower_name", "created_at"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the lend"),
 *     @OA\Property(property="lend_id", type="string", description="ID of the lend"),
 *     @OA\Property(property="inventory_id", type="number", format="float", description="Id of the inventory"),
 *     @OA\Property(property="borrower_name", type="string", description="Name / username of the borrower"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the lend inventory relation was created"),
 * )
 */
class LendInventoryRelModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'lend_inventory_rel';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'lend_id', 'inventory_id', 'borrower_name', 'created_at'];

    public static function createLendInventoryRel($lend_id, $inventory_id, $borrower_name){
        return LendInventoryRelModel::create([
            'id' => Generator::getUUID(), 
            'lend_id' => $lend_id, 
            'inventory_id' => $inventory_id,  
            'borrower_name' => $borrower_name,
            'created_at' => date('Y-m-d H:i:s'), 
        ]);
    }
}
