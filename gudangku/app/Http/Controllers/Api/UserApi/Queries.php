<?php

namespace App\Http\Controllers\Api\UserApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\UserModel;
use App\Models\AdminModel;
use App\Models\ValidateRequestModel;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/user/my_profile",
     *     summary="Get my profile",
     *     description="This request is used to get user profile info. This request is using MySql database, and have a protected routes",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="user fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="user fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                 @OA\Property(property="username", type="string", example="flazefy"),
     *                 @OA\Property(property="email", type="string", example="flazen.edu@gmail.com"),
     *                 @OA\Property(property="role", type="string", example="user"),
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
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="user failed to fetched",
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
    public function get_my_profile(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = UserModel::select('id','username','email','telegram_user_id','telegram_is_valid','created_at')
                ->where('id',$user_id)
                ->first();
            if($res){
                $res->role = 'user';
            }
            if(!$res){
                $res = AdminModel::select('id','username','email','telegram_user_id','telegram_is_valid','created_at')
                    ->where('id',$user_id)
                    ->first();
                if($res){
                    $res->role = 'admin';
                }
            }

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
                    'message' => 'user not found',
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
     * @OA\GET(
     *     path="/api/v1/user",
     *     summary="Get all user",
     *     description="This request is used to get all user. This endpoint for Admin only. This request is using MySql database, and have a protected routes",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="user fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="user fetched"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                      @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                      @OA\Property(property="username", type="string", example="flazefy"),
     *                      @OA\Property(property="email", type="string", example="flazen.edu@gmail.com"),
     *                      @OA\Property(property="role", type="string", example="user"),
     *                      @OA\Property(property="telegram_user_id", type="string", example="1317625970"),
     *                      @OA\Property(property="telegram_is_valid", type="integer", example=1),
     *                      @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47")
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="user failed to fetched",
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
    public function get_all_user(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $check_admin = AdminModel::find($user_id);

            if($check_admin){
                $res = UserModel::selectRaw('*')
                    ->orderby('created_at','desc')
                    ->paginate(12);
                
                if ($res) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'user fetched',
                        'data' => $res,
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'user not found',
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'only admin can use this request',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
