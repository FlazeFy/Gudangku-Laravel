<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Validator;

class Validation
{
    public static function getValidateLogin($request){
        return Validator::make($request->all(), [
            'username' => 'required|min:6|max:30|string',
            'password' => 'required|min:6|string'
        ]);
    }

    public static function getValidateInventory($request,$type){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'inventory_name' => 'required|string|max:75',
                'inventory_category' => 'required|string|max:75',
                'inventory_desc' => 'nullable|string|max:255',
                'inventory_merk' => 'nullable|string|max:75',
                'inventory_color' => 'nullable|string|max:16',
                'inventory_room' => 'required|string|max:36',
                'inventory_storage' => 'nullable|string|max:36',
                'inventory_rack' => 'nullable|string|max:36',
                'inventory_price' => 'required|numeric|min:0|max:999999999',
                'inventory_image' => 'nullable|string|max:500',
                'inventory_unit' => 'required|string|max:36',
                'inventory_vol' => 'required|numeric|min:0|max:999999',
                'inventory_capacity_unit' => 'nullable|string|max:36',
                'inventory_capacity_vol' => 'nullable|numeric|min:0|max:999999',
                'is_favorite' => 'boolean',
                'is_reminder' => 'boolean',
            ]);
        } else if($type == 'update_layout'){
            return Validator::make($request->all(), [
                'inventory_room' => 'required|string|max:36',
                'storage_desc' => 'nullable|string|max:255'
            ]);
        }
    }

    public static function getValidateTimezone($val){
        $regex = '/^[+-](0[0-9]|1[0-4]):([0-5][0-9])$/';
        if(preg_match($regex, $val) === 1){
            return true;
        } else {
            return false;
        }
    }
}
