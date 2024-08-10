<?php

namespace App\Http\Controllers\Api\InventoryApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\InventoryModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/inventory",
     *     summary="Get all inventory",
     *     tags={"Inventory"},
     *     @OA\Response(
     *         response=200,
     *         description="inventory fetched"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="inventory failed to fetched"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_all_inventory(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = InventoryModel::select('*')
                ->where('created_by',$user_id)
                ->orderBy('is_favorite', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory failed to fetched',
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

    /**
     * @OA\GET(
     *     path="/api/v1/inventory/list",
     *     summary="Get list inventory",
     *     tags={"Inventory"},
     *     @OA\Response(
     *         response=200,
     *         description="inventory fetched"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="inventory failed to fetched"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_list_inventory(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = InventoryModel::select('id','inventory_name','inventory_vol','inventory_unit')
                ->where('created_by',$user_id)
                ->whereNull('deleted_at')
                ->orderBy('inventory_name', 'asc')
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory failed to fetched',
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

    /**
     * @OA\GET(
     *     path="/api/v1/inventory/calendar",
     *     summary="Get inventory as calendar format",
     *     tags={"Inventory"},
     *     @OA\Response(
     *         response=200,
     *         description="inventory fetched"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="inventory failed to fetched"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_list_calendar(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = InventoryModel::select('inventory_name','inventory_price','created_at')
                ->where('created_by',$user_id)
                ->whereNull('deleted_at')
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory failed to fetched',
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
