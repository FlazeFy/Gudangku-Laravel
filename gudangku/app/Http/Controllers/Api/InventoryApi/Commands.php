<?php

namespace App\Http\Controllers\Api\InventoryApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\InventoryModel;

// Helpers
use App\Helpers\Audit;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Commands extends Controller
{
    /**
     * @OA\DELETE(
     *     path="/api/v1/inventory/delete/{id}",
     *     summary="Soft delete inventory by id",
     *     tags={"Inventory"},
     *     @OA\Response(
     *         response=200,
     *         description="inventory deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="inventory failed to deleted"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function soft_delete_inventory_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $inventory = InventoryModel::select('inventory_name')->where('id',$id)->first();

            $rows = InventoryModel::where('id', $id)
                ->where('created_by', $user_id)
                ->update([
                    'deleted_at' => date('Y-m-d H:i:s'),
            ]);

            if($rows > 0){
                // History
                Audit::createHistory('Delete', $inventory->inventory_name, $user_id);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory deleted',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory failed to deleted',
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
     * @OA\PUT(
     *     path="/api/v1/inventory/destroy/{id}",
     *     summary="Edit inventory image by id",
     *     tags={"Inventory"},
     *     @OA\Response(
     *         response=200,
     *         description="inventory image updated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="inventory image failed to updated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function edit_image_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $inventory = InventoryModel::select('inventory_name')->where('id',$id)->first();

            if($inventory->image == ""){
                $inventory_image = null;
            } else {
                $inventory_image = $inventory->image;
            }
            $rows = InventoryModel::where('id', $id)
                ->where('created_by', $user_id)
                ->update([
                    'inventory_image' => $inventory_image,
                    'updated_at' => date('Y-m-d H:i:s'),
            ]);
            

            if($rows > 0){
                // History
                Audit::createHistory('Update Image', $inventory->inventory_name, $user_id);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory image updated',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory image failed to updated',
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
     * @OA\DELETE(
     *     path="/api/v1/inventory/destroy/{id}",
     *     summary="Hard delete inventory by id",
     *     tags={"Inventory"},
     *     @OA\Response(
     *         response=200,
     *         description="inventory permentally deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="inventory failed to permentally deleted"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function hard_delete_inventory_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $inventory = InventoryModel::select('inventory_name')->where('id',$id)->first();

            $rows = InventoryModel::destroy($id);

            if($rows > 0){
                // History
                Audit::createHistory('Permentally delete', $inventory->inventory_name, $user_id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory permentally deleted',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory failed to permentally deleted',
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
     * @OA\PUT(
     *     path="/api/v1/inventory/fav_toggle/{id}",
     *     summary="Toogle favorite inventory by id",
     *     tags={"Inventory"},
     *     @OA\Response(
     *         response=200,
     *         description="inventory updated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="inventory failed to updated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function fav_toogle_inventory_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $inventory = InventoryModel::select('inventory_name')->where('id',$id)->first();

            $rows = InventoryModel::where('id',$id)
                ->where('created_by', $user_id)
                ->update([
                    'is_favorite' => $request->is_favorite
            ]);

            if($rows > 0){
                // History
                $ctx = 'Set';
                if($request->is_favorite == 0){
                    $ctx = 'Unset';
                }
                Audit::createHistory($ctx.' to favorite', $inventory->inventory_name, $user_id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory updated',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory failed to updated',
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
     * @OA\PUT(
     *     path="/api/v1/inventory/recover/{id}",
     *     summary="Recover inventory by id",
     *     tags={"Inventory"},
     *     @OA\Response(
     *         response=200,
     *         description="inventory recovered"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="inventory failed to recovered"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function recover_inventory_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $inventory = InventoryModel::select('inventory_name')->where('id',$id)->first();

            $rows = InventoryModel::where('id', $id)
                ->where('created_by', $user_id)
                ->update([
                    'deleted_at' => null,
            ]);

            if($rows > 0){
                // History
                Audit::createHistory('Delete', $inventory->inventory_name, $user_id);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory recovered',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory failed to recovered',
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
