<?php

namespace App\Http\Controllers\Api\UserApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\UserModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Queries extends Controller
{
    public function get_my_profile(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = UserModel::select('username','email','telegram_user_id','created_at')
                ->where('id',$user_id)
                ->first();
            
            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'user fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'user failed to fetched',
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
