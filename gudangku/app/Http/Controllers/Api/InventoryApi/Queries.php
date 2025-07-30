<?php

namespace App\Http\Controllers\Api\InventoryApi;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// Helpers
use App\Helpers\Document;
use App\Helpers\Generator;

// Models
use App\Models\InventoryModel;
use App\Models\InventoryLayoutModel;
use App\Models\ReminderModel;
use App\Models\ReportModel;
use App\Models\AdminModel;
use App\Models\LendInventoryRelModel;

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
     *             @OA\Property(property="lend_data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="inventory_category", type="string", example="Food & Beverages"),
     *                      @OA\Property(property="list_inventory", type="string", example="Nike Air Force 1 High By You"),
     *                  )
     *             ),
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
            // Attribute
            $user_id = $request->user()->id;
            $check_admin = AdminModel::find($user_id);
            $search_key = $request->query('search_key');
            $filter_category = $request->query('filter_category') ?? 'all';
            $sorting = $request->query('sorting') ?? 'desc_created';

            // Inventory fetch
            $res = InventoryModel::selectRaw($check_admin ? 'inventory.*,username' :'*');
            if(!$check_admin){
                $res->where('created_by',$user_id);
            } else {
                $res->join('users','users.id','=','inventory.created_by');
            }

            // Searching
            if ($search_key && trim($search_key) != "") {
                $res = $res->where(function ($query) use ($search_key) {
                    $query->where('inventory_name', 'like', "%$search_key%")
                            ->orWhere('inventory_merk', 'like', "%$search_key%");
                });
            }     

            // Filtering by category or context
            if($filter_category != 'all'){
                if($filter_category == 'deleted'){
                    $res->whereNotNull('deleted_at');
                } else if($filter_category == 'favorite'){
                    $res->where('is_favorite',1);
                } else if($filter_category == 'reminder'){
                    $res->where('is_reminder',1);
                } else {
                    $res->where('inventory_category',$filter_category);
                }
            }           

            $res = $res->get();
            
            if (count($res) > 0) {
                // Reminder fetch
                $res_final = [];
                foreach ($res as $idx => $dt) {
                    $reminder = ReminderModel::getReminderByInventoryId($dt->id,!$check_admin ? $user_id : $dt->created_by);

                    $dt->reminder = count($reminder) > 0 ? $reminder : null;
                    $res_final[] = $dt;
                }

                $collection = collect($res_final);

                // Sorting
                if ($sorting == 'desc_created') {
                    $collection = $collection->sortByDesc('created_at');
                } else if ($sorting == 'asc_created') {
                    $collection = $collection->sortBy('created_at');
                } else if ($sorting == 'desc_name') {
                    $collection = $collection->sortByDesc('inventory_name');
                } else if ($sorting == 'asc_name') {
                    $collection = $collection->sortBy('inventory_name');
                } else if ($sorting == 'desc_price') {
                    $collection = $collection->sortByDesc('inventory_price');
                } else if ($sorting == 'asc_price') {
                    $collection = $collection->sortBy('inventory_price');
                } else if ($sorting == 'desc_updated') {
                    $collection = $collection->sortByDesc('inventory.updated_at');
                } else if ($sorting == 'asc_updated') {
                    $collection = $collection->sortBy('inventory.updated_at');
                }

                // Paginate
                $collection = $collection->sortByDesc('is_favorite')->values();
                $perPage = $request->query('per_page_key') ?? 12;
                $page = request()->input('page', 1);
                $paginator = new LengthAwarePaginator(
                    $collection->forPage($page, $perPage)->values(),
                    $collection->count(),
                    $perPage,
                    $page,
                    ['path' => url()->current()]
                );
                $res_final = $paginator->appends(request()->except('page'));

                // Get Lend Item
                $res_lend = LendInventoryRelModel::getAllLendActiveInventory($user_id);

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'inventory'),
                    'data' => $res_final,
                    'lend_data' => $res_lend
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'inventory'),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
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
                    'message' => Generator::getMessageTemplate("fetch", 'inventory'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'inventory'),
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
     *                      @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
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

            $res = InventoryModel::select('id','inventory_name','inventory_price','created_at')
                ->where('created_by',$user_id)
                ->whereNull('deleted_at')
                ->orderby('created_at','DESC')
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'inventory'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'inventory'),
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
     *     path="/api/v1/inventory/room",
     *     summary="Get inventory room",
     *     description="This request is used to get all inventory room. This request is using MySql database, and has protected routes",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="inventory fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="inventory room fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="inventory_room", type="string", example="Main Room"),
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
     *         description="inventory room failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="inventory room not found")
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
    public function get_list_room(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = InventoryModel::select('inventory_room')
                ->where('created_by',$user_id)
                ->groupby('inventory_room')
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'inventory'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'inventory'),
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
     *     path="/api/v1/inventory/layout/{room}",
     *     summary="Get inventory layout by room",
     *     description="This request is used to get inventory layout to show storage by room. This request is using MySql database, and has protected routes",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="room",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="Main Room"
     *         ),
     *         description="Inventory storage's room",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="inventory fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="inventory layout fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="string", example="17963858-9771-11ee-8f4a-3216422910r4"),
     *                      @OA\Property(property="inventory_storage", type="string", example="Wardobe"),
     *                      @OA\Property(property="storage_desc", type="string", example="Store my clothes"),
     *                      @OA\Property(property="layout", type="string", example="D1:E3")
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
     *         description="inventory layout failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="inventory layout not found")
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
    public function get_room_layout(Request $request,$room)
    {
        try{
            $user_id = $request->user()->id;

            $res = InventoryLayoutModel::getInventoryByLayout($user_id, $room);
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'inventory layout'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'inventory layout'),
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
     *     path="/api/v1/inventory/search/by_room_storage/{room}/{storage}",
     *     summary="Get inventory in storage",
     *     description="This request is used to get all inventory data in layout page. This request is using MySql database, and has protected routes",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="room",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="Main Room"
     *         ),
     *         description="Inventory storage's room",
     *     ),
     *     @OA\Parameter(
     *         name="storage",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="Main Table"
     *         ),
     *         description="Inventory storage",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="inventory fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="inventory fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
     *                      @OA\Property(property="inventory_name", type="string", example="Cake"),
     *                      @OA\Property(property="inventory_unit", type="string", example="Pcs"),
     *                      @OA\Property(property="inventory_vol", type="integer", example=1),
     *                      @OA\Property(property="inventory_price", type="integer", example=200000),
     *                      @OA\Property(property="inventory_category", type="string", example="Food And Beverages"),
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
    public function get_inventory_by_storage(Request $request,$room,$storage){
        try{
            $user_id = $request->user()->id;

            $res = InventoryModel::getInventoryByStorage($storage,$room,$user_id);
            
            if (count($res) > 0) {
                $stats = InventoryModel::getInventoryStatsByStorage($storage,$room,$user_id);

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'inventory'),
                    'data' => $res,
                    'stats' => $stats
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'inventory'),
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
     *     path="/api/v1/inventory/detail/{id}",
     *     summary="Get inventory detail",
     *     description="Fetch inventory details and reminders by the given inventory's `id`. This request uses a MySQL database and requires authentication with a protected route.",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="09397f65-211e-3598-2fa5-b50cdba5183c"
     *         ),
     *         description="Inventory ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory details fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="inventory fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="83ce75db-4016-d87c-2c3c-db1e222d0001"),
     *                 @OA\Property(property="inventory_name", type="string", example="Stand Bracket Laptop"),
     *                 @OA\Property(property="inventory_category", type="string", example="Office Tools"),
     *                 @OA\Property(property="inventory_desc", type="string", example="Stand 2 laptop dan hp"),
     *                 @OA\Property(property="inventory_merk", type="string", example="A Merk"),
     *                 @OA\Property(property="inventory_room", type="string", example="Main Room"),
     *                 @OA\Property(property="inventory_storage", type="string", example="Desk"),
     *                 @OA\Property(property="inventory_rack", type="string", example=null),
     *                 @OA\Property(property="inventory_price", type="integer", example=28200),
     *                 @OA\Property(property="inventory_image", type="string", example="https://firebasestorage.googleapis.com/v0/b/gudangku-94edc.appspot.com/o/inventory%2F2d98f524-de02-11ed-b5ea-0242ac120002_flazefy%2Fdbc22192-f630-4c68-8a95-d148de537bde?alt=media&token=ac5e7d97-9711-4f4e-b22e-4ff911cf6006"),
     *                 @OA\Property(property="inventory_unit", type="string", example="Pcs"),
     *                 @OA\Property(property="inventory_vol", type="integer", example=1),
     *                 @OA\Property(property="inventory_capacity_unit", type="string", example=null),
     *                 @OA\Property(property="inventory_capacity_vol", type="integer", example=null),
     *                 @OA\Property(property="inventory_color", type="string", example=null),
     *                 @OA\Property(property="is_favorite", type="integer", example=0),
     *                 @OA\Property(property="is_reminder", type="integer", example=0),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-14 02:28:37"),
     *                 @OA\Property(property="created_by", type="string", example="2d98f524-de02-11ed-b5ea-0242ac120002"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-25 09:37:20"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", example=null)
     *             ),
     *             @OA\Property(property="reminder",type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="26c0092a-5013-2a81-3a36-fc0abeb7ce6e"),
     *                     @OA\Property(property="reminder_desc", type="string", example="Clean using hand sanitizer and micellar water"),
     *                     @OA\Property(property="reminder_type", type="string", example="Every Month"),
     *                     @OA\Property(property="reminder_context", type="string", example="Every 1"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-16T00:06:05.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authorization token required",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inventory not found",
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
     *     )
     * )
     */
    public function get_inventory_by_id(Request $request, $id){
        try{
            $user_id = $request->user()->id;
            $res = InventoryModel::getInventoryDetail($id,$user_id);
            
            if ($res) {
                $reminder = ReminderModel::getReminderByInventoryId($id,$user_id);

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'inventory'),
                    'data' => $res,
                    'reminder' => count($reminder) > 0 ? $reminder : null
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'inventory'),
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
     *     path="/api/v1/inventory/detail/{id}/doc",
     *     summary="Get inventory detail html format by id",
     *     description="This request is used to get inventory detail html format for document generate. This request is using MySQL database, and has protected routes.",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Inventory ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="inventory detail document generated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="inventory detail document generated"),
     *             @OA\Property(property="data", type="string", example="<p>Ini document</p>"),
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
     *         description="inventory detail document failed to generated",
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
     *     )
     * )
     */
    public function get_inventory_detail_document(Request $request,$id)
    {
        try{
            $user_id = $request->user()->id;

            $check_admin = AdminModel::find($user_id);
            if($check_admin){
                $user_id = null;
            }

            $inventory = InventoryModel::getInventoryDetail($id,$user_id);

            if (is_array($inventory) ? count($inventory) > 0 : $inventory) {    
                $reminder = ReminderModel::getReminderByInventoryId($id,$user_id);
                $html = Document::documentTemplateInventory(null,null,null,$inventory,$reminder);
     
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("generate", "inventory detail document"),
                    'data' => $html
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'inventory'),
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
     *     path="/api/v1/inventory/layout/{room}/doc",
     *     summary="Get room layout html format by id",
     *     description="This request is used to get room layout html format for document generate. This request is using MySQL database, and has protected routes.",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Room",
     *         example="Main%20Room",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="room document generated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="inventory room document generated"),
     *             @OA\Property(property="data", type="string", example="<p>Ini document</p>"),
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
     *         description="room document failed to generated",
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
     *     )
     * )
     */
    public function get_room_document(Request $request,$room)
    {
        try{
            $user_id = $request->user()->id;
            $inventory = InventoryModel::getInventoryByRoom($room,$user_id);
            $layout = InventoryLayoutModel::getInventoryByLayout($user_id, $room);

            if (is_array($inventory) ? count($inventory) > 0 : $inventory && $layout) {    
                $html = Document::documentTemplateLayout(null,null,null,$layout,$inventory,$room);
     
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("generate", "inventory room document"),
                    'data' => $html
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'inventory'),
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
     *     path="/api/v1/inventory/analyze/{id}",
     *     summary="Get analyze data of inventory by id",
     *     description="This request is used to get analyze data of inventory. This request is using MySQL database, and has protected routes.",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Inventory ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="inventory analyzed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="inventory analyzed"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="inventory_name", type="string", example="odio omnis cum"),
     *                 @OA\Property(property="inventory_price", type="integer", example=4145000),
     *                 @OA\Property(property="inventory_category", type="string", example="Skin & Body Care"),
     *                 @OA\Property(property="inventory_room", type="string", example="Car Cabin"),
     *                 @OA\Property(property="inventory_storage", type="string", example="Stand"),
     *                 @OA\Property(property="inventory_rack", type="string", example="Rack-b3"),
     *                 @OA\Property(property="inventory_unit", type="string", example="Pcs"),
     *                 @OA\Property(property="inventory_vol", type="integer", example=6),
     *                 @OA\Property(property="created_at", type="string", example="2024-10-24 21:15:01"),
     *                 @OA\Property(property="updated_at", type="string", example="2024-11-03 18:25:48"),
     *                 @OA\Property(property="inventory_price_analyze", type="object",
     *                     @OA\Property(property="average_inventory_price", type="integer", example=2473052),
     *                     @OA\Property(property="max_inventory_price", type="integer", example=4995000),
     *                     @OA\Property(property="min_inventory_price", type="integer", example=30000),
     *                     @OA\Property(property="diff_ammount_average_to_price", type="integer", example=-1671948),
     *                     @OA\Property(property="diff_status_average_to_price", type="string", example="More Expensive")
     *                 ),
     *                 @OA\Property(property="inventory_category_analyze", type="object",
     *                     @OA\Property(property="total", type="integer", example=98),
     *                     @OA\Property(property="average_price", type="integer", example=2418571)
     *                 ),
     *                 @OA\Property(property="inventory_room_analyze", type="object",
     *                     @OA\Property(property="total", type="integer", example=83),
     *                     @OA\Property(property="average_price", type="integer", example=2515723)
     *                 ),
     *                 @OA\Property(property="inventory_unit_analyze", type="object",
     *                     @OA\Property(property="total", type="integer", example=84),
     *                     @OA\Property(property="average_price", type="integer", example=2439762)
     *                 ),
     *                 @OA\Property(property="inventory_history_analyze", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="total", type="integer", example=2),
     *                         @OA\Property(property="report_category", type="string", example="Others")
     *                     )
     *                 ),
     *                 @OA\Property(property="inventory_report", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="created_at", type="string", example="2024-09-14 02:22:18"),
     *                         @OA\Property(property="report_title", type="string", example="et amet"),
     *                         @OA\Property(property="report_category", type="string", example="Others")
     *                     )
     *                 ),
     *                 @OA\Property(property="inventory_in_monthly_report", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="context", type="string", example="Jan"),
     *                         @OA\Property(property="total", type="integer", example=2)
     *                     )
     *                 ),
     *                 @OA\Property(property="inventory_layout", type="object",
     *                         @OA\Property(property="inventory_storage", type="string", example="Wardobe"),
     *                         @OA\Property(property="layout", type="string", example="E1"),
     *                         @OA\Property(property="storage_desc", type="string", example="Lorem ipsum"),
     *                         @OA\Property(property="created_at", type="string", example="2024-10-25 09:13:00")
     *                 ),
     *                 @OA\Property(property="inventory_activity_report", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="context", type="string", example="2024-12-02"),
     *                         @OA\Property(property="total", type="integer", example=2),
     *                         @OA\Property(property="day", type="string", example="Monday")
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
     *         description="inventory detail document failed to analyze",
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
     *     )
     * )
     */

    public function get_analyze_inventory(Request $request,$id)
    {
        try{
            $user_id = $request->user()->id;
            $inventory = InventoryModel::find($id);
            $year = $request->query('year', date('Y'));

            if ($inventory) {    
                $res_price = InventoryModel::getAnalyzeMost($user_id, 'inventory_price');
                $diff_price_avg = $res_price->average_inventory_price - $inventory->inventory_price;
                $res_price->diff_ammount_average_to_price = $diff_price_avg;
                $res_price->diff_status_average_to_price = $diff_price_avg < 0 ? 'More Exspensive' : 'More Cheaper';
                $res_category = InventoryModel::getAnalyzeContext($user_id, 'inventory_category',$inventory->inventory_category);
                $res_room = InventoryModel::getAnalyzeContext($user_id, 'inventory_room',$inventory->inventory_room);
                $res_merk = InventoryModel::getAnalyzeContext($user_id, 'inventory_merk',$inventory->inventory_merk);
                $res_unit = InventoryModel::getAnalyzeContext($user_id, 'inventory_unit',$inventory->inventory_unit);
                $res_history = InventoryModel::getAnalyzeHistory($user_id,$inventory->id);
                $res_report = ReportModel::getLastFoundInventoryReport($user_id,$inventory->id);
                $res_montly_in_report = ReportModel::getInventoryMonthlyInReport($user_id,$inventory->id,$year);
                $res_layout = InventoryLayoutModel::getFindInventoryByRoomStorage($user_id,$inventory->inventory_room,$inventory->inventory_storage);
                $res_inventory_activity_report = InventoryModel::getAnalyzeActivityInReport($user_id,$inventory->id);

                $res_info = [
                    'inventory_name' => $inventory->inventory_name,
                    'inventory_price' => $inventory->inventory_price,
                    'inventory_category' => $inventory->inventory_category,
                    'inventory_room' => $inventory->inventory_room,
                    'inventory_merk' => $inventory->inventory_merk,
                    'inventory_storage' => $inventory->inventory_storage,
                    'inventory_rack' => $inventory->inventory_rack,
                    'inventory_unit' => $inventory->inventory_unit,
                    'inventory_vol' => $inventory->inventory_vol,
                    'inventory_capacity_unit' => $inventory->inventory_capacity_unit,
                    'inventory_capacity_vol' => $inventory->inventory_capacity_vol,
                    'created_at' => $inventory->created_at,
                    'updated_at' => $inventory->updated_at,
                ];

                $res = (object) array_merge($res_info, [
                    'inventory_price_analyze' => $res_price,
                    'inventory_merk_analyze' => $res_merk,
                    'inventory_category_analyze' => $res_category,
                    'inventory_room_analyze' => $res_room,
                    'inventory_unit_analyze' => $res_unit,
                    'inventory_history_analyze' => $res_history,
                    'inventory_report' => count($res_report) > 0 ? $res_report : null,
                    'inventory_in_monthly_report' => $res_montly_in_report,
                    'inventory_layout' => $res_layout,
                    'inventory_activity_report' => $res_inventory_activity_report
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("analyze", "inventory"),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'inventory'),
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
