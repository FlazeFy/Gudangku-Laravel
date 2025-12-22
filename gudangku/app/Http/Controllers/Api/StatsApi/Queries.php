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
    /**
     * @OA\GET(
     *     path="/api/v1/stats/inventory/total_by_category/{type}",
     *     summary="Get Total Inventory By Category",
     *     description="This request is used to get total inventory by its category. This request interacts with the MySQL database, and have a protected routes.",
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
     *         description="stats fetched successfully. Ordered in descending order by `total`",
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
            // Define user id by role
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

            // Get context total stats
            $res = InventoryModel::getContextTotalStats('inventory_category',$type,$user_id);
            if ($res) {
                // Return success response
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
     *     path="/api/v1/stats/inventory/category/most_expensive/{context}",
     *     summary="Get most expensive inventory per context",
     *     description="This request is used to get most expensive inventory for each category. This request interacts with the MySQL database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="stats fetched successfully. Ordered in descending order by `total`",
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
            $user_id = $request->user()->id;

            // Validate context
            $contexts = explode(',', $context); 
            foreach ($contexts as $ctx) {
                if (!in_array($ctx, ['inventory_category', 'inventory_merk', 'inventory_room', 'inventory_storage'])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => Generator::getMessageTemplate("validation_failed", 'context must be inventory_category, inventory_merk, inventory_room, or inventory_storage'),
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            // Define user id by role
            $check_admin = AdminModel::find($user_id);
            if (count($contexts) === 1) {
                // Get context total stats
                $res = InventoryModel::getMostExpensiveInventoryPerContext(!$check_admin ? $user_id : null, $contexts[0]);
                if ($res) {
                    // Return success response
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
                // Get context total stats
                foreach ($contexts as $ctx) {
                    $res = InventoryModel::getMostExpensiveInventoryPerContext(!$check_admin ? $user_id : null, $ctx);
                    $result[$ctx] = $res ? $res : null;
                }

                // Return success response
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
     *     description="This request is used to get total inventory by its favorite. This request interacts with the MySQL database, and have a protected routes.",
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
     *         description="stats fetched successfully. Ordered in descending order by `total`",
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
            // Define user id by role
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

            // Get total inventory comparsion between favorite or not
            $res = InventoryModel::getTotalInventoryByFavorite($user_id,$type);
            if (count($res) > 0) {
                // Return success response
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
     *     summary="Get Total Inventory By Room",
     *     description="This request is used to get total inventory by its room. This request interacts with the MySQL database, and have a protected routes.",
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
     *         description="stats fetched successfully. Ordered in descending order by `total`",
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
            // Define user id by role
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

            // Get context total stats
            $res = InventoryModel::getContextTotalStats('inventory_room',$type,$user_id);
            if ($res) {
                // Return success response
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
     *     summary="Get Total Inventory By Merk",
     *     description="This request is used to get total inventory by its merk. This request interacts with the MySQL database, and have a protected routes.",
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
     *         description="stats fetched successfully. Ordered in descending order by `total`",
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
            // Define user id by role
            if ($request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user(); 
                $user_id = $user ? $user->id : null;
            } else {
                $user_id = null;
            } 

            // Get context total stats
            $res = InventoryModel::getContextTotalStats('inventory_merk',$type,$user_id);
            if ($res) {
                // Return success response
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
     *     summary="Get Total Report Created Per Month",
     *     description="This request is used to get total report created per month by given `year`. This request interacts with the MySQL database, and have a protected routes.",
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
     *         description="stats fetched successfully. Ordered in ascending order by `context` (month order format)",
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
            // Define user id by role
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

            // Get total report created / spending monthly
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

                // Return success response
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
     *     summary="Get Total Inventory Created Per Month",
     *     description="This request is used to get total inventory created per month by given `year`. This request interacts with the MySQL database, and have a protected routes.",
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
     *         description="stats fetched successfully. Ordered in ascending order by `context` (month order format)",
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
            // Define user id by role
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

            // Get total inventory created monthly
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

                // Return success response
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
     *     summary="Get Total Inventory Favorited Comparison",
     *     description="This request is used to get total inventory comparison by if its favorited or not. This request interacts with the MySQL database, and have a protected routes.",
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
            // Define user id by role
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

            // Get total inventory
            $total_item = InventoryModel::getTotalInventory($user_id,'item');
            $total_item = $total_item->total;
            
            if ($total_item > 0) {
                // Get total inventory
                $total_fav = InventoryModel::getTotalInventory($user_id,'favorite');
                $total_fav = $total_fav->total;

                // Return success response
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
     *     summary="Get Total Inventory Low Capacity Comparison",
     *     description="This request is used to get total inventory comparison by if its low capacity or not. This request interacts with the MySQL database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Stats fetched successfully",
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
            // Define user id by role
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

            // Get total inventory
            $total_item = InventoryModel::getTotalInventory($user_id,'item');
            $total_item = $total_item->total;
            
            if ($total_item > 0) {
                // Get total inventory
                $total_low = InventoryModel::getTotalInventory($user_id,'low');
                $total_low = $total_low->total;

                // Return success response
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
     *     summary="Get Total Activity Per Month",
     *     description="This request is used to get total activity per month by given `year`. This request interacts with the MySQL database, and have a protected routes.",
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
     *         description="Stats fetched successfully. Ordered in ascending order by `context` (month order format)",
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
            // Define user id by role
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

            // Get total history monthly
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

                // Return success response
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
     *     path="/api/v1/stats/inventory/tree_map",
     *     summary="Get Inventory Tree Map",
     *     description="This request is used to get inventory mapping in tree map format. This request interacts with the MySQL database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Stats fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="stats fetched"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="room_ddd072238abb3528f043222882b92c03"),
     *                     @OA\Property(property="name", type="string", example="Main Room"),
     *                     @OA\Property(property="children", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="string", example="storage_d399fd2dbae5d34c249a34287dfcb9bb"),
     *                             @OA\Property(property="name", type="string", example="Wardrobe"),
     *                             @OA\Property(property="children", type="array",
     *                                 @OA\Items(
     *                                     @OA\Property(property="id", type="string", example="rack_f8129906877dbc7b50bf30bc79a2129d"),
     *                                     @OA\Property(property="name", type="string", example="Shoes & Sandals - Bottom"),
     *                                     @OA\Property(property="children", type="array",
     *                                         @OA\Items(
     *                                             @OA\Property(property="id", type="string", example="item_387722ed612ed175f47cc906e0e1fdf9"),
     *                                             @OA\Property(property="name", type="string", example="New Balance2112")
     *                                         )
     *                                     )
     *                                 )
     *                             )
     *                         )
     *                     )
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
    public function getInventoryTreeMap(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            // Get inventory tree map format
            $res = InventoryModel::getInventoryTreeMap($user_id);
            if (count($res) > 0) {
                // Return success response
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
     *     summary="Get Last User Login",
     *     description="This request is used to get list of last user login. This request interacts with the MySQL database, and have a protected routes.",
     *     tags={"Stats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Stats fetched successfully. Ordered in descending order by `login_at`",
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

            // Make sure only admin can access this request
            $check_admin = AdminModel::find($user_id);
            if($check_admin){
                // Get last login user
                $res = UserModel::getLastLoginUser($limit);

                if($res){
                    // Return success response
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
     *     summary="Get User Leaderboard",
     *     description="This request is used to get user leaderboard. This request interacts with the MySQL database, and have a protected routes.",
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

            // Make sure only admin can access this request
            $check_admin = AdminModel::find($user_id);
            if($check_admin){
                // Get user with most context
                $res_inventory = UserModel::getUserWithMostContext('inventory');
                $res_report = UserModel::getUserWithMostContext('report');

                if($res_inventory || $res_report){
                    // Return success response
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
     *     summary="Get Total Report Spending Per Month",
     *     description="This request is used to get total report created per month by given `year`. This request interacts with the MySQL database, and have a protected routes.",
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
     *         description="Stats fetched successfully. Ordered in ascending order by `context` (month order format)",
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

            // Define user id by role
            $check_admin = AdminModel::find($user_id);
            $user_id = $check_admin ? null : $user_id;

            // Get total report created / spending monthly
            $res = ReportModel::getTotalReportCreatedOrSpendingPerMonth($user_id, $year, $check_admin ? true : false, 'spending');
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

                // Return success response
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
     *     summary="Get Total Report Spending Per Month",
     *     description="This request is used to get total report created per month by given `year`. This request interacts with the MySQL database, and have a protected routes.",
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
     *         description="Stats fetched successfully. Ordered in ascending order by `context` (month order format)",
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

            // Define user id by role
            $check_admin = AdminModel::find($user_id);
            $user_id = $check_admin ? null : $user_id;

            // Get total report used monthly
            $res = ReportModel::getTotalReportUsedPerMonth($user_id, $year, $check_admin ? true : false);
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

                // Return success response
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
     *     summary="Get Dashboard",
     *     description="This request is used to get inventory dashboard. This request interacts with the MySQL database, and have a protected routes.",
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

            // Define user id by role
            $check_admin = AdminModel::find($user_id);
            if($check_admin){
                $user_id = $request->query('user_id') ?? null;
            } 

            // Get total inventory stats
            $total_item = InventoryModel::getTotalInventory($user_id,'item');
            $total_fav = InventoryModel::getTotalInventory($user_id,'favorite');
            $total_low = InventoryModel::getTotalInventory($user_id,'low');
            $last_added = InventoryModel::getLastAddedInventory($user_id);
            $most_category = InventoryModel::getMostCategoryInventory($user_id);
            $highest_price = InventoryModel::getHighestPriceInventory($user_id);

            if($total_item && $total_item->total != 0){
                // Return success response
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
