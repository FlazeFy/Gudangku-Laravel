<?php

namespace App\Http\Controllers\Api\AuthApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// Model
use App\Models\UserModel;
use App\Models\AdminModel;
// Helper
use App\Helpers\Validation;
use App\Helpers\Generator;

/**
 * @OA\Info(
 *     title="GudangKu",
 *     version="1.0.0",
 *     description="This document describes the GudangKu API, built with Laravel (PHP), MySQL as the primary database, and Firebase for cloud storage, notification, and NoSQL data storage.",
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
     *     summary="Post Login (Basic Auth)",
     *     description="This authentication request is used to access the application and obtain an authorization token for accessing all protected APIs. This request interacts with the MySQL database.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", example="flazefy"),
     *             @OA\Property(property="password", type="string", example="nopass123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="login successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="token", type="string", example="286|L5fqrLCDDCzPRLKngtm2FM9wq1IU2xFZSVAm10yp874a1a85"),
     *             @OA\Property(property="role", type="integer", example=1),
     *             @OA\Property(property="message", type="object",
     *                 @OA\Property(property="id", type="string", example="83ce75db-4016-d87c-2c3c-db1e222d0001"),
     *                 @OA\Property(property="username", type="string", example="flazefy"),
     *                 @OA\Property(property="email", type="string", example="flazen.edu@gmail.com"),
     *                 @OA\Property(property="telegram_user_id", type="string", example="123456789"),
     *                 @OA\Property(property="telegram_is_valid", type="integer", example=1),
     *                 @OA\Property(property="firebase_fcm_token", type="string", example="ddLEuWR2Q_isCmzHTM8UR4:APA91bEmY8TDmH3ZJtKgXw95wFDKLr53FGA2JArDTiN4jzSWxiGzf9VUECYN2oeqYV"),
     *                 @OA\Property(property="line_user_id", type="string", example="U3356dbe737f22z278e2ba81c71ec5"),
     *                 @OA\Property(property="phone", type="string", example="+628123456789"),
     *                 @OA\Property(property="timezone", type="string", example="+07:00"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-14 02:28:37"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-25 09:37:20"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="{validation_msg}",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="{field validation message}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="account is not found or have wrong password",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="wrong username or password")
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
    public function postLogin(Request $request)
    {
        try {
            // Validate request body
            $validator = Validation::getValidateLogin($request);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $validator->messages()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                // Check for Admin account
                $user = AdminModel::getByUsername($request->username);
                $role = 1;
                if($user == null){
                    // Check for User account
                    $user = UserModel::getByUsername($request->username);
                    $role = 0;
                }

                // Verify username and password
                if (!$user || !Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("custom", 'wrong username or password')       
                    ], Response::HTTP_UNAUTHORIZED);
                } else {
                    // Create Token
                    $token = $user->createToken('login')->plainTextToken;
                    unset($user->password);

                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => $user,
                        'token' => $token,  
                        'role' => $role                  
                    ], Response::HTTP_OK);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/logout",
     *     summary="Post Log Out",
     *     description="This authentication request is used to sign out from application or reset current session. This request interacts with the MySQL database.",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Logout success"),
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
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function postLogout(Request $request)
    {
        try {
            $user_id = $request->user()->id;
            
            // Reset session & token
            session()->flush();
            $request->user()->currentAccessToken()->delete();

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => Generator::getMessageTemplate("custom", 'logout success')
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
