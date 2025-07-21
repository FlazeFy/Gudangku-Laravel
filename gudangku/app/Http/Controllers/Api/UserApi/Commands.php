<?php

namespace App\Http\Controllers\Api\UserApi;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Telegram\Bot\Laravel\Facades\Telegram;

// Helper
use App\Helpers\Generator;
use App\Helpers\Validation;

// Models
use App\Models\UserModel;
use App\Models\HistoryModel;
use App\Models\InventoryModel;
use App\Models\InventoryLayoutModel;
use App\Models\ReportModel;
use App\Models\ReportItemModel;
use App\Models\AdminModel;
use App\Models\ReminderModel;
use App\Models\ValidateRequestModel;
use App\Models\PersonalAccessToken;

// Mailer
use App\Jobs\UserMailer;

class Commands extends Controller
{
    /**
     * @OA\PUT(
     *     path="/api/v1/user/update_telegram_id",
     *     summary="Update telegram token id",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="telegram id updated! and validation has been sended to you",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="telegram id updated! and validation has been sended to you")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="telegram id failed to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="user not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="telegram user id has been used",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="telegram ID has been used. try another")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
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
                        'message' => Generator::getMessageTemplate("custom", 'telegram id updated! and validation has been sended to you'),
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'user'),
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("conflict", 'telegram ID'),
                ], Response::HTTP_CONFLICT);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/user/update_profile",
     *     summary="Update profile",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="profile updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="profile updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="telegram id failed to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="user not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="username / email has been used",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="username or email has been used. try another")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="{validation_msg}",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="{field validation message}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function update_profile(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $validator = Validation::getValidateUser($request,'update');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $check = UserModel::selectRaw('1')
                    ->where(function ($query) use ($request) {
                        $query->where('email', $request->email)
                            ->orWhere('username', $request->username);
                    })
                    ->where('id', '!=', $user_id)
                    ->first();

                if($check == null){
                    $res = UserModel::where('id',$user_id)
                        ->update([
                            'email' => $request->email,
                            'username' => $request->username
                        ]);
                    
                    if ($res) {
                        $user = UserModel::getSocial($user_id);

                        if($user->telegram_is_valid == 1){
                            $response = Telegram::sendMessage([
                                'chat_id' => $user->telegram_user_id,
                                'text' => "Hello,\n\nYour profile has been updated",
                                'parse_mode' => 'HTML'
                            ]);
                        }

                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("update", 'profile'),
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("not_found", 'user'),
                        ], Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("conflict", 'email or username'),
                    ], Response::HTTP_CONFLICT);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
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
     *         description="the validation token has been sended to {email} email account",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="the validation token has been sended to flazen.edu@gmail.com email account")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="there already a request with same username / username has been used. try another",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="there already a request with same username")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
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
                            'message' => Generator::getMessageTemplate("custom", "the validation token has been sended to $email email account"),
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("unknown_error", null),
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("custom", 'there already a request with same username'),
                    ], Response::HTTP_CONFLICT);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("conflict", 'username'),
                ], Response::HTTP_CONFLICT);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
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
     *         description="account is registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="account is registered"),
     *             @OA\Property(property="is_signed_in", type="bool", example=true),
     *             @OA\Property(property="token", type="string", example="123456ABCD")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Token is invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="token is invalid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="username already used",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="username has been used. try another")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="{validation_msg}",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="{field validation message}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function post_validate_register(Request $request)
    {
        try{
            $validator = Validation::getValidateUser($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
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

                        $user = UserModel::createUser($request->username, $request->password, $request->email);
                        if($user){
                            // Send email
                            $ctx = 'Register new account';
                            $email = $request->email;
                            $data = "Welcome to GudangKu, happy explore!";

                            dispatch(new UserMailer($ctx, $data, $username, $email));

                            if(Hash::check($request->password, $user->password)){
                                $token = $user->createToken('login')->plainTextToken;

                                return response()->json([
                                    'is_signed_in' => true,
                                    'token' => $token,
                                    'status' => 'success',
                                    'message' => Generator::getMessageTemplate("custom", "account is registered"),
                                ], Response::HTTP_OK);   
                            } else {
                                return response()->json([
                                    'is_signed_in' => false,
                                    'status' => 'success',
                                    'message' => Generator::getMessageTemplate("custom", "account is registered"),
                                ], Response::HTTP_OK);   
                            }
                        } else {
                            return response()->json([
                                'status' => 'failed',
                                'message' => Generator::getMessageTemplate("unknown_error", null),
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("conflict", 'username'),
                        ], Response::HTTP_CONFLICT);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("custom", 'token is invalid'),
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
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
     *         description="the validation token has been sended to {email} email account",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="the validation token has been sended to flazen.edu@gmail.com email account")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="request not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="the validation token has been sended to flazen.edu@gmail.com email account")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
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
                            'message' => Generator::getMessageTemplate("custom", "the validation token has been sended to $email email account"),
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("unknown_error", null),
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => Generator::getMessageTemplate("not_found", 'request'),
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
                        'message' => Generator::getMessageTemplate("custom", "the validation token has been sended to $email email account"),
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("unknown_error", null),
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
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
     *         description="firebase message token / timezone has been updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="timezone has been updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Timezone is invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="timezone is invalid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
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
                    'message' => Generator::getMessageTemplate("custom", "$cols has been updated"),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("custom", "timezone is invalid"),
                ], Response::HTTP_BAD_REQUEST);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/user/validate_telegram_id",
     *     summary="Validate telegram id change",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="telegram id has been validated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="validation token is not valid"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function validate_telegram_id(Request $request){
        try{
            $user_id = $request->user()->id;
            $res = ValidateRequestModel::where('request_type','telegram_id_validation')
                ->where('created_by',$user_id)
                ->where('request_context',$request->request_context)
                ->delete();
            if($res > 0){
                $user = UserModel::find($user_id);
                UserModel::where('id', $user_id)
                    ->update([
                        'telegram_is_valid' => 1
                    ]);

                $response = Telegram::sendMessage([
                    'chat_id' => $user->telegram_user_id,
                    'text' => "Validation success.\nWelcome <b>{$user->username}</b>!,",
                    'parse_mode' => 'HTML'
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("custom", 'telegram id has been validated'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("custom", 'validation token is not valid'),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/user/{id}",
     *     summary="Delete User By Id",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="User ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="user deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="user deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login | permission denied. only admin can use this request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="user not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="user not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function hard_delete_user_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $check_admin = AdminModel::find($user_id);

            if($check_admin){
                $res = UserModel::where('id',$id)->delete();
                
                if ($res) {
                    InventoryModel::where('created_by',$id)->delete();
                    InventoryLayoutModel::where('created_by',$id)->delete();
                    ReportModel::where('created_by',$id)->delete();
                    ReportItemModel::where('created_by',$id)->delete();
                    HistoryModel::where('created_by',$id)->delete();
                    ReminderModel::where('created_by',$id)->delete();
                    PersonalAccessToken::where('tokenable_id',$id)->delete();

                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("delete", 'user'),
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'user'),
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("permission", 'admin'),
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
