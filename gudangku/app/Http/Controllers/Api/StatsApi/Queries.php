<?php

namespace App\Http\Controllers\Api\StatsApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\InventoryModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Queries extends Controller
{
    public function get_total_inventory_by_category(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = InventoryModel::selectRaw("inventory_category as context, COUNT(1) as total")
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

    public function get_total_inventory_by_favorite(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = InventoryModel::selectRaw("
                    CASE 
                        WHEN is_favorite = 1 THEN 'Favorite' 
                        ELSE 'Normal Item' 
                    END AS context, 
                    $query_total as total")
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
                    'message' => 'stats failed to fetched',
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

    public function get_total_inventory_by_room(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = InventoryModel::selectRaw("inventory_room as context, $query_total as total")
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
                    'message' => 'stats failed to fetched',
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
