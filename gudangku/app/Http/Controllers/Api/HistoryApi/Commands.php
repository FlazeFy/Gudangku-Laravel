<?php

namespace App\Http\Controllers\Api\HistoryApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\HistoryModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Commands extends Controller
{
    public function hard_delete_history_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $rows = HistoryModel::destroy($id);

            if($rows > 0){
                return response()->json([
                    'status' => 'success',
                    'message' => 'history permentally deleted',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'history failed to permentally deleted',
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
