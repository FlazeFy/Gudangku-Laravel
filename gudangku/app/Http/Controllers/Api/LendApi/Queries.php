<?php

namespace App\Http\Controllers\Api\LendApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

// Helpers
use App\Helpers\Generator;
// Models
use App\Models\LendModel;
use App\Models\InventoryModel;
use App\Models\LendInventoryRelModel;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/lend/qr",
     *     summary="Get Active QR Code",
     *     description="This request is used to get active qr code.  This request interacts with the MySQL database, and has protected routes.",
     *     tags={"Lend"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="lend qr code fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="lend qr code fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="d8b5d4cc-805d-3303-3966-dc767c062d27"),
     *                 @OA\Property(property="lend_qr_url", type="string", format="url", example="https://storage.googleapis.com/download/storage/v1/b/gudangku-94edc.appspot.com/o/lend%2F..."),
     *                 @OA\Property(property="qr_period", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-19 02:37:58"),
     *                 @OA\Property(property="lend_desc", type="string", example="lorem ipsum"),
     *                 @OA\Property(property="lend_expired_datetime", type="string", format="date-time", example="2024-03-19 04:37:58"),
     *                 @OA\Property(property="is_expired", type="boolean", example=false),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="qr is code is exist but expired",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="lend qr code is expired")
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
     *         description="lend qr code failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="lend qr code not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="qr is code cant generated due to empty inventory",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you must have at least one inventory to generate QR")
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
    public function getLendActive(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            // Check If user has inventory
            $check_inventory = InventoryModel::getInventoryTotal($user_id);
            if($check_inventory == 0){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'you must have at least one inventory to generate QR',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Get active lend
            $res = LendModel::getLendActive($user_id);
            if($res) { 
                $res->lend_expired_datetime = Carbon::parse($res->created_at)->addHours($res->qr_period);
                $res->is_expired = Carbon::now()->greaterThan($res->lend_expired_datetime);

                if(!$res->is_expired){
                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("fetch", "lend qr code"),
                        'data' => $res
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'lend qr code is expired',
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'lend qr code'),
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
     *     path="/api/v1/lend/history",
     *     summary="Get History QR Code",
     *     description="This request is used to get history of qr code. This request interacts with the MySQL database, has a pagination, and has protected routes.",
     *     tags={"Lend"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="lend qr code fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="lend qr code fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", format="uuid", example="d8b5d4cc-805d-3303-3966-dc767c062d27"),
     *                         @OA\Property(property="lend_qr_url", type="string", format="url", example="https://storage.googleapis.com/download/storage/v1/b/gudangku-94edc.appspot.com/o/lend%2F..."),
     *                         @OA\Property(property="qr_period", type="integer", example=2),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-19 02:37:58"),
     *                         @OA\Property(property="lend_desc", type="string", example="lorem ipsum"),
     *                         @OA\Property(property="lend_status", type="string", example="expired"),
     *                         @OA\Property(property="is_finished", type="integer", example=0),
     *                         @OA\Property(property="list_inventory", type="string", example="Herborist Aloe Vera Gel (Skin & Body Care), Palmolive Shower Gel Absoule Relax (Food And Beverages)"),
     *                     )
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
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
     *         description="lend qr code failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="lend qr code not found")
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
    public function getLendHistory(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $perPage = $request->query('per_page_key') ?? 12;

            // Get all lend
            $res = LendModel::getAllLend($user_id,$perPage);
            if ($res->count() > 0){
                $final_res = [];
                foreach ($res as $dt) {
                    $final_res[] = [
                        'id' => $dt->id,
                        'lend_qr_url' => $dt->lend_qr_url,
                        'qr_period' => $dt->qr_period,
                        'lend_desc' => $dt->lend_desc,
                        'lend_status' => $dt->lend_status,
                        'created_at' => $dt->created_at,
                        'is_finished' => $dt->is_finished,
                        'list_inventory' => $dt->list_inventory,
                        'borrower_name' => $dt->borrower_name,
                        'list_inventory_detail' => LendInventoryRelModel::getInventoryByLendId($user_id,$dt->id)
                    ];
                }

                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "lend qr code"),
                    'data' => [
                        'current_page' => $res->currentPage(),
                        'data' => $final_res,
                        'first_page_url' => $res->url(1),
                        'from' => $res->firstItem(),
                        'last_page' => $res->lastPage(),
                        'last_page_url' => $res->url($res->lastPage()),
                        'links' => $res->toArray()['links'], 
                        'next_page_url' => $res->nextPageUrl(),
                        'path' => $res->path(),
                        'per_page' => $res->perPage(),
                        'prev_page_url' => $res->previousPageUrl(),
                        'to' => $res->lastItem(),
                        'total' => $res->total(),
                    ]
                ], Response::HTTP_OK);                
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'lend qr code'),
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
     *     path="/api/v1/lend/inventory/{lend_id}",
     *     summary="Get Lend Inventory",
     *     description="This request is used to get lend inventory by lend id (QR Code).  This request interacts with the MySQL database, has a pagination, and has protected routes.",
     *     tags={"Lend"},
     *     @OA\Response(
     *         response=200,
     *         description="lend inventory fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="lend inventory fetched"),
     *             @OA\Property(property="owner", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="d8b5d4cc-805d-3303-3966-dc767c062d27"),
     *                 @OA\Property(property="username", type="string", format="url", example="flazefy"),
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
     *                         @OA\Property(property="inventory_image", type="string", example="https://example.com/inventory/image.jpg"),
     *                         @OA\Property(property="inventory_unit", type="string", example="Pcs"),
     *                         @OA\Property(property="inventory_vol", type="integer", example=1),
     *                         @OA\Property(property="inventory_color", type="string", example="Black"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-19 02:37:58"),
     *                     )
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
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
     *         description="lend inventory failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="lend inventory not found")
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
    public function getLendInventory(Request $request, $lend_id)
    {
        try{
            $perPage = $request->query('per_page_key') ?? 12;

            // Get lend by ID
            $check = LendModel::find($lend_id);
            if($check->lend_status == 'expired' || $check->lend_status == 'used' || $check->is_finished){
                $extra = $check->lend_status == 'expired' ? 'expired' : 'used';
                return response()->json([
                    'status' => 'failed',
                    'message' => "lend already $extra",
                ], Response::HTTP_BAD_REQUEST);
            }

            // Get lend attached inventory by lend's ID
            $res = LendModel::getAllLendInventory($lend_id,$perPage);
            if(count($res) > 0) { 
                // Get lend owner by lend's ID
                $user = LendModel::getLendOwnerById($lend_id);

                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "lend inventory"),
                    'data' => $res,
                    'owner' => $user
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'lend inventory'),
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
