<?php

namespace App\Http\Controllers\Api\UserApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\UserModel;
use App\Models\AdminModel;
use App\Models\ValidateRequestModel;

// Helper
use App\Helpers\Generator;

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

            $res = UserModel::getUserById($user_id); // with admin too
            $validation_telegram = ValidateRequestModel::getActiveRequest($user_id);
            
            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'user'),
                    'data' => $res,
                    'telegram_data' => $validation_telegram,
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'user'),
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
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login | permission denied. only admin can use this request")
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
            $paginate = $request->query('per_page_key') ?? 12;

            if($check_admin){
                $res = UserModel::getAllUser($paginate);
                
                if ($res) {
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("fetch", 'user'),
                        'data' => $res,
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

    /**
     * @OA\GET(
     *     path="/api/v1/user/my_year",
     *     summary="Get My Content's Year",
     *     description="This request is used to get all available year to selected in filter based on created date inventory or report. This request is using MySql database, and have a protected routes",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="user year fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="user year fetched"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                      @OA\Property(property="year", type="integer", example=2023),
     *                 )
     *             ),
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
     *         description="user year failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="user year not found")
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
    public function get_content_year(Request $request){
        try{
            $user_id = $request->user()->id;
            $check_admin = AdminModel::find($user_id);
            $res = UserModel::getAvailableYear($check_admin ? null : $user_id, $check_admin ? true : false);

            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'user year'),
                    'data' => $res,
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'user year'),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
