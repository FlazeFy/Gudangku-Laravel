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
