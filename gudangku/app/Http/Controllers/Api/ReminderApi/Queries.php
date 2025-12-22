<?php

namespace App\Http\Controllers\Api\ReminderApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\ScheduleMarkModel;
use App\Models\AdminModel;
// Helper
use App\Helpers\Generator;

class Queries extends Controller
{
    private $module;

    public function __construct()
    {
        $this->module = "reminder mark";
    }

    /**
     * @OA\GET(
     *     path="/api/v1/reminder/mark",
     *     summary="Get All Reminder Scheduler Mark",
     *     description="This request is used to get all executed reminder mark. This request interacts with the MySQL database, has protected routes, and has a pagination",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Reminder mark fetched successfully. Ordered in descending order by `last_execute`",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="reminder mark fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="inventory_name", type="string", example="Skintific Mugwort Clay Mask"),
     *                         @OA\Property(property="inventory_category", type="string", example="Skin & Body Care"),
     *                         @OA\Property(property="reminder_desc", type="string", example="Restock at https://tokopedia.link/rBfBm3vVDIb\r\nBeli 2 boleh"),
     *                         @OA\Property(property="reminder_type", type="string", example="Every Month"),
     *                         @OA\Property(property="reminder_context", type="string", example="Every 19"),
     *                         @OA\Property(property="last_executed", type="string", format="date-time", example="2024-09-20 22:53:47"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47"),
     *                         @OA\Property(property="username", type="string", example="flazefy")
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
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login | permission denied. only admin can use this request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="reminder mark failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="reminder mark not found")
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
    public function getReminderMark(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            
            // Make sure only admin can access the request
            $check_admin = AdminModel::find($user_id);
            if($check_admin){
                // Get all reminder mark with pagination
                $res = ScheduleMarkModel::getAllReminderMark(true);
                if($res->count() > 0) {
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
     *     path="/api/v1/reminder/history",
     *     summary="Get All Reminder Scheduler History",
     *     description="This request is used to get all executed reminder history. This request interacts with the MySql database, has protected routes, and has a pagination.",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Reminder history fetched successfully. Ordered in descending order by `last_execute`",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="reminder history fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
     *                         @OA\Property(property="inventory_name", type="string", example="Skintific Mugwort Clay Mask"),
     *                         @OA\Property(property="reminder_desc", type="string", example="Restock at https://tokopedia.link/rBfBm3vVDIb\r\nBeli 2 boleh"),
     *                         @OA\Property(property="reminder_type", type="string", example="Every Month"),
     *                         @OA\Property(property="reminder_context", type="string", example="Every 19"),
     *                         @OA\Property(property="last_executed", type="string", format="date-time", example="2024-09-20 22:53:47")
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
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login | only admin can use this request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="reminder history failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="reminder history not found")
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
    public function getReminderHistory(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            // Get all reminder history
            $res = ScheduleMarkModel::getAllReminderHistory($user_id,true);
            if($res->isNotEmpty()) {
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
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
