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
     *     description="This request is used to get all inventory data. This request is using MySql database, has protected routes, and supports pagination.",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="inventory fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="inventory fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
     *                         @OA\Property(property="inventory_name", type="string", example="Nike Air Force 1 High By You"),
     *                         @OA\Property(property="inventory_category", type="string", example="Fashion"),
     *                         @OA\Property(property="inventory_desc", type="string", example="Sepatu High"),
     *                         @OA\Property(property="inventory_merk", type="string", example="Nike"),
     *                         @OA\Property(property="inventory_room", type="string", example="Main Room"),
     *                         @OA\Property(property="inventory_storage", type="string", example="Wardrobe"),
     *                         @OA\Property(property="inventory_rack", type="string", example="Shoes & Sandals - Bottom"),
     *                         @OA\Property(property="inventory_price", type="number", example=2249000),
     *                         @OA\Property(property="inventory_image", type="string", example="https://example.com/inventory/image.jpg"),
     *                         @OA\Property(property="inventory_unit", type="string", example="Pcs"),
     *                         @OA\Property(property="inventory_vol", type="integer", example=1),
     *                         @OA\Property(property="inventory_capacity_unit", type="string", example="percentage"),
     *                         @OA\Property(property="inventory_capacity_vol", type="integer", example=80),
     *                         @OA\Property(property="inventory_color", type="string", example="Black"),
     *                         @OA\Property(property="is_favorite", type="integer", example=1),
     *                         @OA\Property(property="is_reminder", type="integer", example=0),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-19 02:37:58"),
     *                         @OA\Property(property="created_by", type="string", example="2d98f524-de02-11ed-b5ea-0242ac120002"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-07-18 15:50:18"),
     *                         @OA\Property(property="deleted_at", type="string", format="date-time", example="2024-05-02 03:32:31")
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
     *         description="inventory failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="inventory not found")
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
                    'message' => 'inventory not found',
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
     *     path="/api/v1/inventory/list",
     *     summary="Get list inventory",
     *     description="This request is used to get all inventory data but in shot format for selection. This request is using MySql database, and has protected routes",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="inventory fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="inventory fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
     *                      @OA\Property(property="inventory_name", type="string", example="Nike Air Force 1 High By You"),
     *                      @OA\Property(property="inventory_unit", type="string", example="Pcs"),
     *                      @OA\Property(property="inventory_vol", type="integer", example=1),
     *                  )
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
     *         description="inventory failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="inventory not found")
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
                    'message' => 'inventory not found',
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
     *     path="/api/v1/inventory/calendar",
     *     summary="Get inventory as calendar format",
     *     description="This request is used to get all inventory data but in calendar format. This request is using MySql database, and has protected routes",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="inventory fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="inventory fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="inventory_name", type="string", example="Nike Air Force 1 High By You"),
     *                      @OA\Property(property="inventory_price", type="number", example=2249000),
     *                      @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-19 02:37:58")
     *                  )
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
     *         description="inventory failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="inventory not found")
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
                    'message' => 'inventory not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
