<?php

namespace App\Http\Controllers\Api\ErrorApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\ErrorModel;
use App\Models\AdminModel;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/error",
     *     summary="Get all error history",
     *     description="This request is used to get all error history recorded. This request is using MySql database, have a protected routes, and have template pagination.",
     *     tags={"Error"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="history fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="error history fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", example=25),
     *                         @OA\Property(property="message", type="string", example="count(): Argument #1 ($value) must be of type Countable|array, null given"),
     *                         @OA\Property(property="stack_trace", type="string", example="... require_once('/Users/leonardh...')\n#41 {main}"),
     *                         @OA\Property(property="file", type="string", example="ErrorApi/Queries.php"),
     *                         @OA\Property(property="line", type="number", example=20),
     *                         @OA\Property(property="is_fixed", type="boolean", example="0"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47"),
     *                         @OA\Property(property="faced_by", type="string", example="2d98f524-de02-11ed-b5ea-0242ac120002")
     *                     )
     *                 ),
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
     *         description="history failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="error history not found")
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
    public function get_all_error(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $res = ErrorModel::getAllError();
            $check_admin = AdminModel::find($user_id);
            
            if($check_admin){
                if ($res) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'error history fetched',
                        'data' => $res
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'error history not found',
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
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
