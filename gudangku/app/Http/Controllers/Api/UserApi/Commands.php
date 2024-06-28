<?php

namespace App\Http\Controllers\Api\UserApi;

use App\Http\Controllers\Controller;

use App\Helpers\Generator;

// Models
use App\Models\UserModel;
use App\Models\ValidateRequestModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Telegram\Bot\Laravel\Facades\Telegram;

use App\Jobs\UserMailer;
use Illuminate\Support\Facades\Mail;

class Commands extends Controller
{
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
                'message' => 'something wrong. Please contact admin '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_register_validation_token(Request $request)
    {
        try{
            $username = $request->username;
            $valid = ValidateRequestModel::selectRaw('*')
                ->where('request_type','register')
                ->where('request_context',$username)
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
                        'message' => 'something wrong. Please contact admin',
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'there already a request with same username',
                ], Response::HTTP_CONFLICT);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
