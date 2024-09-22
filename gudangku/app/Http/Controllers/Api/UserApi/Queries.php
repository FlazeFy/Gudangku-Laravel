<?php

namespace App\Http\Controllers\Api\UserApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\UserModel;
use App\Models\ValidateRequestModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/user/my_profile",
     *     summary="Get my profile",
     *     description="This request is used to get user profile info. This request is using MySql database, and have a protected routes",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="user fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="user fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="username", type="string", example="flazefy"),
     *                 @OA\Property(property="email", type="string", example="flazen.edu@gmail.com"),
     *                 @OA\Property(property="telegram_user_id", type="string", example="1317625970"),
     *                 @OA\Property(property="telegram_is_valid", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47")
     *             ),
     *             @OA\Property(property="telegram_data", type="object", 
     *                 @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
     *                 @OA\Property(property="request_type", type="string", example="register"),
     *                 @OA\Property(property="request_context", type="string", example="OUW36L"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="user failed to fetched"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_my_profile(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = UserModel::select('username','email','telegram_user_id','telegram_is_valid','created_at')
                ->where('id',$user_id)
                ->first();

            $validation_telegram = ValidateRequestModel::getActiveRequest($user_id);
            
            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'user fetched',
                    'data' => $res,
                    'telegram_data' => $validation_telegram
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'user failed to fetched',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
