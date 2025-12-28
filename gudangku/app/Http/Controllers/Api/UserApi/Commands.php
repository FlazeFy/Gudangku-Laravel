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
     *     summary="Put Update Telegram ID",
     *     description="This request is used to update telegram ID by given `telegram_user_id`. This request interacts with the MySQL database, broadcast message using Telegram, and has a protected routes.",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"telegram_user_id"},
     *             @OA\Property(property="telegram_user_id", type="string", example="123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="telegram id updated! and validation has been sended to you",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="telegram id updated! and validation has been sended to you")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="telegram id failed to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="telegram ID is invalid. Please check your Telegram ID")
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
    public function putUpdateTelegramID(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $new_telegram_id = $request->telegram_user_id;

            // Check if telegram ID has been used
            $check = UserModel::isTelegramIDUsed($new_telegram_id);
            if($check === null){
                // Update user by ID
                $res = UserModel::updateUserById(['telegram_user_id' => $new_telegram_id, 'telegram_is_valid' => 0],$user_id);
                if($res){
                    // Generate token
                    $token_length = 6;
                    $token = Generator::getTokenValidation($token_length);

                    // Create validate request
                    ValidateRequestModel::createValidateRequest('telegram_id_validation', $token, $user_id);

                    // Get user by ID
                    $user = UserModel::getSocial($user_id);

                    // Check if user Telegram ID is valid
                    if(TelegramMessage::checkTelegramID($new_telegram_id)){
                        // Send telegram message
                        $response = Telegram::sendMessage([
                            'chat_id' => $new_telegram_id,
                            'text' => "Hello,\n\nWe received a request to validate GudangKu apps's account with username <b>$user->username</b> to sync with this Telegram account. If you initiated this request, please confirm that this account belongs to you by clicking the button YES.\n\nAlso we provided the Token :\n$token\n\nIf you did not request this, please press button NO.\n\nThank you, GudangKu",
                            'parse_mode' => 'HTML'
                        ]);
    
                        // Return success response
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("custom", 'telegram id updated! and validation has been sended to you'),
                        ], Response::HTTP_OK);
                    } else {
                        // Reset telegram from user account if not valid
                        UserModel::updateUserById(['telegram_user_id' => null, 'telegram_is_valid' => 0],$user_id);
                        
                        // Return success response
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("custom", 'telegram ID is invalid. Please check your Telegram ID'),
                        ], Response::HTTP_BAD_REQUEST);
                    }
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
     *     summary="Put Update Profile",
     *     description="This request is used to update profile by given `username` and `email`. This request interacts with the MySQL database, broadcast message using Telegram, and has a protected routes.",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","email"},
     *             @OA\Property(property="username", type="string", example="FlazenApps"),
     *             @OA\Property(property="email", type="string", example="flazfy@gmail.com")
     *         )
     *     ),
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
    public function putUpdateProfile(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            // Validate request body
            $validator = Validation::getValidateUser($request,'update');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $extra_msg = null;
                
                // Check if username / email has been used
                $check = UserModel::isUsernameEmailUsedWithExceptionalId($request->email, $request->username, $user_id);
                if($check == null){
                    // Update user by ID
                    $res = UserModel::updateUserById(['email' => $request->email, 'username' => $request->username],$user_id);
                    if ($res) {
                        // Get user data by ID
                        $user = UserModel::getSocial($user_id);

                        if($user->telegram_is_valid == 1){
                            // Check if user Telegram ID is valid
                            // If Telegram ID is invalid, keep return success response because this is update profile function. (assume that the telegram is not validated yet)
                            if(TelegramMessage::checkTelegramID($user->telegram_user_id)){
                                $response = Telegram::sendMessage([
                                    'chat_id' => $user->telegram_user_id,
                                    'text' => "Hello,\n\nYour profile has been updated",
                                    'parse_mode' => 'HTML'
                                ]);
                            } else {
                                // Reset telegram from user account if not valid
                                UserModel::updateUserById([ 'telegram_user_id' => null, 'telegram_is_valid' => 0],$user_id);
                                $extra_msg = ' Telegram ID is invalid. Please check your Telegram ID';
                            }
                        }
                        
                        $msg = Generator::getMessageTemplate("update", 'profile');

                        // Return success response
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("custom", $extra_msg ? $msg.". But ".$extra_msg: $msg),
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
     *     summary="Post Register Validation Token",
     *     description="This request is used to check and send validation token to the user who in registration process. This request interacts with the MySQL database, broadcast email, and has a protected routes.",
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
    public function postRegisterValidationToken(Request $request)
    {
        try{
            $username = $request->username;

            // Check if username has not been used
            $check_user = UserModel::isUsernameUsed($username);
            if(!$check_user){
                // Get active request
                $valid = ValidateRequestModel::getActiveRequest($username, 'register');
                if(!$valid){
                    // Generate token
                    $token_length = 6;
                    $token = Generator::getTokenValidation($token_length);

                    // Create validate request
                    $valid_insert = ValidateRequestModel::createValidateRequest('register', $token, $username);
                    if($valid_insert){
                        // Send email
                        $ctx = 'Generate registration token';
                        $email = $request->email;
                        $data = "You almost finish your registration process. We provided you with this token <br><h5>$token</h5> to make sure this account is yours.<br>If you're the owner just paste this token into the Token's Field. If its not, just leave this message<br>Thank You, Gudangku";
                        dispatch(new UserMailer($ctx, $data, $username, $email));

                        // Return success response
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
     *     summary="Post Register Account & Accept Validation",
     *     description="This request is used to finally create an user and accept validation request. This request interacts with the MySQL database, broadcast email, and has a protected routes.",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password","email","token"},
     *             @OA\Property(property="username", type="string", example="FlazenApps"),
     *             @OA\Property(property="password", type="string", example="abcde132"),
     *             @OA\Property(property="email", type="string", example="flazfy@gmail.com"),
     *             @OA\Property(property="token", type="string", example="AKC123"),
     *         )
     *     ),
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
    public function postValidateRegister(Request $request)
    {
        try{
            // Validate request body
            $validator = Validation::getValidateUser($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $username = $request->username;

                // Get active request
                $valid = ValidateRequestModel::getActiveRequest($username, 'register', $request->token);
                if($valid){
                    // Check if username has not been used
                    $check_user = UserModel::isUsernameUsed($username);
                    if(!$check_user){
                        // Hard delete request by id
                        ValidateRequestModel::destroy($valid->id);

                        // Create user
                        $user = UserModel::createUser($request->username, $request->password, $request->email);
                        if($user){
                            // Send email
                            $ctx = 'Register new account';
                            $email = $request->email;
                            $data = "Welcome to GudangKu, happy explore!";
                            dispatch(new UserMailer($ctx, $data, $username, $email));

                            // Return success response
                            if(Hash::check($request->password, $user->password)){
                                // Hash the password
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
     *     summary="Post Regenerate Registration Token",
     *     description="This request is used to regenerate and send validation token that has been expired or just regenerate it. This request interacts with the MySQL database, broadcast email, and has a protected routes.",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username"},
     *             @OA\Property(property="username", type="string", example="flazefy")
     *         )
     *     ),
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
    public function postRegenerateRegisterToken(Request $request)
    {
        try{
            $username = $request->username;

            // Generate token
            $token_length = 6;
            $token = Generator::getTokenValidation($token_length);

            $ctx = 'Generate registration token';
            $email = $request->email;
            $data = "You almost finish your registration process. We provided you with this token <br><h5>$token</h5> to make sure this account is yours.<br>If you're the owner just paste this token into the Token's Field. If its not, just leave this message<br>Thank You, Gudangku";

            // Get active register request
            $valid = ValidateRequestModel::getActiveRequest($username, 'register');
            if($valid){
                // Hard delete validate request by ID
                $delete = ValidateRequestModel::destroy($valid->id);

                if($delete > 0){
                    // Create validate request
                    $valid_insert = ValidateRequestModel::createValidateRequest('register', $token, $username);

                    if($valid_insert){
                        // Send email token validation
                        dispatch(new UserMailer($ctx, $data, $username, $email));

                        // Return success response
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
                // Create validate request
                $valid_insert = ValidateRequestModel::createValidateRequest('register', $token, $username);

                if($valid_insert){
                    // Send email token validation
                    dispatch(new UserMailer($ctx, $data, $username, $email));

                    // Return success response
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
     *     summary="Put Update User Timezone",
     *     description="This request is used to update user's `timezone`. This request interacts with the MySQL database, and has a protected routes.",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"timezone"},
     *             @OA\Property(property="timezone", type="string", example="+11"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="firebase message token / timezone has been updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="timezone has been updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
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
    public function updateTimezoneFCM(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $cols = "timezone";

            // Validate timezone format
            $check = Validation::getValidateTimezone($request->timezone);
            if($check){
                // Update user by ID
                if($request->firebase_fcm_token == null){
                    UserModel::updateUserById(['timezone'=> $request->timezone],$user_id);
                } else {
                    UserModel::updateUserById(['timezone'=> $request->timezone, 'firebase_fcm_token'=> $request->firebase_fcm_token],$user_id);
                    $cols .= " and firebase fcm token";
                }
                
                // Return success response
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
     *     summary="Put Validate Telegram ID",
     *     description="This request is used to validate Telegram ID change by give `request_context`. This request interacts with the MySQL database, and has a protected routes.",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"request_context"},
     *             @OA\Property(property="request_context", type="string", example="AX1AJ9"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="telegram id has been validated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="telegram id has been validated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid telegram ID",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Telegram ID is invalid. Please check your Telegram ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="validation token not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="validation token is not valid")
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
    public function putValidateTelegramID(Request $request){
        try{
            $user_id = $request->user()->id;

            // Hard delete validate request by request context
            $res = ValidateRequestModel::deleteValidateRequestByRequestContext($request->request_context, $user_id);
            if($res > 0){
                // Update user by ID
                $res = UserModel::updateUserById(['telegram_is_valid' => 1],$user_id);
                if($res > 0){
                    // Get user data
                    $user = UserModel::getSocial($user_id);

                    // Check if user Telegram ID is valid
                    if(TelegramMessage::checkTelegramID($user->telegram_user_id)){
                        // Send telegram message with file
                        $response = Telegram::sendMessage([
                            'chat_id' => $user->telegram_user_id,
                            'text' => "Validation success.\nWelcome <b>{$user->username}</b>!,",
                            'parse_mode' => 'HTML'
                        ]);
        
                        // Return success response
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("custom", 'telegram id has been validated'),
                        ], Response::HTTP_OK);
                    } else {
                        // Reset telegram from user account if not valid
                        UserModel::updateUserById([ 'telegram_user_id' => null, 'telegram_is_valid' => 0],$user_id);

                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("custom", "Telegram ID is invalid. Please check your Telegram ID"),
                        ], Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'user'),
                    ], Response::HTTP_NOT_FOUND);
                }
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
     *     summary="Hard Delete User By Id",
     *     description="This request is used to delete user by given user's `id`. This request interacts with the MySQL database and has a protected routes.",
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
    public function hardDeleteUserById(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            // Make sure only admin can access this request
            $check_admin = AdminModel::find($user_id);
            if($check_admin){
                // Hard delete user by ID
                $res = UserModel::where('id',$id)->delete();
                
                if ($res) {
                    // Hard delete data that related to user
                    InventoryModel::deleteInventoryByUserId($id);
                    InventoryLayoutModel::deleteInventoryLayoutByUserId($id);
                    ReportModel::deleteReportByUserId($id);
                    ReportItemModel::deleteReportItemByUserId($id);
                    HistoryModel::deleteHistoryByUserId($id);
                    ReminderModel::deleteReminderByUserId($id);
                    PersonalAccessToken::deletePersonalAccessTokenByUserId($id);

                    // Return success response
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
