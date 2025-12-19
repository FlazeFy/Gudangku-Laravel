<?php

namespace App\Http\Controllers\Api\ErrorApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\ErrorModel;
use App\Models\AdminModel;

class Queries extends Controller
{
    private $module;
    public function __construct()
    {
        $this->module = "error";
    }
    
    /**
     * @OA\GET(
     *     path="/api/v1/error",
     *     summary="Get All Error",
     *     description="This request is used to get all error audit. This request interacts with the MySQL database, has a protected routes (Admin only), and a pagination.",
     *     tags={"Error"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Error fetched successfully. Ordered in ascending order by `created_at`",
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
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login | permission denied. only admin can use this feature")
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
    public function getAllError(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $paginate = $request->query('per_page_key') ?? 12;

            // Make sure only admin can access the request
            $check_admin = AdminModel::find($user_id);
            if($check_admin){
                // Get all error
                $res = ErrorModel::getAllError($paginate);
                if (count($res) > 0) {
                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("fetch", $this->module),
                        'data' => $res
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", $this->module),
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("custom", "you dont have permission to access the $this->module data"),
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
