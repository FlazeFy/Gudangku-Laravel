<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

use App\Models\UserModel;

class Generator
{
    public static function getUUID(){
        $result = '';
        $bytes = random_bytes(16);
        $hex = bin2hex($bytes);
        $time_low = substr($hex, 0, 8);
        $time_mid = substr($hex, 8, 4);
        $time_hi_and_version = substr($hex, 12, 4);
        $clock_seq_hi_and_reserved = hexdec(substr($hex, 16, 2)) & 0x3f;
        $clock_seq_low = hexdec(substr($hex, 18, 2));
        $node = substr($hex, 20, 12);
        $uuid = sprintf('%s-%s-%s-%02x%02x-%s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $clock_seq_low, $node);
        
        return $uuid;
    }

    public static function getUserId($role){
        $token = session()->get("token_key");
        $accessToken = PersonalAccessToken::findToken($token);

        if ($accessToken) {
            if($accessToken->tokenable){
                Auth::login($accessToken->tokenable);
                $user = Auth::user();
                
                $res = $user->id;
                return $res;
            } else {
                return redirect("/")->with('failed_message','This account is no longer exist');
            }
        } else {
            return null;
        }
    }

    public static function getUserEmail($user_id){
        $profile = UserModel::select('email')
            ->where('id',$user_id)
            ->first();

        return $profile->email;
    }

    public static function getTokenValidation($len){
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $res = '';
        
        $charCount = strlen($characters);
        for ($i = 0; $i < $len; $i++) {
            $res .= $characters[rand(0, $charCount - 1)];
        }
        
        return $res;
    }

    public static function isMobileDevice(){
        $user = $_SERVER['HTTP_USER_AGENT'];
    
        $type = ['mobile', 'android', 'iphone', 'ipod', 'blackberry', 'windows phone'];
        
        foreach ($type as $key) {
            if (stripos($user, $key) !== false) {
                return true;
            }
        }
    
        return false;
    }

    public static function generateMonthName($idx,$type){
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
        if($type == 'short'){
            return substr($months[$idx-1], 0, 3);
        } else if($type == 'full'){
            return $months[$idx-1];
        }
    }

    public static function generateDocTemplate($type){
        $datetime = now();

        if($type == "footer"){
            return "
                <br><hr>
                <div>
                    <h6 class='date-text' style='margin: 0;'>Parts of FlazenApps</h6>
                    <h6 class='date-text' style='margin: 0; float:right; margin-top:-12px;'>Generated at $datetime by <span style='color:#3b82f6;'>https://gudangku.leonardhors.com</span></h6>
                </div>
            ";
        } else if($type == "header"){
            return "
                <div style='text-align:center;'>
                    <h1 style='color:#3b82f6; margin:0;'>GudangKu</h1>
                    <h4 style='color:#212121; margin:0; font-style:italic;'>Smart Inventory, Easy Life</h4><br>
                </div>
                <hr>
            ";
        } else if($type == "style"){
            return "
                <style>
                    body { font-family: Helvetica; }
                    table { border-collapse: collapse; font-size:10px; width:100%; }
                    td, th { border: 1px solid #dddddd; text-align: left; padding: 8px; }
                    th { text-align:center; }
                    .date-text { font-style:italic; font-weight:normal; color:grey; font-size:11px; }
                    thead { background-color:rgba(59, 131, 246, 0.75); }
                </style>
            ";
        }
    }
}