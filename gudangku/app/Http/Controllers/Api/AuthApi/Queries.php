<?php

namespace App\Http\Controllers\Api\AuthApi;

use App\Models\UserModel;
use App\Models\AdminModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class Queries extends Controller
{
     /**
     * @OA\GET(
     *     path="/api/v1/logout",
     *     summary="Sign out from Apps",
     *     description="This request is used to get sign out from the Apps (sign out from the session). This request is using MySql database, and have a protected routes.",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Logout success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Logout success"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function logout(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            $check = AdminModel::where('id', $user_id)->first();

            if($check == null){
                $request->user()->currentAccessToken()->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Logout success'
                ], Response::HTTP_OK);
            } else {
                // Admin
                $request->user()->currentAccessToken()->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Logout success'
                ], Response::HTTP_OK);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
