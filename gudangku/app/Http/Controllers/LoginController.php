<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

// Model
use App\Models\UserModel;
use App\Models\GoogleTokensModel;
// Mailer
use App\Jobs\UserMailer;

class LoginController extends Controller
{
    public function index()
    {
        return view('login.index');
    }

    public function login_auth(Request $request){
        $request->session()->put('username_key', $request->username);
        $request->session()->put('role_key', $request->role);
        $request->session()->put('token_key', $request->token);
        $request->session()->put('email_key', $request->email);
        $request->session()->put('id_key', $request->id);

        return redirect()->route('landing');
    }

    public function redirect_to_google(){
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/calendar']) 
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();    
    }

    public function login_google_callback(Request $request){
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->scopes([
                    'https://www.googleapis.com/auth/calendar',
                    'openid',
                    'profile',
                    'email'
                ])
                ->with(['access_type' => 'offline', 'prompt' => 'consent'])
                ->user();
            $email = $googleUser->getEmail();
            $username = explode('@', $googleUser->getEmail())[0];
            $access_token = $googleUser->token;
            $expiry_date = now()->addSeconds($googleUser->expiresIn);

            $user = UserModel::getUserByUsernameOrEmail($username,$email);
            if($user){
                $user_id = $user->id;
                $token = $user->createToken('login')->plainTextToken;
                $request->session()->put('username_key', $user->username);
                $request->session()->put('role_key', 0);
                $request->session()->put('token_key', $token);
                $request->session()->put('email_key', $user->email);
                $request->session()->put('id_key', $user->id);

                GoogleTokensModel::deleteGoogleTokensByUserId($user_id);
                GoogleTokensModel::createGoogleTokens($access_token, $expiry_date, $user_id);

                return redirect('/')->with('success_message', "Welcome $username"); 
            } else {
                $user = UserModel::createUser($username, "GOOGLE_SIGN_IN", $email);
                if($user){
                    $user_id = $user->id;
                    $token = $user->createToken('login')->plainTextToken;
                    $request->session()->put('username_key', $user->username);
                    $request->session()->put('role_key', 0);
                    $request->session()->put('token_key', $token);
                    $request->session()->put('email_key', $user->email);
                    $request->session()->put('id_key', $user_id);
                    
                    // Send email
                    $ctx = 'Register new account';
                    $data = "Welcome to GudangKu, happy explore!";
                    dispatch(new UserMailer($ctx, $data, $username, $email));

                    GoogleTokensModel::deleteGoogleTokensByUserId($user_id);
                    GoogleTokensModel::createGoogleTokens($access_token, $expiry_date, $user_id);

                    return redirect('/')->with('success_message', "Welcome $username"); 
                } else {
                    return redirect('/login')->with('failed_message', 'failed to login with google'); 
                }
            }
        } catch (\Exception $e) {
            return redirect('/login')->with('failed_message', $e->getMessage()); 
        }
    }
}
