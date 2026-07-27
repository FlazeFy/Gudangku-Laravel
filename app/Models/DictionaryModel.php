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

    public static function isUsedName($name, $type) {
        return DictionaryModel::whereRaw('LOWER(dictionary_name) = ?', [strtolower($name)])
            ->whereRaw('LOWER(dictionary_type) = ?', [strtolower($type)])
            ->exists();
    }

    public static function getDictionaryByType($type) {
        $res = DictionaryModel::select('dictionary_name','dictionary_type');
        if (strpos($type, ',')) {
            $dcts = explode(",", $type);
            foreach ($dcts as $dt) {
                $res = $res->orwhere('dictionary_type',$dt); 
            }
        } else {
            $res = $res->where('dictionary_type',$type); 
        }

        return $res->orderby('dictionary_type', 'ASC')
            ->orderby('dictionary_name', 'ASC')
            ->get();
    }

    public static function getRandom($null,$type) {
        return $null == 0 ? DictionaryModel::inRandomOrder()->take(1)->where('dictionary_type',$type)->first()->dictionary_name : null;
    }

    public static function createDictionary($dictionary_type, $dictionary_name) {
        return DictionaryModel::create([
            'dictionary_type' => $dictionary_type,
            'dictionary_name' => $dictionary_name,
        ]);
    }
}
