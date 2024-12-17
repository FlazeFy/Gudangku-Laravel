<?php

namespace App\Http\Controllers\Api\ReminderApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

// Models
use App\Models\ScheduleMarkModel;
use App\Models\AdminModel;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/reminder/mark",
     *     summary="Get all reminder scheduler mark",
     *     description="This request is used to get all executed reminder mark. This request is using MySql database, have a protected routes, and have template pagination.",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="reminder mark fetched",
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
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login | only admin can use this request")
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
    public function get_reminder_mark(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $res = ScheduleMarkModel::getAllReminderMark(true);
            $check_admin = AdminModel::find($user_id);
            
            if($check_admin){
                if ($res) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'reminder mark fetched',
                        'data' => $res
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'reminder mark not found',
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'only admin can use this request',
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}