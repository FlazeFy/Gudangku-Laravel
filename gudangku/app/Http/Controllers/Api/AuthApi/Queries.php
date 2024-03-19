<?php

namespace App\Http\Controllers\Api\AuthApi;

use App\Helpers\Generator;
use App\Models\UserModel;
use App\Models\AdminModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class Queries extends Controller
{
    public function logout(Request $request)
    {
        $user_id = $request->user()->id;
        $check = AdminModel::where('id', $user_id)->first();

        if($check == null){
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'message' => 'logout success'
            ], Response::HTTP_OK);
        } else {
            // Admin
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'message' => 'logout success'
            ], Response::HTTP_OK);
        }
    }
}
