<?php

namespace App\Http\Controllers\Api\UserApi;

use App\Http\Controllers\Controller;

use App\Helpers\Generator;
use App\Helpers\Validation;

// Models
use App\Models\UserModel;
use App\Models\ValidateRequestModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Telegram\Bot\Laravel\Facades\Telegram;

use App\Jobs\UserMailer;
use Illuminate\Support\Facades\Mail;

class Commands extends Controller
{
    /**
     * @OA\PUT(
     *     path="/api/v1/user/update_telegram_id",
     *     summary="Update telegram token id",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="telegram id updated! and validation has been sended to you"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="telegram id failed to update"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="telegram user id has been used"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function update_telegram_id(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $new_telegram_id = $request->telegram_user_id;

            $check = UserModel::selectRaw('1')
                ->where('telegram_user_id', $new_telegram_id)
                ->first();

            if($check == null){
                $res = UserModel::where('id',$user_id)
                    ->update([
                        'telegram_user_id' => $new_telegram_id,
                        'telegram_is_valid' => 0
                    ]);
                
                if ($res) {
                    $token_length = 6;
                    $token = Generator::getTokenValidation($token_length);

                    ValidateRequestModel::create([
                        'id' => Generator::getUUID(), 
                        'request_type' => 'telegram_id_validation',
                        'request_context' => $token, 
                        'created_at' => date('Y-m-d H:i:s'), 
                        'created_by' => $user_id
                    ]);

                    $user = UserModel::find($user_id);

                    $response = Telegram::sendMessage([
                        'chat_id' => $new_telegram_id,
                        'text' => "Hello,\n\nWe received a request to validate GudangKu apps's account with username <b>$user->username</b> to sync with this Telegram account. If you initiated this request, please confirm that this account belongs to you by clicking the button YES.\n\nAlso we provided the Token :\n$token\n\nIf you did not request this, please press button NO.\n\nThank you, GudangKu",
                        'parse_mode' => 'HTML'
                    ]);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'telegram id updated! and validation has been sended to you',
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'telegram id failed to update',
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'telegram user id has been used',
                ], Response::HTTP_CONFLICT);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin ',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/register/token",
     *     summary="Check and send validation token to the user who in registration process",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="the validation token has been sended to {email} email account"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="there already a request with same username / username already being used"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_register_validation_token(Request $request)
    {
        try{
            $username = $request->username;
            $check_user = UserModel::selectRaw('1')
                ->where('username',$username)
                ->first();

            if(!$check_user){
                $valid = ValidateRequestModel::selectRaw('1')
                    ->where('request_type','register')
                    ->where('created_by',$username)
                    ->first();

                if(!$valid){
                    $token_length = 6;
                    $token = Generator::getTokenValidation($token_length);

                    $valid_insert = ValidateRequestModel::create([
                        'id' => Generator::getUUID(), 
                        'request_type' => 'register',
                        'request_context' => $token, 
                        'created_at' => date('Y-m-d H:i:s'), 
                        'created_by' => $username
                    ]);

                    if($valid_insert){
                        // Send email
                        $ctx = 'Generate registration token';
                        $email = $request->email;
                        $data = "You almost finish your registration process. We provided you with this token <br><h5>$token</h5> to make sure this account is yours.<br>If you're the owner just paste this token into the Token's Field. If its not, just leave this message<br>Thank You, Gudangku";

                        dispatch(new UserMailer($ctx, $data, $username, $email));

                        return response()->json([
                            'status' => 'success',
                            'message' => "the validation token has been sended to $email email account",
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'something wrong. please contact admin',
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'there already a request with same username',
                    ], Response::HTTP_CONFLICT);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'username already being used',
                ], Response::HTTP_CONFLICT);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/register/account",
     *     summary="Register account and accept validation",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="account is registered"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Token is invalid"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="username already used"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function post_validate_register(Request $request)
    {
        try{
            $username = $request->username;
            $valid = ValidateRequestModel::selectRaw('id')
                ->where('request_type','register')
                ->where('request_context',$request->token)
                ->where('created_by',$username)
                ->first();

            if($valid){
                $check_user = UserModel::selectRaw('1')
                    ->where('username',$username)
                    ->first();

                if(!$check_user){
                    ValidateRequestModel::destroy($valid->id);

                    $user = UserModel::create([
                        'id' => Generator::getUUID(), 
                        'username' => $request->username, 
                        'password' => Hash::make($request->password),
                        'telegram_user_id' => null,
                        'telegram_is_valid' => 0,
                        'email' => $request->email,
                        'phone' => null,
                        'created_at' => date('Y-m-d H:i:s'), 
                        'updated_at' => null
                    ]);

                    if($user){
                        // Send email
                        $ctx = 'Register new account';
                        $email = $request->email;
                        $data = "Welcome to GudangKu, happy explore!";

                        dispatch(new UserMailer($ctx, $data, $username, $email));

                        if(!Hash::check($request->password, $user->password)){
                            $token = $user->createToken('login')->plainTextToken;

                            return response()->json([
                                'is_signed_in' => true,
                                'token' => $token,
                                'status' => 'success',
                                'message' => "account is registered",
                            ], Response::HTTP_OK);   
                        } else {
                            return response()->json([
                                'is_signed_in' => false,
                                'status' => 'success',
                                'message' => "account is registered",
                            ], Response::HTTP_OK);   
                        }
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'something wrong. please contact admin',
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'username already used',
                    ], Response::HTTP_CONFLICT);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Token is invalid',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/register/regen_token",
     *     summary="Regenerate registration token",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="the validation token has been sended to {email} email account"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="request not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function regenerate_register_token(Request $request)
    {
        try{
            $username = $request->username;
            $valid = ValidateRequestModel::select('id')
                ->where('request_type','register')
                ->where('created_by',$username)
                ->first();

            $token_length = 6;
            $token = Generator::getTokenValidation($token_length);

            if($valid){
                $delete = ValidateRequestModel::destroy($valid->id);

                if($delete > 0){
                    $valid_insert = ValidateRequestModel::create([
                        'id' => Generator::getUUID(), 
                        'request_type' => 'register',
                        'request_context' => $token, 
                        'created_at' => date('Y-m-d H:i:s'), 
                        'created_by' => $username
                    ]);

                    if($valid_insert){
                        // Send email
                        $ctx = 'Generate registration token';
                        $email = $request->email;
                        $data = "You almost finish your registration process. We provided you with this token <br><h5>$token</h5> to make sure this account is yours.<br>If you're the owner just paste this token into the Token's Field. If its not, just leave this message<br>Thank You, Gudangku";

                        dispatch(new UserMailer($ctx, $data, $username, $email));

                        return response()->json([
                            'status' => 'success',
                            'message' => "the validation token has been sended to $email email account",
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'something wrong. please contact admin',
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'request not found',
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                // already deleted
                $valid_insert = ValidateRequestModel::create([
                    'id' => Generator::getUUID(), 
                    'request_type' => 'register',
                    'request_context' => $token, 
                    'created_at' => date('Y-m-d H:i:s'), 
                    'created_by' => $username
                ]);

                if($valid_insert){
                    // Send email
                    $ctx = 'Generate registration token';
                    $email = $request->email;
                    $data = "You almost finish your registration process. We provided you with this token <br><h5>$token</h5> to make sure this account is yours.<br>If you're the owner just paste this token into the Token's Field. If its not, just leave this message<br>Thank You, Gudangku";

                    dispatch(new UserMailer($ctx, $data, $username, $email));

                    return response()->json([
                        'status' => 'success',
                        'message' => "the validation token has been sended to $email email account",
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'something wrong. please contact admin',
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/user/update_timezone_fcm",
     *     summary="Update user timezone",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="firebase message token / timezone has been updated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Timezone is invalid"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function update_timezone_fcm(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $cols = "timezone";
            $check = Validation::getValidateTimezone($request->timezone);
            if($check){
                if($request->firebase_fcm_token == null){
                    UserModel::where('id',$user_id)->update([
                        'timezone'=> $request->timezone
                    ]);
                } else {
                    UserModel::where('id',$user_id)->update([
                        'timezone'=> $request->timezone,
                        'firebase_fcm_token'=> $request->firebase_fcm_token
                    ]);
                    $cols .= " and firebase fcm token";
                }
                
                return response()->json([
                    'status' => 'success',
                    'message' => "$cols has been updated",
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Timezone is invalid',
                ], Response::HTTP_BAD_REQUEST);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin ',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
