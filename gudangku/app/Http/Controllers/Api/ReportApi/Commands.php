<?php

namespace App\Http\Controllers\Api\ReportApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\ReportItemModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Commands extends Controller
{
    /**
     * @OA\DELETE(
     *     path="/api/v1/report/delete/item/{id}",
     *     summary="Hard delete report item by id",
     *     tags={"Report"},
     *     @OA\Response(
     *         response=200,
     *         description="report item deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="report item failed to deleted"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function hard_delete_report_item_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $rows = ReportItemModel::where('id', $id)
                ->where('created_by', $user_id)
                ->delete();

            if($rows > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => 'report item deleted',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'report item failed to deleted',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin'.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
