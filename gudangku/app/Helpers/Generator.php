<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use DateTime;
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

    public static function getRandomDate($null){
        if($null == 0){
            $start = strtotime('2023-01-01 00:00:00');
            $end = strtotime(date("Y-m-d H:i:s"));
            $random = mt_rand($start, $end); 
            $res = date('Y-m-d H:i:s', $random);
        } else {
            $res = null;
        }

        return $res;
    }

    public static function getRandomTimezone(){
        $symbol = ['+','-'];
        $ran = mt_rand(0, 1);
        $select_symbol = $symbol[$ran];
        if($select_symbol == '+'){
            $hour = mt_rand(0, 14);
        } else {
            $hour = mt_rand(0, 12);
        }

        $timezone = "$select_symbol$hour:00";
        return $timezone;
    }

    public static function getRandomVol($unit){
        if($unit == 'Kilogram'){
            $res = mt_rand(1, 30);
        } else if($unit == 'Pcs'){
            $res = mt_rand(1, 20);
        } else if($unit == 'Liter'){
            $res = mt_rand(1, 5);
        } else if($unit == 'Ml'){
            $res = mt_rand(25, 750);
            $res = round($res / 5) * 5;
        }
        return $res;
    }

    public static function getRandomStorage(){
        $storages = ["Main Table","Secondary Table","Desk","Wardrobe","Cabinet","Shelf","Top Drawer","Bottom Drawer","Center Drawer","Locker","Cupboard","Top Rack","Bottom Rack",
            "Center Rack","Box","Container","Crate","Closet","Stand","Case","Safe","Pantry"];
        $ran = mt_rand(0, count($storages)-1);
        return $storages[$ran];
    }

    public static function getDateDiff($datetime){
        $now = new DateTime();
        $date = new DateTime($datetime);

        $diff = $now->diff($date);
        if ($diff->d > 0) {
            $days = $diff->d;
            $hours = $diff->h;
            return ($days > 1 ? $days.' days' : '1 day').($hours > 0 ? ' and '.$hours.' hour'.($hours > 1 ? 's' : '') : '').' ago';
        } elseif ($diff->h > 0) {
            return $diff->h.' hour'.($diff->h > 1 ? 's' : '').' ago';
        } elseif ($diff->i > 0) {
            return $diff->i.' minute'.($diff->i > 1 ? 's' : '').' ago';
        } else {
            return 'Just now';
        }
    }

    public static function extractText($pos, $text, $start_text, $end_text){
        $start = strpos($text, $start_text);
        $end = strpos($text, $end_text);

        if ($start !== false && $end !== false) {
            $start += strlen($start_text);
            
            $length = $end - $start;
            $result = trim(substr($text, $start, $length));
            return [
                "result" => $result != "" ? $result : null,
                "is_valid" => true
            ]; 
        } 

        return [
            "result" => null,
            "is_valid" => false
        ];
    }

    public static function extractTextFromImage($filePath)
    {
        $apiKey = 'K81110450288957'; 
        $url = 'https://api.ocr.space/parse/image';

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url, [
                'headers' => [
                    'apikey' => $apiKey,
                ],
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($filePath, 'r'),
                    ],
                    [
                        'name'     => 'language',
                        'contents' => 'eng', 
                    ],
                ],
            ]);

            $responseBody = json_decode($response->getBody(), true);

            if (!empty($responseBody['ParsedResults'][0]['ParsedText'])) {
                return $responseBody['ParsedResults'][0]['ParsedText'];
            } else {
                return 'No text detected in the image.';
            }
        } catch (\Exception $e) {
            return 'Error during OCR process: ' . $e->getMessage();
        }
    }

    public static function checkPossiblePrice($arr){
        // Check if start with number, have comma, have stop. or contain o or b
        $res = []; 

        foreach ($arr as $string) {
            if (preg_match('/^\d.*[,.].*[ob]+/', $string) || preg_match('/^Rp\s*.*[,.].*/', $string)) { 
                $string = str_replace('o', '0', $string);
                $string = str_replace('O', '0', $string);
                $string = str_replace('b', '6', $string);
                $string = str_replace('z', '2', $string);
                $string = str_replace('Z', '2', $string);
                $string = str_replace('s', '5', $string);
                $string = str_replace('S', '5', $string);
            }

            $res[] = $string;
        }

        return $res;
    }
}