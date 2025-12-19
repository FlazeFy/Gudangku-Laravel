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

    public static function getRandom($null,$type){
        if($null == 0){
            $data = DictionaryModel::inRandomOrder()->take(1)->where('dictionary_type',$type)->first();
            $res = $data->dictionary_name;
        } else {
            $res = null;
        }
        
        return $res;
    }

    public static function isUsedName($name, $type){
        $res = DictionaryModel::selectRaw('1')
            ->whereRaw('LOWER(dictionary_name) = LOWER(?)', [$name])
            ->whereRaw('LOWER(dictionary_type) = LOWER(?)', [$type])
            ->first();

        return $res ? true : false;
    }

    public static function createDictionary($dictionary_type, $dictionary_name){
        return DictionaryModel::create([
            'dictionary_type' => $dictionary_type,
            'dictionary_name' => $dictionary_name,
        ]);
    }

    public static function getDictionaryByType($type){
        $res = DictionaryModel::select('dictionary_name','dictionary_type');
        if(strpos($type, ',')){
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
}
