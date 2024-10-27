<?php

namespace App\Http\Controllers\Api\StatsApi;

use App\Http\Controllers\Controller;
use App\Helpers\Generator;

// Models
use App\Models\InventoryModel;
use App\Models\ReportModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Queries extends Controller
{
    private function get_inventory_stats_view($type){
        if($type == "price"){
            return "CAST(SUM(inventory_price) as UNSIGNED)";
        } else if($type == "item") {
            return "COUNT(1)";
        }
    }
    /**
     * @OA\GET(
     *     path="/api/v1/stats/inventory/total_by_category/{type}",
     *     summary="Get total inventory by category",
     *     description="This request is used to get total inventory by its category. This request is using MySql database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"item", "price"},
     *             example="item"
     *         ),
     *         description="Stats inventory view type: either 'item' for count or 'price' for sum of prices",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Fashion"),
     *                          @OA\Property(property="total", type="integer", example=2)
     *                 )
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
     *         description="stats failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="stats not found")
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
    public function get_total_inventory_by_category(Request $request, $type)
    {
        try{
            $user_id = $request->user()->id;

            $res = InventoryModel::selectRaw("inventory_category as context, ".$this->get_inventory_stats_view($type)." as total")
                ->where('created_by', $user_id)
                ->groupby('inventory_category')
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'stats fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'stats failed to fetched',
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
     *     path="/api/v1/stats/inventory/total_by_favorite/{type}",
     *     summary="Get total inventory by favorite",
     *     description="This request is used to get total inventory by its favorite. This request is using MySql database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"item", "price"},
     *             example="item"
     *         ),
     *         description="Stats inventory view type: either 'item' for count or 'price' for sum of prices",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Favorite"),
     *                          @OA\Property(property="total", type="integer", example=2)
     *                 )
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
     *         description="stats failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="stats not found")
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
    public function get_total_inventory_by_favorite(Request $request, $type)
    {
        try{
            $user_id = $request->user()->id;

            $res = InventoryModel::selectRaw("
                    CASE 
                        WHEN is_favorite = 1 THEN 'Favorite' 
                        ELSE 'Normal Item' 
                    END AS context, 
                    ".$this->get_inventory_stats_view($type)." as total")
                ->where('created_by', $user_id)
                ->groupby('is_favorite')
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'stats fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'stats not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => $e->getMessage(),
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/stats/inventory/total_by_room/{type}",
     *     summary="Get total inventory by room",
     *     description="This request is used to get total inventory by its room. This request is using MySql database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"item", "price"},
     *             example="item"
     *         ),
     *         description="Stats inventory view type: either 'item' for count or 'price' for sum of prices",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Main Room"),
     *                          @OA\Property(property="total", type="integer", example=2)
     *                 )
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
     *         description="stats failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="stats not found")
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
    public function get_total_inventory_by_room(Request $request, $type)
    {
        try{
            $user_id = $request->user()->id;

            $res = InventoryModel::selectRaw("inventory_room as context, ".$this->get_inventory_stats_view($type)." as total")
                ->where('created_by', $user_id)
                ->groupby('inventory_room')
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'stats fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'stats not found',
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
     *     path="/api/v1/stats/report/total_created_per_month/{year}",
     *     summary="Get total report created per month",
     *     description="This request is used to get total report created per month by given `year`. This request is using MySql database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example="2024"
     *         ),
     *         description="Report created year",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Jan"),
     *                          @OA\Property(property="total_report", type="integer", example=3),
     *                          @OA\Property(property="total_item", type="integer", example=8)
     *                 )
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
     *         description="stats failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="stats not found")
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
    public function get_total_report_created_per_month(Request $request, $year)
    {
        try{
            $user_id = $request->user()->id;

            $res = ReportModel::selectRaw("COUNT(DISTINCT report.id) as total_report, CAST(SUM(item_qty) AS UNSIGNED) as total_item, MONTH(report.created_at) as context")
                ->join('report_item','report_item.report_id','=','report.id')
                ->where('report.created_by', $user_id)
                ->whereRaw("YEAR(report.created_at) = '$year'")
                ->groupByRaw('MONTH(report.created_at)')
                ->get();
            
            if (count($res) > 0) {
                $res_final = [];
                for ($i=1; $i <= 12; $i++) { 
                    $total_report = 0;
                    $total_item = 0;
                    foreach ($res as $idx => $val) {
                        if($i == $val->context){
                            $total_report = $val->total_report;
                            $total_item = $val->total_item;
                            break;
                        }
                    }
                    array_push($res_final, [
                        'context' => Generator::generateMonthName($i,'short'),
                        'total_report' => $total_report,
                        'total_item' => $total_item,
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'stats fetched',
                    'data' => $res_final
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'stats not found',
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
     *     path="/api/v1/stats/report/total_spending_per_month/{year}",
     *     summary="Get total report spending per month",
     *     description="This request is used to get total report created per month by given `year`. This request is using MySql database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example="2024"
     *         ),
     *         description="Report created year",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Jan"),
     *                          @OA\Property(property="total_price", type="integer", example=20000),
     *                          @OA\Property(property="total_item", type="integer", example=2),
     *                          @OA\Property(property="average_price_per_item", type="integer", example=10000),
     *                 )
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
     *         description="stats failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="stats not found")
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
    public function get_total_report_spending_per_month(Request $request, $year)
    {
        try{
            $user_id = $request->user()->id;

            $res = ReportModel::selectRaw("CAST(SUM(item_price) AS UNSIGNED) as total_price, CAST(SUM(item_qty) AS UNSIGNED) as total_item, MONTH(report.created_at) as context")
                ->join('report_item','report_item.report_id','=','report.id')
                ->where('report.created_by', $user_id)
                ->where('report_category','Shopping Cart')
                ->whereRaw("YEAR(report.created_at) = '$year'")
                ->groupByRaw('MONTH(report.created_at)')
                ->get();
            
            if (count($res) > 0) {
                $res_final = [];
                for ($i=1; $i <= 12; $i++) { 
                    $total_price = 0;
                    $total_item = 0;
                    foreach ($res as $idx => $val) {
                        if($i == $val->context){
                            $total_price = $val->total_price;
                            $total_item = $val->total_item;
                            break;
                        }
                    }
                    array_push($res_final, [
                        'context' => Generator::generateMonthName($i,'short'),
                        'total_price' => $total_price,
                        'total_item' => $total_item,
                        'average_price_per_item' => $total_item > 0 ? ceil($total_price / $total_item * 100) / 100 : 0
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'stats fetched',
                    'data' => $res_final
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'stats not found',
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
     *     path="/api/v1/stats/report/total_used_per_month/{year}",
     *     summary="Get total report spending per month",
     *     description="This request is used to get total report created per month by given `year`. This request is using MySql database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example="2024"
     *         ),
     *         description="Report created year",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Jan"),
     *                          @OA\Property(property="total_washlist", type="integer", example=14),
     *                          @OA\Property(property="total_checkout", type="integer", example=4),
     *                 )
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
     *         description="stats failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="stats not found")
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
    public function get_total_report_used_per_month(Request $request, $year){
        try{
            $user_id = $request->user()->id;

            $res = ReportModel::selectRaw("
                    SUM(CASE WHEN report_category = 'Checkout' THEN 1 ELSE 0 END) as total_checkout,
                    SUM(CASE WHEN report_category = 'Wash List' THEN 1 ELSE 0 END) as total_washlist,
                    MONTH(report.created_at) as context
                ")
                ->join('report_item', 'report_item.report_id', '=', 'report.id')
                ->where('report.created_by', $user_id)
                ->whereIn('report_category', ['Checkout', 'Wash List'])
                ->whereRaw("YEAR(report.created_at) = '$year'")
                ->groupByRaw('MONTH(report.created_at)')
                ->get();
            
            if (count($res) > 0) {
                $res_final = [];
                for ($i=1; $i <= 12; $i++) { 
                    $total_checkout = 0;
                    $total_washlist = 0;
                    foreach ($res as $idx => $val) {
                        if($i == $val->context){
                            $total_checkout = $val->total_checkout;
                            $total_washlist = $val->total_washlist;
                            break;
                        }
                    }
                    array_push($res_final, [
                        'context' => Generator::generateMonthName($i,'short'),
                        'total_checkout' => $total_checkout,
                        'total_washlist' => $total_washlist,
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'stats fetched',
                    'data' => $res_final
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'stats not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
