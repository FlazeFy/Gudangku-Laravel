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

    public static function getValidateTimezone($val){
        $regex = '/^[+-](0[0-9]|1[0-4]):([0-5][0-9])$/';
        if(preg_match($regex, $val) === 1){
            return true;
        } else {
            return false;
        }
    }
}
