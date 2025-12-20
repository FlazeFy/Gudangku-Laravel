<?php

namespace App\Http\Controllers\Api\StatsApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Helpers\Generator;
use Illuminate\Support\Facades\Auth;

// Models
use App\Models\InventoryModel;
use App\Models\ReportModel;
use App\Models\AdminModel;
use App\Models\UserModel;
use App\Models\HistoryModel;

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
    public function getTotalInventoryByCategory(Request $request, $type)
    {
        try{
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;

                $check_admin = AdminModel::find($user_id);
                if($check_admin){
                    $user_id = $request->query('user_id') ?? null;
                } 
            } else {
                $user_id = null;
            }

            $res = InventoryModel::getContextTotalStats('inventory_category',$type,$user_id);
            
            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
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
     *     path="/api/v1/stats/inventory/category/most_expensive",
     *     summary="Get most expensive inventory per context",
     *     description="This request is used to get most expensive inventory for each category. This request is using MySql database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Fashion (Shirt A)"),
     *                          @OA\Property(property="total", type="integer", example=200000)
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
    public function getMostExpensiveInventoryPerContext(Request $request, $context){
        try {
            $contexts = explode(',', $context); 
            foreach ($contexts as $ctx) {
                if (!in_array($ctx, ['inventory_category', 'inventory_merk', 'inventory_room', 'inventory_storage'])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => Generator::getMessageTemplate("validation_failed", 'context must be inventory_category, inventory_merk, inventory_room, or inventory_storage'),
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            $user_id = $request->user()->id;
            $check_admin = AdminModel::find($user_id);

            if (count($contexts) === 1) {
                $res = InventoryModel::getMostExpensiveInventoryPerContext(!$check_admin ? $user_id : null, $contexts[0]);
                if ($res) {
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("fetch", 'stats'),
                        'data' => $res
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'stats'),
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                $result = [];
                foreach ($contexts as $ctx) {
                    $res = InventoryModel::getMostExpensiveInventoryPerContext(!$check_admin ? $user_id : null, $ctx);
                    $result[$ctx] = $res ? $res : null;
                }

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $result
                ], Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
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
    public function getTotalInventoryByFavorite(Request $request, $type)
    {
        try{
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;

                $check_admin = AdminModel::find($user_id);
                if($check_admin){
                    $user_id = $request->query('user_id') ?? null;
                } 
            } else {
                $check_admin = null;
                $user_id = null;
            }

            $res = InventoryModel::selectRaw("
                    CASE 
                        WHEN is_favorite = 1 THEN 'Favorite' 
                        ELSE 'Normal Item' 
                    END AS context, 
                    ".$this->get_inventory_stats_view($type)." as total");
            if(!$check_admin && $user_id){
                $res->where('created_by',$user_id);
            }
            $res = $res->groupby('is_favorite')
                ->orderby('total','desc')
                ->limit(7)
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
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
    public function getTotalInventoryByRoom(Request $request, $type)
    {
        try{
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;

                $check_admin = AdminModel::find($user_id);
                if($check_admin){
                    $user_id = $request->query('user_id') ?? null;
                } 
            } else {
                $user_id = null;
            }

            $res = InventoryModel::getContextTotalStats('inventory_room',$type,$user_id);
            
            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
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
     *     path="/api/v1/stats/inventory/total_by_merk/{type}",
     *     summary="Get total inventory by merk",
     *     description="This request is used to get total inventory by its merk. This request is using MySql database, and have a protected routes.",
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
    public function getTotalInventoryByMerk(Request $request, $type)
    {
        try{
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;
            } else {
                $user_id = null;
            } 

            $res = InventoryModel::getContextTotalStats('inventory_merk',$type,$user_id);
            
            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
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
    public function getTotalReportCreatedPerMonth(Request $request, $year)
    {
        try{
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;

                $check_admin = AdminModel::find($user_id);
                if($check_admin){
                    $user_id = $request->query('user_id') ?? null;
                } 
            } else {
                $check_admin = null;
                $user_id = null;
            }

            $res = ReportModel::getTotalReportCreatedOrSpendingPerMonth($user_id, $year, $check_admin ? true : false, 'created');
            
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
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res_final
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
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
     *     path="/api/v1/stats/inventory/total_created_per_month/{year}",
     *     summary="Get total inventory created per month",
     *     description="This request is used to get total inventory created per month by given `year`. This request is using MySql database, and have a protected routes.",
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
     *         description="Inventory created year",
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
     *                          @OA\Property(property="total", type="integer", example=3)
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
    public function getTotalInventoryCreatedPerMonth(Request $request, $year)
    {
        try{
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;

                $check_admin = AdminModel::find($user_id);
                if($check_admin){
                    $user_id = $request->query('user_id') ?? null;
                } 
            } else {
                $check_admin = null;
                $user_id = null;
            }

            $res = InventoryModel::getTotalInventoryCreatedPerMonth($user_id, $year, $check_admin ? true : false);
            
            if (count($res) > 0) {
                $res_final = [];
                for ($i=1; $i <= 12; $i++) { 
                    $total = 0;
                    foreach ($res as $idx => $val) {
                        if($i == $val->context){
                            $total = $val->total;
                            break;
                        }
                    }
                    array_push($res_final, [
                        'context' => Generator::generateMonthName($i,'short'),
                        'total' => $total,
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res_final
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
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
     *     path="/api/v1/stats/inventory/favorite_inventory_comparison",
     *     summary="Get total inventory favorited comparison",
     *     description="This request is used to get total inventory comparison by if its favorited or not. This request is using MySql database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Jan"),
     *                          @OA\Property(property="total", type="integer", example=3)
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
    public function getTotalFavoriteInventoryComparison(Request $request)
    {
        try{
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;

                $check_admin = AdminModel::find($user_id);
                if($check_admin){
                    $user_id = $request->query('user_id') ?? null;
                } 
            } else {
                $user_id = null;
            }

            $total_item = InventoryModel::getTotalInventory($user_id,'item');
            $total_item = $total_item->total;
            
            if ($total_item > 0) {
                $total_fav = InventoryModel::getTotalInventory($user_id,'favorite');
                $total_fav = $total_fav->total;

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => [
                        [ 'context' => 'Favorited', 'total' => $total_fav ],
                        [ 'context' => 'Normal Inventory', 'total' => $total_item ]
                    ]
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
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
     *     path="/api/v1/stats/inventory/low_capacity_inventory_comparison",
     *     summary="Get total inventory low capacity comparison",
     *     description="This request is used to get total inventory comparison by if its low capacity or not. This request is using MySql database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Jan"),
     *                          @OA\Property(property="total", type="integer", example=3)
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
    public function getTotalLowCapacityInventoryComparison(Request $request)
    {
        try{
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;

                $check_admin = AdminModel::find($user_id);
                if($check_admin){
                    $user_id = $request->query('user_id') ?? null;
                } 
            } else {
                $user_id = null;
            }

            $total_item = InventoryModel::getTotalInventory($user_id,'item');
            $total_item = $total_item->total;
            
            if ($total_item > 0) {
                $total_low = InventoryModel::getTotalInventory($user_id,'low');
                $total_low = $total_low->total;

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => [
                        [ 'context' => 'Low Capacity', 'total' => $total_low ],
                        [ 'context' => 'Normal Capacity', 'total' => $total_item ]
                    ]
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
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
     *     path="/api/v1/stats/history/total_activity_per_month/{year}",
     *     summary="Get total activity per month",
     *     description="This request is used to get total activity per month by given `year`. This request is using MySql database, and have a protected routes.",
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
     *         description="Activity created year",
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
     *                          @OA\Property(property="total", type="integer", example=3)
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
    public function getTotalActivityPerMonth(Request $request, $year)
    {
        try{
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;

                $check_admin = AdminModel::find($user_id);
                if($check_admin){
                    $user_id = $request->query('user_id') ?? null;
                } 
            } else {
                $user_id = null;
            }

            $res = HistoryModel::getTotalActivityPerMonth($user_id, $year);
            
            if (count($res) > 0) {
                $res_final = [];
                for ($i=1; $i <= 12; $i++) { 
                    $total = 0;
                    foreach ($res as $idx => $val) {
                        if($i == $val->context){
                            $total = $val->total;
                            break;
                        }
                    }
                    array_push($res_final, [
                        'context' => Generator::generateMonthName($i,'short'),
                        'total' => $total,
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res_final
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getInventoryTreeMap(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = InventoryModel::getInventoryTreeMap($user_id);
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
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
     *     path="/api/v1/stats/user/last_login",
     *     summary="Get last user login",
     *     description="This request is used to get list of last user login. This request is using MySql database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="username", type="string", example="Jan"),
     *                          @OA\Property(property="login_at", type="string", format="date-time", example="2024-03-14 02:28:37")
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
    public function getLastLoginUser(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $limit = $request->query('limit') ?? 7;

            $check_admin = AdminModel::find($user_id);
            if($check_admin){
                $res = UserModel::getLastLoginUser($limit);

                if($res){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("fetch", 'stats'),
                        'data' => $res
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'stats'),
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
     *     path="/api/v1/stats/user/leaderboard",
     *     summary="Get user leaderboard",
     *     description="This request is used to get user leaderboard. This request is using MySql database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="user_with_most_inventory", type="object", nullable=true,
     *                     @OA\Property(property="username", type="string", example="John Doe"),
     *                     @OA\Property(property="total", type="integer", example=15)
     *                 ),
     *                 @OA\Property(
     *                     property="user_with_most_report", type="object", nullable=true,
     *                     @OA\Property(property="username", type="string", example="Jane Smith"),
     *                     @OA\Property(property="total", type="integer", example=22)
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
    public function getLeaderboard(Request $request){
        try{
            $user_id = $request->user()->id;
            $limit = $request->query('limit') ?? 7;

            $check_admin = AdminModel::find($user_id);
            if($check_admin){
                $res_inventory = UserModel::getUserWithMostContext('inventory');
                $res_report = UserModel::getUserWithMostContext('report');

                if($res_inventory || $res_report){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("fetch", 'stats'),
                        'data' => [
                            'user_with_most_inventory' => $res_inventory,
                            'user_with_most_report' => $res_report
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'stats'),
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
    public function getTotalReportSpendingPerMonth(Request $request, $year)
    {
        try{
            $user_id = $request->user()->id;
            $check_admin = AdminModel::find($user_id);

            $res = ReportModel::getTotalReportCreatedOrSpendingPerMonth($check_admin ? null : $user_id, $year, $check_admin ? true : false, 'spending');
            
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
                        'average_price_per_item' => $total_item > 0 ? (int)ceil($total_price / $total_item * 100) / 100 : 0
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res_final
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
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
    public function getTotalReportUsedPerMonth(Request $request, $year){
        try{
            $user_id = $request->user()->id;
            $check_admin = AdminModel::find($user_id);

            $res = ReportModel::getTotalReportUsedPerMonth($check_admin ? null : $user_id, $year, $check_admin ? true : false);
            
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
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => $res_final
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
                ], Response::HTTP_NOT_FOUND);
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
     *     path="/api/v1/stats/dashboard",
     *     summary="Get dashboard",
     *     description="This request is used to get inventory dashboard. This request is using MySql database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_item", type="integer", example=14),
     *                 @OA\Property(property="total_fav", type="integer", example=4),
     *                 @OA\Property(property="total_low", type="integer", example=0),
     *                 @OA\Property(property="last_added", type="string", example="New Balance"),
     *                 @OA\Property(property="most_category", type="object",
     *                     @OA\Property(property="context", type="string", example="Skin & Body Care"),
     *                     @OA\Property(property="total", type="integer", example=8)
     *                 ),
     *                 @OA\Property(property="highest_price", type="object",
     *                     @OA\Property(property="inventory_name", type="string", example="New Balance"),
     *                     @OA\Property(property="inventory_price", type="integer", example=2249000)
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
    public function getDashboard(Request $request){
        try{
            $user_id = $request->user()->id;

            $check_admin = AdminModel::find($user_id);
            if($check_admin){
                $user_id = $request->query('user_id') ?? null;
            } 

            $total_item = InventoryModel::getTotalInventory($user_id,'item');
            $total_fav = InventoryModel::getTotalInventory($user_id,'favorite');
            $total_low = InventoryModel::getTotalInventory($user_id,'low');
            $last_added = InventoryModel::getLastAddedInventory($user_id);
            $most_category = InventoryModel::getMostCategoryInventory($user_id);
            $highest_price = InventoryModel::getHighestPriceInventory($user_id);

            if($total_item && $total_item->total != 0){
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'stats'),
                    'data' => [
                        'total_item' => $total_item ? $total_item->total : null,
                        'total_fav' => $total_fav ? $total_fav->total : null,
                        'total_low' => $total_low ? $total_low->total : null,
                        'last_added' => $last_added ? $last_added->inventory_name : null,
                        'most_category' => $most_category,
                        'highest_price' => $highest_price
                    ]
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'stats'),
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
