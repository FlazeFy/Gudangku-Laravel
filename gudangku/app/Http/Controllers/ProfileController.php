<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Telegram\Bot\Laravel\Facades\Telegram;

// Helpers
use App\Helpers\Generator;
// Models
use App\Models\UserModel;
use App\Models\ValidateRequestModel;

class ProfileController extends Controller
{
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            return view('profile.index');
        } else {
            return redirect("/login");
        }
    }

    public function sign_out()
    {
        Session::flush();

        return redirect('/login')->with('success_message', 'Successfully sign out'); 
    }

    public function validate_telegram_id(Request $request)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));
        $user = UserModel::select('telegram_user_id','username')->where('id',$user_id)->first();
        $token_length = 6;
        $token = Generator::getTokenValidation($token_length);

        ValidateRequestModel::create([
            'id' => Generator::getUUID(), 
            'request_type' => 'telegram_id_validation',
            'request_context' => $token, 
            'created_at' => date('Y-m-d H:i:s'), 
            'created_by' => $user_id
        ]);

        $response = Telegram::sendMessage([
            'chat_id' => $user->telegram_user_id,
            'text' => "Hello,\n\nWe received a request to validate GudangKu apps's account with username <b>$user->username</b> to sync with this Telegram account. If you initiated this request, please confirm that this account belongs to you by clicking the button YES.\n\nAlso we provided the Token :\n$token\n\nIf you did not request this, please press button NO.\n\nThank you, GudangKu",
            'parse_mode' => 'HTML'
        ]);

        return redirect()->back()->with('success_message', 'Validation has sended to your telegram account'); 
    }

    public function submit_telegram_validation(Request $request)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));
        $user = UserModel::select('telegram_user_id','username')->where('id',$user_id)->first();
        $id = $request->id;

        $query = ValidateRequestModel::where('request_context',$request->validate_token)
            ->where('id',$id)
            ->where('created_by',$user_id)
            ->where('request_type','telegram_id_validation')
            ->delete();
        

        if ($query > 0) {
            UserModel::where('id', $user_id)
                ->update([
                    'telegram_is_valid' => 1
                ]);

            $response = Telegram::sendMessage([
                'chat_id' => $user->telegram_user_id,
                'text' => "Validation success.\nWelcome <b>{$user->username}</b>!,",
                'parse_mode' => 'HTML'
            ]);
    
            return redirect()->back()->with('success_message', 'Telegram Account is Validated!');
        } else {
            return redirect()->back()->with('error_message', 'Validation failed. Please try again.');
        }
    }
}
