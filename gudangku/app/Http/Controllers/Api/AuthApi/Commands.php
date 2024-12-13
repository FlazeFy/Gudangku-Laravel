<?php

namespace App\Http\Controllers\Api\AuthApi;

use App\Models\UserModel;
use App\Models\AdminModel;
use App\Helpers\Validation;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Info(
 *     title="GudangKu",
 *     version="1.0.0",
 *     description="API Documentation for GudangKu",
 *     @OA\Contact(
 *         email="flazen.edu@gmail.com"
 *     )
 * ),
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT Authorization header using the Bearer scheme",
 * )
 */

class Commands extends Controller
{
    /**
     * @OA\POST(
     *     path="/api/v1/login",
     *     summary="Sign in to the Apps",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="{user_data}"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="{validation_msg}"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Wrong username or password"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function login(Request $request)
    {
        try {
            $validator = Validation::getValidateLogin($request);

            if ($validator->fails()) {
                $errors = $validator->messages();

                return response()->json([
                    'status' => 'failed',
                    'result' => $errors,
                    'token' => null
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user = AdminModel::where('username', $request->username)->first();
                $role = 1;
                if($user == null){
                    $user = UserModel::where('username', $request->username)->first();
                    $role = 0;
                }

                if (!$user || !Hash::check($request->password, $user->password)) {
                    //if (!$user || ($request->password != $user->password)) {
                    return response()->json([
                        'status' => 'failed',
                        'result' => 'wrong username or password',
                        'token' => null,                
                    ], Response::HTTP_UNAUTHORIZED);
                } else {
                    $token = $user->createToken('login')->plainTextToken;
                    unset($user->password);

                    return response()->json([
                        'status' => 'success',
                        'result' => $user,
                        'token' => $token,  
                        'role' => $role                  
                    ], Response::HTTP_OK);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
