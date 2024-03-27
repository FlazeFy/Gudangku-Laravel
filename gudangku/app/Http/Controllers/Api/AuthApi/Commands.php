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

class Commands extends Controller
{
    //
    public function login(Request $request)
    {
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

                return response()->json([
                    'status' => 'success',
                    'result' => $user,
                    'token' => $token,  
                    'role' => $role                  
                ], Response::HTTP_OK);
            }
        }
        
    }
}
