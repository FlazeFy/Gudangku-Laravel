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
// Helpers
use App\Helpers\Generator;

class LoginController extends Controller
{
    public function index()
    {
        return view('login.index');
    }

    private function setUserSession(Request $request, $username, $role, $token, $email, $id) {
        $request->session()->put([
            'username_key' => $username,
            'role_key' => $role,
            'token_key' => $token,
            'email_key' => $email,
            'id_key'=> $id,
        ]);
    }

    private function setGoogleToken($user_id, $access_token, $expiry_date) {
        GoogleTokensModel::deleteGoogleTokensByUserId($user_id);
        GoogleTokensModel::createGoogleTokens($access_token, $expiry_date, $user_id);
    }

    public function login_auth(Request $request) {
        $this->setUserSession($request, $request->username, $request->role, $request->token, $request->email, $request->id);

        return redirect()->route('landing');
    }

    public function redirect_to_google() {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/calendar']) 
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();    
    }

    public function login_google_callback(Request $request) {
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
            if ($user) {
                $user_id = $user->id;
                $token = $user->createToken('login')->plainTextToken;
                $this->setUserSession($request, $user->username, 0, $token, $user->email, $user->id);
                $this->setGoogleToken($user_id, $access_token, $expiry_date);

                return redirect('/')->with('success_message', "Welcome $username"); 
            } else {
                $user = UserModel::createUser($username, "GOOGLE_SIGN_IN", $email);
                if ($user) {
                    $user_id = $user->id;
                    $token = $user->createToken('login')->plainTextToken;
                    $this->setUserSession($request, $user->username, 0, $token, $user->email, $user->id);
                    $this->setGoogleToken($user_id, $access_token, $expiry_date);

                    // Send email
                    $ctx = 'Register new account';
                    $data = "Welcome to GudangKu, happy explore!";
                    dispatch(new UserMailer($ctx, $data, $username, $email));

                    return redirect('/')->with('success_message', "Welcome $username"); 
                } else {
                    return redirect('/login')->with('failed_message', 'failed to login with google'); 
                }
            }
        } catch (\Exception $e) {
            return redirect('/login')->with('failed_message', Generator::getMessageTemplate("unknown_error", null)); 
        }
    }
}
