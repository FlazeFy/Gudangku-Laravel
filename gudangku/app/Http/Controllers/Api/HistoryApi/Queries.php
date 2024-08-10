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
     *     tags={"History"},
     *     @OA\Response(
     *         response=200,
     *         description="history fetched"
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
                ->paginate(15);
            
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
