<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\UserModel;

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
        return Socialite::driver('google')->redirect();
    }

    public function login_google_callback(Request $request){
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $email = $googleUser->getEmail();
            $username = explode('@', $googleUser->getEmail())[0];

            $user = UserModel::getUserByUsernameOrEmail($username,$email);
            if($user){
                $token = $user->createToken('login')->plainTextToken;
                $request->session()->put('username_key', $user->username);
                $request->session()->put('role_key', 0);
                $request->session()->put('token_key', $token);
                $request->session()->put('email_key', $user->email);
                $request->session()->put('id_key', $user->id);

                return redirect('/')->with('success_message', "Welcome $username"); 
            } else {
                $user = UserModel::createUser($username, "GOOGLE_SIGN_IN", $email);
                if($user){
                    $token = $user->createToken('login')->plainTextToken;
                    $request->session()->put('username_key', $user->username);
                    $request->session()->put('role_key', 0);
                    $request->session()->put('token_key', $token);
                    $request->session()->put('email_key', $user->email);
                    $request->session()->put('id_key', $user->id);
                    
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
            return redirect('/login')->with('failed_message', $e->getMessage()); 
        }
    }
}
