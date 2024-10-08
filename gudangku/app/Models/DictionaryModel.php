<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Dictionary",
 *     type="object",
 *     required={"dictionary_type", "dictionary_name"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="dictionary_type", type="string", description="Type of the dictionary"),
 *     @OA\Property(property="dictionary_name", type="string", description="Name of the dictionary")
 * )
 */

class DictionaryModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'dictionary';
    protected $primaryKey = 'id';
    protected $fillable = ['dictionary_type', 'dictionary_name'];
}
