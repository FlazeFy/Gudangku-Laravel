<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Validator;

// Rules
use App\Rules\ReportCategory;
use App\Rules\ReminderType;
use App\Rules\InventoryCategory;
use App\Rules\InventoryUnit;
use App\Rules\InventoryCapacityUnit;
use App\Rules\DictionaryType;

class Validation
{
    private static function validateInventoryVolumeRelation($validator, $request){
        $validator->after(function ($validator) use ($request) {
            if ($request->filled('inventory_capacity_vol') && is_numeric($request->inventory_capacity_vol) &&
                is_numeric($request->inventory_vol) && $request->inventory_vol < $request->inventory_capacity_vol) {
                $validator->errors()->add('inventory_vol', 'Inventory vol must be greater than or equal to inventory capacity vol.');
            }
        });
    }

    public static function getValidateLogin($request){
        return Validator::make($request->all(), [
            'username' => 'required|min:6|max:36|string',
            'password' => 'required|min:6|string'
        ]);
    }

    public static function getValidateUser($request, $type){
        $rules = [
            'username' => 'required|string|min:6|max:36',
            'email' => 'required|string|min:10|max:255',
        ];

        if ($type === 'create') {
            $rules['password'] = 'required|string|min:6|max:500';
        }

        return Validator::make($request->all(), $rules);
    }

    public static function getValidateInventory($request, $type){
        $baseRules = [
            'inventory_name' => 'required|string|max:75',
            'inventory_category' => ['required', new InventoryCategory],
            'inventory_desc' => 'nullable|string|max:255',
            'inventory_merk' => 'nullable|string|max:75',
            'inventory_color' => 'nullable|string|max:16',
            'inventory_room' => 'required|string|max:36',
            'inventory_storage' => 'nullable|string|max:36',
            'inventory_rack' => 'nullable|string|max:36',
            'inventory_price' => 'nullable|numeric|min:0|max:999999999',
            'inventory_unit' => ['required', new InventoryUnit],
            'inventory_vol' => 'required|numeric|min:0|max:999999',
            'inventory_capacity_unit' => ['nullable', new InventoryCapacityUnit],
            'inventory_capacity_vol' => 'nullable|numeric|min:0|max:999999',
            'created_at' => 'required|date_format:Y-m-d H:i:s',
        ];

        switch ($type) {
            case 'create':
                $rules = $baseRules + ['is_favorite' => 'required|boolean'];
                break;
            case 'update':
                $rules = $baseRules;
                break;
            case 'update_layout':
                return Validator::make($request->all(), [
                    'inventory_storage' => 'required|string|max:36',
                    'storage_desc' => 'nullable|string|max:255',
                ]);
            case 'create_layout':
                return Validator::make($request->all(), [
                    'inventory_room' => 'required|string|max:36',
                    'inventory_storage' => 'required|string|max:36',
                    'storage_desc' => 'nullable|string|max:255',
                    'layout' => 'nullable|string|min:2',
                ]);
            default:
                throw new \InvalidArgumentException('Invalid inventory validation type');
        }

        $validator = Validator::make($request->all(), $rules);
        self::validateInventoryVolumeRelation($validator, $request);

        return $validator;
    }

    public static function getValidateLend($request, $type){
        if($type == 'create_qr'){
            return Validator::make($request->all(), [
                'qr_period' => 'required|numeric|min:1|max:24',
            ]);
        } else if($type == 'create_borrow'){
            return Validator::make($request->all(), [
                'borrower_name' => 'required|string|min:1|max:36',
            ]);
        } else if($type == 'update_returned'){
            return Validator::make($request->all(), [
                'list_inventory' => 'required|array',
                'list_inventory.*.id' => 'required|string|min:36|max:36',
                'list_inventory.*.is_returned' => 'required|boolean',
            ]);
        } 
    }

    public static function getValidateReportItem($request, $type){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'report_item' => 'required|json',
            ]); 
        } else if($type == 'update'){
            return Validator::make($request->all(), [
                'item_name' => 'required|string|max:75|min:2',
                'item_desc' => 'nullable|string|max:144',
                'item_qty' => 'required|numeric|min:0|max:9999',
                'item_price' => 'nullable|numeric|min:0|max:999999999',
            ]);
        } 
    }

    public static function getValidateReport($request, $type){
        $rules = [
            'report_title' => 'required|string|max:36',
            'report_desc' => 'nullable|string|max:255',
            'report_category' => ['required', new ReportCategory],
            'created_at' => 'required|date_format:Y-m-d H:i:s',
        ];

        if ($type === 'create') {
            $rules += [
                'is_reminder' => 'required|numeric|min:0|max:1',
                'reminder_at' => 'nullable|date_format:Y-m-d H:i:s',
                'report_item' => 'nullable|json',
            ];
        }

        return Validator::make($request->all(), $rules);
    }

    public static function getValidateReminder($request, $type){
        $rules = [
            'reminder_desc' => 'required|string|max:255',
            'reminder_type' => ['required', new ReminderType],
            'reminder_context' => 'required|string|max:36',
        ];

        if ($type === 'create' || $type === 'update') {
            $rules['inventory_id'] = 'required|string|min:36|max:36';
        }
        if ($type === 'create_copy') {
            $rules['list_inventory_id'] = 'required|string|min:36';
        }

        return Validator::make($request->all(), $rules);
    }

    public static function getValidateDictionary($request, $type){
        if($type == 'create'){
            return Validator::make($request->all(), [
                'dictionary_name' => 'required|string|max:75|min:2',
                'dictionary_type' => ['required', new DictionaryType],
            ]);  
        } else if($type == 'delete'){
            return Validator::make($request->all(), [
                'id' => 'required|numeric|max:999',
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
