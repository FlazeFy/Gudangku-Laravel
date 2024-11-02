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
                'inventory_storage' => 'required|string|max:36',
                'storage_desc' => 'nullable|string|max:255'
            ]);
        } else if($type == 'create_layout'){
            return Validator::make($request->all(), [
                'inventory_room' => 'required|string|max:36',
                'inventory_storage' => 'required|string|max:36',
                'storage_desc' => 'nullable|string|max:255',
                'layout' => 'nullable|string|min:2',
            ]);
        }
    }

    public static function getValidateReportItem($request,$type){
        if($type == 'create'){
            
        } else if($type == 'update'){
            return Validator::make($request->all(), [
                'item_name' => 'required|string|max:75|min:2',
                'item_desc' => 'nullable|string|max:144',
                'item_qty' => 'required|numeric|min:0|max:9999',
                'item_price' => 'required|numeric|min:0|max:999999999',
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

    public static function getValidateUUID($val){
        return preg_match('/^[{]?([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})[}]?$/i', $val) === 1;
    }
}
