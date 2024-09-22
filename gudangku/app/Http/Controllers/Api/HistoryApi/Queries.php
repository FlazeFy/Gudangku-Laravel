<?php

namespace App\Http\Controllers\Api\HistoryApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\HistoryModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/history",
     *     summary="Get all history",
     *     description="This request is used to get all history when user use the App. This request is using MySql database, have a protected routes, and have template pagination.",
     *     tags={"History"},
     *     @OA\Response(
     *         response=200,
     *         description="history fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="history fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
     *                         @OA\Property(property="history_type", type="string", example="Create"),
     *                         @OA\Property(property="history_context", type="string", example="Barang bawaan"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47"),
     *                         @OA\Property(property="created_by", type="string", example="2d98f524-de02-11ed-b5ea-0242ac120002")
     *                     )
     *                 ),
     *             )
     *         )
     *     ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="history failed to fetched"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_all_history(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = HistoryModel::select('*')
                ->where('created_by',$user_id)
                ->orderby('created_at', 'DESC')
                ->paginate(12);
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'history fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'history failed to fetched',
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
